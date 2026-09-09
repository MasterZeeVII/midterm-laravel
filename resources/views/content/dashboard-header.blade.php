@php
    // Both bars share one scale so their lengths are directly comparable.
    $peak = max($totals['income'], $totals['expense']);
    $incomeWidth = $peak > 0 ? $totals['income'] / $peak * 100 : 0;
    $expenseWidth = $peak > 0 ? $totals['expense'] / $peak * 100 : 0;
    $spentShare = $totals['income'] > 0 ? $totals['expense'] / $totals['income'] * 100 : null;
@endphp

<header class="border-b border-slate-200">
    <div class="mx-auto max-w-[1600px] px-6 pt-6 pb-7">

        <div class="flex flex-wrap items-baseline gap-x-4 gap-y-1">
            <h1 class="text-lg font-semibold tracking-tight">GeepGoop</h1>
            <p class="text-sm text-slate-500">บันทึกรายรับ-รายจ่าย</p>
            <p class="ml-auto text-sm text-slate-500">
                {{ $rangeLabel }}<span class="text-slate-300 mx-2">|</span>{{ number_format($totals['count']) }} รายการ
            </p>
        </div>

        <div class="mt-6 grid gap-x-12 gap-y-6 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">

            <!-- Income against expense, drawn on a shared scale. Length is the
                 comparison; the figures are the exact values. -->
            <div class="space-y-3 max-w-2xl">
                <div class="flex items-center gap-4">
                    <span class="w-16 shrink-0 text-sm text-slate-500">รายรับ</span>
                    <span class="flex-1 h-2 bg-slate-100 rounded-sm overflow-hidden">
                        <span class="block h-full bg-teal-600 rounded-sm" style="width: {{ $incomeWidth }}%"></span>
                    </span>
                    <span class="figure w-36 shrink-0 text-right text-sm font-medium text-teal-700">
                        ฿{{ number_format($totals['income'], 2) }}
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="w-16 shrink-0 text-sm text-slate-500">รายจ่าย</span>
                    <span class="flex-1 h-2 bg-slate-100 rounded-sm overflow-hidden">
                        <span class="block h-full bg-red-600 rounded-sm" style="width: {{ $expenseWidth }}%"></span>
                    </span>
                    <span class="figure w-36 shrink-0 text-right text-sm font-medium text-red-600">
                        ฿{{ number_format($totals['expense'], 2) }}
                    </span>
                </div>

                @if ($spentShare !== null)
                    <p class="text-xs text-slate-500 pl-20">
                        ใช้ไป {{ number_format($spentShare) }}% ของรายรับในช่วงนี้
                    </p>
                @endif
            </div>

            <div class="md:text-right md:border-l md:border-slate-200 md:pl-12">
                <p class="text-sm text-slate-500">คงเหลือ</p>
                <p class="mt-1 text-4xl font-semibold tracking-tight {{ $totals['balance'] < 0 ? 'text-red-600' : 'text-teal-700' }}">
                    ฿{{ number_format($totals['balance'], 2) }}
                </p>
            </div>
        </div>
    </div>
</header>
