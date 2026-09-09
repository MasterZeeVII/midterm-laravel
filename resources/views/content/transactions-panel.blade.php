<section aria-label="รายการ">
    <h2 class="text-base font-semibold mb-4">รายการ</h2>

    @if ($trans->isEmpty())
        <p class="border-t border-slate-200 py-16 text-center text-sm text-slate-400">
            ยังไม่มีรายการ เพิ่มรายการแรกได้จากแบบฟอร์มทางซ้าย
        </p>
    @else
        <div class="overflow-x-auto">
        <table class="w-full min-w-[34rem] border-collapse text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-left text-slate-500">
                    <th class="py-2 pr-4 font-normal">วันที่</th>
                    <th class="py-2 pr-4 font-normal">หมวดหมู่</th>
                    <th class="py-2 pr-4 font-normal">หมายเหตุ</th>
                    <th class="py-2 pr-4 text-right font-normal">จำนวนเงิน</th>
                    <th class="py-2 text-right font-normal">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($trans as $tran)
                    <tr class="border-b border-slate-100">
                        <td class="py-2.5 pr-4 whitespace-nowrap text-slate-500">{{ $tran->ts_date->format('d/m/Y') }}</td>
                        <td class="py-2.5 pr-4 whitespace-nowrap">{{ $tran->category?->cat_name ?? '—' }}</td>
                        <td class="py-2.5 pr-4 text-slate-500">{{ $tran->ts_note }}</td>
                        <td class="figure py-2.5 pr-4 text-right font-medium whitespace-nowrap {{ $tran->category?->isExpense() ? 'text-red-600' : 'text-teal-700' }}">
                            {{ $tran->category?->isExpense() ? '−' : '+' }}฿{{ number_format($tran->ts_amount, 2) }}
                        </td>
                        <td class="py-2.5 text-right whitespace-nowrap text-slate-400">
                            <a href="{{ route('home', ['edit' => $tran->ts_id]) }}" class="hover:text-slate-900">แก้ไข</a>
                            <span class="px-1 text-slate-300">·</span>
                            <form method="POST" action="{{ route('trans.destroy', $tran->ts_id) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="hover:text-red-600">ลบ</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
</section>
