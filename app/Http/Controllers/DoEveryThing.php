<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class DoEveryThing extends Controller {
    private const INCOME = 'รายรับ';
    private const EXPENSE = 'รายจ่าย';

    /**
     * How many periods the trend chart shows, per grouping. Sized so the
     * common ranges (a month by day, a year by month) fit whole rather than
     * quietly losing their first few columns.
     */
    private const TREND_LIMIT = ['day' => 31, 'week' => 16, 'month' => 12];

    /** Ranked category lists fold everything past this into "อื่น ๆ". */
    private const BREAKDOWN_LIMIT = 8;

    private const THAI_MONTHS_SHORT = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    private const THAI_DAYS_SHORT = ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'];

    public function index(Request $request) {
        $cats = $this->get_cat();
        $filters = $this->filters($request, $cats);
        $group = $this->grouping($request);

        $trans = $this->get_trans($filters);
        $filterQuery = $this->filter_query($filters);

        return view('index', [
            'cats' => $cats,
            'trans' => $trans,
            'ledger' => $this->build_ledger($trans),
            'search' => $filters['q'],
            'filters' => $filters,
            'filterQuery' => $filterQuery,
            // Everything needed to rebuild the current view in a link.
            'viewQuery' => $filterQuery + ($group !== 'month' ? ['group' => $group] : []),
            'hasFilters' => $filterQuery !== [],
            'rangeLabel' => $this->range_label($filters),
            'group' => $group,
            'totals' => $this->get_totals($trans),
            'trend' => $this->build_trend($trans, $group, $filters),
            'breakdown' => $this->build_breakdown($trans),
            'editing' => $filters['edit'] !== null ? Transaction::find($filters['edit']) : null,
            'editingCat' => $filters['edit_cat'] !== null ? ExpenseCategory::find($filters['edit_cat']) : null,
        ]);
    }

    /**
     * Query string values can arrive as arrays (e.g. ?edit[]=1), which would
     * otherwise blow up Model::find() or type-hinted string params. Collapse
     * anything that isn't a plain non-empty string down to null.
     */
    private function scalar_query(Request $request, string $key): ?string {
        $value = $request->query($key);

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * Every filter the dashboard understands, resolved from the query string.
     * Anything unparseable collapses to null so a hand-edited or stale URL
     * degrades to "no filter" instead of an error or a confusing empty page.
     */
    private function filters(Request $request, ?Collection $cats = null): array {
        $from = $this->parse_date($this->scalar_query($request, 'from'));
        $to = $this->parse_date($this->scalar_query($request, 'to'));

        // A backwards range is almost always a typo in the two date inputs.
        // Swapping is friendlier than silently returning nothing.
        if ($from !== null && $to !== null && $from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        $cat = $this->scalar_query($request, 'cat');
        // Ignore a category that no longer exists (a stale bookmark, or a
        // category deleted since) rather than showing an empty ledger
        // filtered by nothing the user can see or clear.
        if ($cat !== null) {
            $cats ??= $this->get_cat();
            $cat = $cats->contains(fn ($c) => (string) $c->cat_id === $cat) ? (int) $cat : null;
        }

        return [
            'q' => $this->scalar_query($request, 'q'),
            'from' => $from,
            'to' => $to,
            'cat' => $cat,
            'edit' => $this->scalar_query($request, 'edit'),
            'edit_cat' => $this->scalar_query($request, 'edit_cat'),
        ];
    }

    private function parse_date(?string $value): ?Carbon {
        if ($value === null) return null;

        try {
            $date = Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }

        // createFromFormat can roll 2026-13-45 over into the next year, so
        // round-trip it to reject anything that wasn't a real date.
        return $date !== false && $date->format('Y-m-d') === $value
            ? $date->startOfDay()
            : null;
    }

    private function grouping(Request $request): string {
        $group = $this->scalar_query($request, 'group');

        return array_key_exists($group, self::TREND_LIMIT) ? $group : 'month';
    }

    /**
     * The active filters as query-string params, for links that need to keep
     * the current view (edit links, the grouping switch, redirects after a
     * save). Edit params are deliberately excluded — they're a mode, not a
     * filter, and shouldn't survive a save.
     */
    private function filter_query(array $filters): array {
        return array_filter([
            'q' => $filters['q'],
            'from' => $filters['from']?->format('Y-m-d'),
            'to' => $filters['to']?->format('Y-m-d'),
            'cat' => $filters['cat'],
        ], fn ($v) => $v !== null);
    }

    public function get_cat() {
        return ExpenseCategory::all();
    }

    /**
     * One query feeds the ledger, the totals and both analytics panels, so
     * every part of the page always describes the same slice of data.
     */
    public function get_trans(array $filters = []) {
        return $this->filtered_query($filters)
            ->orderByDesc('transactions.ts_date')
            ->orderByDesc('transactions.ts_id')
            ->get();
    }

    private function filtered_query(array $filters): Builder {
        $search = $filters['q'] ?? null;
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        $cat = $filters['cat'] ?? null;

        return Transaction::with('category')
            ->when($search !== null, fn ($query) => $query->where(
                'ts_note', 'like', '%' . addcslashes($search, '%_\\') . '%'
            ))
            ->when($from !== null, fn ($query) => $query->whereDate('ts_date', '>=', $from->format('Y-m-d')))
            ->when($to !== null, fn ($query) => $query->whereDate('ts_date', '<=', $to->format('Y-m-d')))
            ->when($cat !== null, fn ($query) => $query->where('cat_id', $cat));
    }

    public function get_totals(?Collection $trans = null): array {
        $trans ??= $this->get_trans();

        $income = 0.0;
        $expense = 0.0;

        foreach ($trans as $tran) {
            $amount = (float) $tran->ts_amount;
            match ($tran->category?->cat_type) {
                self::INCOME => $income += $amount,
                self::EXPENSE => $expense += $amount,
                default => null,
            };
        }

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'count' => $trans->count(),
        ];
    }

    /**
     * The ledger reads like a passbook: rows sit under the day they happened
     * on, with that day's net in the header. Relative headings ("วันนี้") are
     * resolved here rather than in the view so there's one source of truth.
     */
    private function build_ledger(Collection $trans): array {
        $today = Carbon::today();
        $groups = [];

        foreach ($trans as $tran) {
            $date = $tran->ts_date;
            $key = $date->format('Y-m-d');

            if (!isset($groups[$key])) {
                // Carbon 3 returns a float here; both sides are midnight, so
                // the value is whole and safe to cast for an exact match.
                $days = (int) $date->copy()->startOfDay()->diffInDays($today, false);

                $groups[$key] = [
                    'key' => $key,
                    // Only the last few days get a relative name; everything
                    // else is headed by its date alone.
                    'relative' => match ($days) {
                        0 => 'วันนี้',
                        1 => 'เมื่อวาน',
                        -1 => 'พรุ่งนี้',
                        default => null,
                    },
                    // Shown alongside a relative name, so "วันนี้" never hides
                    // which day a row actually belongs to.
                    'full' => self::THAI_DAYS_SHORT[(int) $date->dayOfWeek] . ' ' . $this->thai_date($date),
                    'net' => 0.0,
                    'rows' => [],
                ];
            }

            $amount = (float) $tran->ts_amount;
            $groups[$key]['net'] += $tran->category?->cat_type === self::EXPENSE ? -$amount : $amount;
            $groups[$key]['rows'][] = $tran;
        }

        return array_values($groups);
    }

    /** e.g. 9 ก.ย. 2569 — Thai months, Buddhist-era year. */
    private function thai_date(Carbon $date): string {
        return $date->day . ' ' . self::THAI_MONTHS_SHORT[$date->month - 1] . ' ' . ($date->year + 543);
    }

    /** Names the slice the page is currently describing, in plain Thai. */
    private function range_label(array $filters): string {
        $from = $filters['from'];
        $to = $filters['to'];

        return match (true) {
            $from !== null && $to !== null => $this->thai_date($from) . ' – ' . $this->thai_date($to),
            $from !== null => 'ตั้งแต่ ' . $this->thai_date($from),
            $to !== null => 'ถึง ' . $this->thai_date($to),
            default => 'ทุกช่วงเวลา',
        };
    }

    /**
     * Income vs expense per period. Empty periods are filled in so a month
     * with no activity reads as a real gap instead of being skipped over.
     */
    private function build_trend(Collection $trans, string $group, array $filters): array {
        $buckets = [];

        foreach ($trans as $tran) {
            $key = $this->period_start($tran->ts_date, $group)->format('Y-m-d');
            $buckets[$key] ??= ['income' => 0.0, 'expense' => 0.0];

            $amount = (float) $tran->ts_amount;
            if ($tran->category?->cat_type === self::EXPENSE) {
                $buckets[$key]['expense'] += $amount;
            } elseif ($tran->category?->cat_type === self::INCOME) {
                $buckets[$key]['income'] += $amount;
            }
        }

        if ($buckets === []) {
            return ['labels' => [], 'income' => [], 'expense' => [], 'empty' => true];
        }

        $keys = array_keys($buckets);
        sort($keys);

        // Anchor the axis to the filter range when one is set, so an explicit
        // "May to August" still shows August even if nothing was spent then.
        $start = $this->period_start(
            $filters['from'] ?? Carbon::parse($keys[0]),
            $group
        );
        $end = $this->period_start(
            $filters['to'] ?? Carbon::parse($keys[count($keys) - 1]),
            $group
        );

        $periods = [];
        for ($cursor = $start->copy(); $cursor->lte($end); $cursor = $this->period_next($cursor, $group)) {
            $periods[] = $cursor->copy();
            // Guard against a filter range so wide it would build a huge axis.
            if (count($periods) > 400) break;
        }

        $periods = array_slice($periods, -self::TREND_LIMIT[$group]);

        $labels = [];
        $income = [];
        $expense = [];

        foreach ($periods as $period) {
            $key = $period->format('Y-m-d');
            $labels[] = $this->period_label($period, $group);
            $income[] = round($buckets[$key]['income'] ?? 0.0, 2);
            $expense[] = round($buckets[$key]['expense'] ?? 0.0, 2);
        }

        return ['labels' => $labels, 'income' => $income, 'expense' => $expense, 'empty' => false];
    }

    private function period_start(Carbon $date, string $group): Carbon {
        return match ($group) {
            'day' => $date->copy()->startOfDay(),
            'week' => $date->copy()->startOfWeek(Carbon::MONDAY),
            default => $date->copy()->startOfMonth(),
        };
    }

    private function period_next(Carbon $date, string $group): Carbon {
        return match ($group) {
            'day' => $date->copy()->addDay(),
            'week' => $date->copy()->addWeek(),
            default => $date->copy()->addMonth(),
        };
    }

    private function period_label(Carbon $period, string $group): string {
        $month = self::THAI_MONTHS_SHORT[$period->month - 1];

        return match ($group) {
            'day' => $period->day . ' ' . $month,
            'week' => $period->day . '–' . $period->copy()->endOfWeek(Carbon::SUNDAY)->day . ' ' . $month,
            default => $month . ' ' . substr((string) ($period->year + 543), 2),
        };
    }

    /**
     * Which categories the money actually goes to (and comes from), ranked.
     * Rendered as bars in the view, so no chart library is involved.
     */
    private function build_breakdown(Collection $trans): array {
        $sums = [];

        foreach ($trans as $tran) {
            $cat = $tran->category;
            if ($cat === null) continue;

            $sums[$cat->cat_id] ??= ['name' => $cat->cat_name, 'type' => $cat->cat_type, 'total' => 0.0];
            $sums[$cat->cat_id]['total'] += (float) $tran->ts_amount;
        }

        return [
            'expense' => $this->rank(array_filter($sums, fn ($r) => $r['type'] === self::EXPENSE)),
            'income' => $this->rank(array_filter($sums, fn ($r) => $r['type'] === self::INCOME)),
        ];
    }

    private function rank(array $rows): array {
        usort($rows, fn ($a, $b) => $b['total'] <=> $a['total']);

        // Past a handful of bars the ranking stops being readable, so the
        // tail is folded into one "อื่น ๆ" row rather than dropped.
        if (count($rows) > self::BREAKDOWN_LIMIT) {
            $tail = array_splice($rows, self::BREAKDOWN_LIMIT - 1);
            $rows[] = [
                'name' => 'อื่น ๆ (' . count($tail) . ' หมวดหมู่)',
                'type' => $tail[0]['type'],
                'total' => array_sum(array_column($tail, 'total')),
            ];
        }

        $total = array_sum(array_column($rows, 'total'));
        // Not simply the first row: the folded "อื่น ๆ" total can add up to
        // more than the largest single category, and would overflow its bar.
        $peak = $rows === [] ? 0.0 : max(array_column($rows, 'total'));

        return array_map(fn ($row) => $row + [
            // share is the number the reader wants; width is bar length,
            // scaled so the largest category fills the row.
            'share' => $total > 0 ? $row['total'] / $total * 100 : 0.0,
            'width' => $peak > 0 ? $row['total'] / $peak * 100 : 0.0,
        ], $rows);
    }

    /**
     * Saving from a filtered view should land back on that same view, so the
     * forms carry the active filters as hidden fields and every redirect
     * replays them. With no filters this is plain route('home').
     */
    private function back_to_view(Request $request): RedirectResponse {
        $cats = $this->get_cat();

        $filters = $this->filters(
            $request->duplicate($request->only(['q', 'from', 'to', 'cat'])),
            $cats
        );

        return redirect()->route('home', $this->filter_query($filters));
    }

    public function store_trans(Request $request): RedirectResponse {
        Transaction::create($this->validate_trans($request));

        return $this->back_to_view($request);
    }

    public function update_trans(Request $request, Transaction $tran): RedirectResponse {
        $tran->update($this->validate_trans($request));

        return $this->back_to_view($request);
    }

    public function destroy_trans(Request $request, Transaction $tran): RedirectResponse {
        $tran->delete();

        return $this->back_to_view($request);
    }

    private function validate_trans(Request $request): array {
        $data = $request->validate([
            'cat_id' => ['required', 'exists:expense_categories,cat_id'],
            'ts_date' => ['required', 'date'],
            'ts_amount' => ['required', 'numeric', 'min:0', 'max:99999999999999.99'],
            'ts_note' => ['nullable', 'string', 'max:16000'],
        ]);

        // A field left out of the request entirely never appears in the
        // validated data, so pin it to null rather than letting callers
        // reach for a key that isn't there.
        $data['ts_note'] ??= null;

        return $data;
    }

    public function store_cat(Request $request): RedirectResponse {
        ExpenseCategory::create($this->validate_cat($request));

        return $this->back_to_view($request);
    }

    public function update_cat(Request $request, ExpenseCategory $cat): RedirectResponse {
        $data = $this->validate_cat($request);

        // The schema has no way to remember what a category's type used to
        // be, so once it has transactions, changing the type would silently
        // reclassify their historical income/expense totals. Block it
        // instead — create a new category if the type needs to change.
        if ($data['cat_type'] !== $cat->cat_type && $cat->transactions()->exists()) {
            return back()->withErrors([
                'cat_type' => 'ไม่สามารถเปลี่ยนประเภทได้ เนื่องจากหมวดหมู่นี้มีรายการอยู่แล้ว',
            ])->withInput();
        }

        $cat->update($data);

        return $this->back_to_view($request);
    }

    public function destroy_cat(Request $request, ExpenseCategory $cat): RedirectResponse {
        $cat->delete();

        return $this->back_to_view($request);
    }

    private function validate_cat(Request $request): array {
        return $request->validate([
            'cat_name' => ['required', 'string', 'max:255'],
            'cat_type' => ['required', Rule::in([self::INCOME, self::EXPENSE])],
        ]);
    }
}
