<section aria-label="รายการ">
    <div class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1">
        <h2 class="text-base font-semibold">รายการ</h2>
        @if ($hasFilters)
            <p class="text-sm text-slate-500">แสดงเฉพาะรายการที่ตรงกับตัวกรอง</p>
        @endif
    </div>

    @if (count($ledger) === 0)
        <p class="mt-4 border-t border-slate-200 py-16 text-center text-sm text-slate-400">
            {{ $hasFilters ? 'ไม่พบรายการที่ตรงกับตัวกรอง ลองขยายช่วงวันที่หรือล้างตัวกรอง' : 'ยังไม่มีรายการ เพิ่มรายการแรกได้จากแบบฟอร์มทางซ้าย' }}
        </p>
    @else
        {{-- The money and action columns don't wrap, so on a narrow screen the
             table scrolls inside this box rather than widening the page. --}}
        <div class="mt-4 overflow-x-auto">
        <table class="w-full min-w-[34rem] border-collapse text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-left text-slate-500">
                    <th class="py-2 pr-4 font-normal">หมวดหมู่</th>
                    <th class="py-2 pr-4 font-normal">หมายเหตุ</th>
                    <th class="py-2 pr-4 text-right font-normal">จำนวนเงิน</th>
                    <th class="py-2 text-right font-normal">จัดการ</th>
                </tr>
            </thead>

            {{-- One tbody per day, so rows read like entries under a passbook
                 date line while the money column stays aligned throughout. --}}
            @foreach ($ledger as $day)
                <tbody>
                    <tr class="bg-slate-50">
                        <th colspan="2" class="py-2 pr-4 text-left font-medium">
                            @if ($day['relative'])
                                {{ $day['relative'] }}
                                <span class="ml-2 font-normal text-slate-400">{{ $day['full'] }}</span>
                            @else
                                {{ $day['full'] }}
                            @endif
                        </th>
                        <th class="figure py-2 pr-4 text-right font-medium {{ $day['net'] < 0 ? 'text-red-600' : 'text-teal-700' }}">
                            {{ $day['net'] < 0 ? '−' : '+' }}฿{{ number_format(abs($day['net']), 2) }}
                        </th>
                        <th class="py-2 text-right text-xs font-normal text-slate-400">
                            {{ count($day['rows']) }} รายการ
                        </th>
                    </tr>

                    @foreach ($day['rows'] as $data)
                        @php $isIncome = $data->category->cat_type === 'รายรับ'; @endphp
                        <tr class="border-b border-slate-100">
                            <td class="py-2.5 pr-4 whitespace-nowrap">{{ $data->category->cat_name }}</td>
                            <td class="py-2.5 pr-4 text-slate-500">{{ $data->ts_note }}</td>
                            <td class="figure py-2.5 pr-4 text-right font-medium whitespace-nowrap {{ $isIncome ? 'text-teal-700' : 'text-red-600' }}">
                                {{ $isIncome ? '+' : '−' }}฿{{ number_format($data->ts_amount, 2) }}
                            </td>
                            <td class="py-2.5 text-right whitespace-nowrap text-slate-400">
                                <a href="{{ route('home', array_merge($viewQuery, ['edit' => $data->ts_id])) }}" class="hover:text-slate-900">แก้ไข</a>
                                <span class="px-1 text-slate-300">·</span>
                                <a href="#delete-trans-{{ $data->ts_id }}" class="hover:text-red-600">ลบ</a>

                                <div id="delete-trans-{{ $data->ts_id }}" class="modal-target">
                                    <div class="bg-white rounded-md shadow-lg p-6 max-w-sm w-full space-y-4 text-left">
                                        <p class="text-sm text-slate-700">ลบรายการนี้ใช่หรือไม่? การลบไม่สามารถย้อนกลับได้</p>
                                        <div class="flex justify-end gap-4 text-sm">
                                            <a href="#" class="text-slate-500 hover:text-slate-900">ยกเลิก</a>
                                            <form method="POST" action="{{ route('trans.destroy', $data->ts_id) }}">
                                                @csrf
                                                @method('DELETE')
                                                @include('content.filter-fields')
                                                <button type="submit" class="text-red-600 font-medium hover:text-red-700">ลบ</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            @endforeach
        </table>
        </div>
    @endif
</section>
