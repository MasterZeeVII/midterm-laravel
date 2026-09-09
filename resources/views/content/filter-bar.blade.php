@php
    use Illuminate\Support\Carbon;

    $today = Carbon::today();
    // Preset ranges are plain links — the server resolves the dates, so the
    // shortcuts work without any script on the page.
    $presets = [
        'เดือนนี้' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
        'เดือนที่แล้ว' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
        'ปีนี้' => [$today->copy()->startOfYear(), $today->copy()->endOfYear()],
    ];

@endphp

<section aria-label="ตัวกรอง" class="border-b border-slate-200 pb-6">
    <form method="GET" action="{{ route('home') }}" class="flex flex-wrap items-end gap-x-5 gap-y-4">
        @if ($group !== 'month')
            <input type="hidden" name="group" value="{{ $group }}">
        @endif

        <div class="w-36">
            <label for="filter-from" class="block text-xs text-slate-500 mb-1">ตั้งแต่วันที่</label>
            <input type="date" id="filter-from" name="from" class="field"
                   value="{{ $filters['from']?->format('Y-m-d') }}">
        </div>

        <div class="w-36">
            <label for="filter-to" class="block text-xs text-slate-500 mb-1">ถึงวันที่</label>
            <input type="date" id="filter-to" name="to" class="field"
                   value="{{ $filters['to']?->format('Y-m-d') }}">
        </div>

        <div class="w-48">
            <label for="filter-cat" class="block text-xs text-slate-500 mb-1">หมวดหมู่</label>
            <select id="filter-cat" name="cat" class="field">
                <option value="">ทุกหมวดหมู่</option>
                @foreach ($cats as $cat)
                    <option value="{{ $cat->cat_id }}" @selected($filters['cat'] === $cat->cat_id)>{{ $cat->cat_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-52 flex-1 min-w-[10rem]">
            <label for="filter-q" class="block text-xs text-slate-500 mb-1">ค้นหาหมายเหตุ</label>
            <input type="search" id="filter-q" name="q" placeholder="เช่น ค่ากาแฟตอนเช้า" class="field"
                   value="{{ $filters['q'] }}">
        </div>

        <button type="submit"
                class="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">
            กรองข้อมูล
        </button>

        @if ($hasFilters)
            <a href="{{ route('home', $group !== 'month' ? ['group' => $group] : []) }}"
               class="py-2 text-sm text-slate-500 hover:text-slate-900">ล้างตัวกรอง</a>
        @endif
    </form>

    <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm">
        <span class="text-slate-400">ช่วงที่ใช้บ่อย</span>
        @foreach ($presets as $label => [$start, $end])
            @php
                $active = $filters['from']?->isSameDay($start) && $filters['to']?->isSameDay($end);
            @endphp
            <a href="{{ route('home', array_merge($filterQuery, [
                    'from' => $start->format('Y-m-d'),
                    'to' => $end->format('Y-m-d'),
                ] + ($group !== 'month' ? ['group' => $group] : []))) }}"
               class="{{ $active ? 'text-teal-700 font-medium' : 'text-slate-500 hover:text-slate-900' }}">{{ $label }}</a>
        @endforeach
    </div>
</section>
