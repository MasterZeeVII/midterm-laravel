<header class="border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-6 pt-6 pb-7">
        <h1 class="text-lg font-semibold tracking-tight">GeepGoop</h1>
        <p class="text-sm text-slate-500">บันทึกรายรับ-รายจ่าย</p>

        <div class="mt-5 grid grid-cols-3 gap-4 text-center sm:text-left">
            <div>
                <p class="text-sm text-slate-500">รายรับ</p>
                <p class="figure mt-1 text-xl font-semibold text-teal-700">฿{{ number_format($totals['income'], 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-500">รายจ่าย</p>
                <p class="figure mt-1 text-xl font-semibold text-red-600">฿{{ number_format($totals['expense'], 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-500">คงเหลือ</p>
                <p class="figure mt-1 text-xl font-semibold {{ $totals['balance'] < 0 ? 'text-red-600' : 'text-teal-700' }}">
                    ฿{{ number_format($totals['balance'], 2) }}
                </p>
            </div>
        </div>
    </div>
</header>
