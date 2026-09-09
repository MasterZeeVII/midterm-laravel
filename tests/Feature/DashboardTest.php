<?php

namespace Tests\Feature;

use App\Models\ExpenseCategory;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function category(string $name, string $type): ExpenseCategory
    {
        return ExpenseCategory::create(['cat_name' => $name, 'cat_type' => $type]);
    }

    private function tran(ExpenseCategory $cat, string $date, $amount, ?string $note = null): Transaction
    {
        return Transaction::create([
            'cat_id' => $cat->cat_id,
            'ts_date' => $date,
            'ts_amount' => $amount,
            'ts_note' => $note,
        ]);
    }

    // ---------------------------------------------------------------- filters

    public function test_a_date_range_narrows_the_ledger(): void
    {
        $cat = $this->category('เงินเดือน', 'รายรับ');
        $this->tran($cat, '2026-05-10', 100, 'inside_range');
        $this->tran($cat, '2026-08-10', 200, 'outside_range');

        $this->get('/?from=2026-05-01&to=2026-05-31')
            ->assertSee('inside_range')
            ->assertDontSee('outside_range');
    }

    public function test_a_date_range_also_narrows_the_totals(): void
    {
        $cat = $this->category('เงินเดือน', 'รายรับ');
        $this->tran($cat, '2026-05-10', 100);
        $this->tran($cat, '2026-08-10', 2500);

        // Only the May figure is in range, so the 2,500 must not be summed in.
        $this->get('/?from=2026-05-01&to=2026-05-31')
            ->assertSee('฿100.00')
            ->assertDontSee('฿2,600.00');
    }

    public function test_the_range_boundaries_are_inclusive(): void
    {
        $cat = $this->category('เงินเดือน', 'รายรับ');
        $this->tran($cat, '2026-05-01', 1, 'first_day_marker');
        $this->tran($cat, '2026-05-31', 2, 'last_day_marker');

        $this->get('/?from=2026-05-01&to=2026-05-31')
            ->assertSee('first_day_marker')
            ->assertSee('last_day_marker');
    }

    public function test_a_backwards_date_range_is_swapped_rather_than_returning_nothing(): void
    {
        $cat = $this->category('เงินเดือน', 'รายรับ');
        $this->tran($cat, '2026-05-10', 100, 'swapped_marker');

        $this->get('/?from=2026-05-31&to=2026-05-01')
            ->assertOk()
            ->assertSee('swapped_marker');
    }

    public function test_an_unparseable_date_is_ignored_instead_of_erroring(): void
    {
        $cat = $this->category('เงินเดือน', 'รายรับ');
        $this->tran($cat, '2026-05-10', 100, 'still_visible_marker');

        foreach (['not-a-date', '2026-13-45', '05/10/2026', '2026-05'] as $bad) {
            $this->get('/?from=' . urlencode($bad))
                ->assertOk()
                ->assertSee('still_visible_marker');
        }
    }

    public function test_a_category_filter_narrows_the_ledger(): void
    {
        $food = $this->category('ค่าอาหาร', 'รายจ่าย');
        $travel = $this->category('ค่าเดินทาง', 'รายจ่าย');
        $this->tran($food, '2026-05-10', 100, 'food_marker');
        $this->tran($travel, '2026-05-10', 200, 'travel_marker');

        $this->get('/?cat=' . $food->cat_id)
            ->assertSee('food_marker')
            ->assertDontSee('travel_marker');
    }

    public function test_a_category_id_that_no_longer_exists_is_ignored(): void
    {
        $cat = $this->category('ค่าอาหาร', 'รายจ่าย');
        $this->tran($cat, '2026-05-10', 100, 'orphan_filter_marker');

        // A stale bookmark pointing at a deleted category should show the
        // full ledger, not an empty page filtered by something invisible.
        $this->get('/?cat=999999')
            ->assertOk()
            ->assertSee('orphan_filter_marker');
    }

    public function test_filters_compose_with_the_text_search(): void
    {
        $food = $this->category('ค่าอาหาร', 'รายจ่าย');
        $travel = $this->category('ค่าเดินทาง', 'รายจ่าย');

        $this->tran($food, '2026-05-10', 100, 'coffee_marker');
        $this->tran($food, '2026-08-10', 100, 'coffee_out_of_range');
        $this->tran($travel, '2026-05-10', 100, 'coffee_wrong_cat');
        $this->tran($food, '2026-05-11', 100, 'tea_marker');

        $this->get('/?q=coffee&from=2026-05-01&to=2026-05-31&cat=' . $food->cat_id)
            ->assertSee('coffee_marker')
            ->assertDontSee('coffee_out_of_range')
            ->assertDontSee('coffee_wrong_cat')
            ->assertDontSee('tea_marker');
    }

    public function test_malformed_array_filter_params_do_not_crash_the_page(): void
    {
        $this->get('/?from[]=1')->assertOk();
        $this->get('/?to[]=1')->assertOk();
        $this->get('/?cat[]=1')->assertOk();
        $this->get('/?group[]=1')->assertOk();
    }

    public function test_an_unknown_grouping_falls_back_to_monthly(): void
    {
        $cat = $this->category('เงินเดือน', 'รายรับ');
        $this->tran($cat, '2026-05-10', 100);

        // "รายเดือน" renders as plain text only when it is the active choice.
        $this->get('/?group=fortnight')
            ->assertOk()
            ->assertSee('รายเดือน');
    }

    // -------------------------------------------------- filters survive writes

    public function test_saving_a_transaction_from_a_filtered_view_returns_to_that_view(): void
    {
        $cat = $this->category('เงินเดือน', 'รายรับ');

        $response = $this->post('/trans', [
            'cat_id' => $cat->cat_id,
            'ts_date' => '2026-05-10',
            'ts_amount' => '100',
            'from' => '2026-05-01',
            'to' => '2026-05-31',
            'q' => 'coffee',
        ]);

        $response->assertRedirect(route('home', [
            'q' => 'coffee', 'from' => '2026-05-01', 'to' => '2026-05-31',
        ]));
    }

    public function test_deleting_from_a_filtered_view_returns_to_that_view(): void
    {
        $cat = $this->category('เงินเดือน', 'รายรับ');
        $tran = $this->tran($cat, '2026-05-10', 100);

        $this->delete("/trans/{$tran->ts_id}", ['from' => '2026-05-01'])
            ->assertRedirect(route('home', ['from' => '2026-05-01']));
    }

    public function test_an_unfiltered_save_still_redirects_to_the_plain_home_url(): void
    {
        $cat = $this->category('เงินเดือน', 'รายรับ');

        $this->post('/trans', [
            'cat_id' => $cat->cat_id, 'ts_date' => '2026-05-10', 'ts_amount' => '100',
        ])->assertRedirect(route('home'));
    }

    // -------------------------------------------------------------- analytics

    public function test_the_trend_fills_in_a_month_with_no_activity(): void
    {
        $cat = $this->category('เงินเดือน', 'รายรับ');
        $this->tran($cat, '2026-05-10', 100);
        $this->tran($cat, '2026-07-10', 300);

        // June sits between the two and must appear as an empty column
        // rather than being skipped, or the gap reads as "no time passed".
        $this->get('/?group=month')->assertSee('มิ.ย.');
    }

    public function test_the_trend_groups_by_day_when_asked(): void
    {
        $cat = $this->category('เงินเดือน', 'รายรับ');
        $this->tran($cat, '2026-05-10', 100);
        $this->tran($cat, '2026-05-11', 250);

        $response = $this->get('/?group=day&from=2026-05-10&to=2026-05-11');

        // The table view under the chart carries each period's figures.
        $response->assertSee('฿100.00')->assertSee('฿250.00');
    }

    public function test_the_breakdown_ranks_categories_by_total(): void
    {
        $small = $this->category('ค่าอาหาร', 'รายจ่าย');
        $big = $this->category('ค่าเช่าบ้าน', 'รายจ่าย');
        $this->tran($small, '2026-05-10', 100);
        $this->tran($big, '2026-05-10', 900);

        // Scoped to the analytics panel — the sidebar lists the same names in
        // creation order, which would otherwise decide the comparison.
        $panel = $this->analytics_panel($this->get('/')->getContent());

        $this->assertTrue(
            strpos($panel, 'ค่าเช่าบ้าน') < strpos($panel, 'ค่าอาหาร'),
            'the larger category should be ranked first'
        );
    }

    /** The markup between the analytics section and the ledger below it. */
    private function analytics_panel(string $content): string
    {
        $start = strpos($content, 'aria-label="ภาพรวม"');
        $end = strpos($content, 'aria-label="รายการ"');

        $this->assertNotFalse($start, 'analytics section not found');
        $this->assertNotFalse($end, 'ledger section not found');

        return substr($content, $start, $end - $start);
    }

    public function test_the_breakdown_folds_a_long_tail_into_one_row(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $cat = $this->category("หมวด{$i}", 'รายจ่าย');
            $this->tran($cat, '2026-05-10', $i * 10);
        }

        // 12 categories collapse to 7 named + one folded row.
        $this->get('/')->assertSee('อื่น ๆ (5 หมวดหมู่)');
    }

    public function test_no_breakdown_bar_can_overflow_its_track(): void
    {
        // The folded tail (5 × 100 = 500) outweighs the largest single
        // category (400), so bar width must be scaled to the real maximum.
        foreach ([400, 300, 200, 150, 120, 110, 105] as $i => $amount) {
            $this->tran($this->category("ใหญ่{$i}", 'รายจ่าย'), '2026-05-10', $amount);
        }
        for ($i = 0; $i < 5; $i++) {
            $this->tran($this->category("เล็ก{$i}", 'รายจ่าย'), '2026-05-10', 100);
        }

        $panel = $this->analytics_panel($this->get('/')->getContent());

        preg_match_all('/style="width: ([0-9.]+)%"/', $panel, $matches);
        $this->assertNotEmpty($matches[1]);
        foreach ($matches[1] as $width) {
            $this->assertLessThanOrEqual(100.0, (float) $width);
        }
    }

    public function test_the_breakdown_totals_only_the_filtered_slice(): void
    {
        $cat = $this->category('ค่าอาหาร', 'รายจ่าย');
        $this->tran($cat, '2026-05-10', 100);
        $this->tran($cat, '2026-08-10', 5000);

        $this->get('/?from=2026-05-01&to=2026-05-31')
            ->assertSee('฿100.00')
            ->assertDontSee('฿5,100.00');
    }

    public function test_a_category_name_containing_html_is_escaped_in_the_analytics_panel(): void
    {
        $cat = $this->category('</script><script>alert(1)</script>', 'รายจ่าย');
        $this->tran($cat, '2026-05-10', 100);

        $this->get('/')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('</script><script>alert(1)</script>');
    }

    // ------------------------------------------------------------ date display

    public function test_todays_rows_are_headed_with_a_relative_label(): void
    {
        $cat = $this->category('เงินเดือน', 'รายรับ');
        $this->tran($cat, Carbon::today()->format('Y-m-d'), 100);
        $this->tran($cat, Carbon::yesterday()->format('Y-m-d'), 100);

        $this->get('/')->assertSee('วันนี้')->assertSee('เมื่อวาน');
    }

    public function test_older_rows_are_headed_with_a_thai_buddhist_era_date(): void
    {
        $cat = $this->category('เงินเดือน', 'รายรับ');
        $this->tran($cat, '2026-05-10', 100);

        // 10 May 2026 CE = 10 พ.ค. 2569 BE, on a Sunday.
        $this->get('/')->assertSee('10 พ.ค. 2569');
    }

    public function test_each_day_shows_its_own_net(): void
    {
        $income = $this->category('เงินเดือน', 'รายรับ');
        $expense = $this->category('ค่าอาหาร', 'รายจ่าย');
        $this->tran($income, '2026-05-10', 500);
        $this->tran($expense, '2026-05-10', 200);

        // 500 in, 200 out on the same day nets +300.
        $this->get('/')->assertSee('+฿300.00');
    }

    public function test_a_day_that_nets_negative_is_marked_as_such(): void
    {
        $income = $this->category('เงินเดือน', 'รายรับ');
        $expense = $this->category('ค่าอาหาร', 'รายจ่าย');
        $this->tran($income, '2026-05-10', 100);
        $this->tran($expense, '2026-05-10', 400);

        $this->get('/')->assertSee('−฿300.00');
    }

    public function test_the_empty_state_tells_you_what_to_do_next(): void
    {
        $this->get('/')->assertSee('ยังไม่มีรายการ เพิ่มรายการแรกได้จากแบบฟอร์มทางซ้าย');
    }

    public function test_a_filtered_empty_state_offers_to_widen_the_filter(): void
    {
        $cat = $this->category('เงินเดือน', 'รายรับ');
        $this->tran($cat, '2026-05-10', 100);

        $this->get('/?from=2030-01-01')
            ->assertSee('ไม่พบรายการที่ตรงกับตัวกรอง ลองขยายช่วงวันที่หรือล้างตัวกรอง');
    }

    public function test_the_edit_link_keeps_the_active_filters(): void
    {
        $cat = $this->category('เงินเดือน', 'รายรับ');
        $tran = $this->tran($cat, '2026-05-10', 100);

        // Clicking edit from a filtered ledger must not drop you back to the
        // unfiltered view.
        // assertSee escapes the needle, matching the &amp; in the rendered href.
        $this->get('/?from=2026-05-01&cat=' . $cat->cat_id)
            ->assertSee(route('home', [
                'from' => '2026-05-01', 'cat' => $cat->cat_id, 'edit' => $tran->ts_id,
            ]));
    }
}
