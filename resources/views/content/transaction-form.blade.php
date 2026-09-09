<form id="transaction-form" class="space-y-5" method="POST"
      action="{{ $editing ? route('trans.update', $editing->ts_id) : route('trans.store') }}">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif
    @include('content.filter-fields')

    <div class="flex items-baseline justify-between gap-3">
        <h2 class="text-base font-semibold">{{ $editing ? 'แก้ไขรายการ' : 'เพิ่มรายการ' }}</h2>
        @if ($editing)
            <a href="{{ route('home', $viewQuery) }}" class="text-sm text-slate-500 hover:text-slate-900">ยกเลิก</a>
        @endif
    </div>

    <div>
        <label for="ts_date" class="block text-xs text-slate-500 mb-1">วันที่</label>
        <input type="date" id="ts_date" name="ts_date" required
               value="{{ old('ts_date', $editing?->ts_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
               class="field">
        @error('ts_date')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="cat_id" class="block text-xs text-slate-500 mb-1">หมวดหมู่</label>
        @if ($cats->isEmpty())
            <p class="text-xs text-amber-600 py-1.5">ยังไม่มีหมวดหมู่ กรุณาเพิ่มหมวดหมู่ก่อนบันทึกรายการ</p>
        @else
            <select id="cat_id" name="cat_id" required class="field">
                <option value="" disabled @selected(old('cat_id', $editing?->cat_id) === null)>-- เลือกหมวดหมู่ --</option>
                <optgroup label="รายรับ">
                    @foreach ($cats->where('cat_type', 'รายรับ') as $cat)
                        <option value="{{ $cat->cat_id }}" @selected((int) old('cat_id', $editing?->cat_id) === $cat->cat_id)>{{ $cat->cat_name }}</option>
                    @endforeach
                </optgroup>
                <optgroup label="รายจ่าย">
                    @foreach ($cats->where('cat_type', 'รายจ่าย') as $cat)
                        <option value="{{ $cat->cat_id }}" @selected((int) old('cat_id', $editing?->cat_id) === $cat->cat_id)>{{ $cat->cat_name }}</option>
                    @endforeach
                </optgroup>
            </select>
        @endif
        @error('cat_id')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="ts_amount" class="block text-xs text-slate-500 mb-1">จำนวนเงิน</label>
        <input type="text" inputmode="decimal" id="ts_amount" name="ts_amount"
               pattern="\d{1,14}(\.\d{1,2})?" placeholder="0.00" required
               value="{{ old('ts_amount', $editing?->ts_amount) }}"
               class="figure text-lg field">
        <p class="text-xs text-slate-400 mt-1">ตัวเลขเต็มไม่เกิน 14 หลัก ทศนิยมไม่เกิน 2 ตำแหน่ง</p>
        @error('ts_amount')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="ts_note" class="block text-xs text-slate-500 mb-1">หมายเหตุ</label>
        <input type="text" id="ts_note" name="ts_note" placeholder="ไม่บังคับ"
               value="{{ old('ts_note', $editing?->ts_note) }}"
               class="field">
        @error('ts_note')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    @if ($editing)
        <a href="#confirm-trans-save"
           class="block w-full rounded-md bg-teal-700 px-4 py-2 text-center text-sm font-medium text-white hover:bg-teal-800">
            บันทึกการแก้ไข
        </a>

        <div id="confirm-trans-save" class="modal-target">
            <div class="bg-white rounded-md shadow-lg p-6 max-w-sm w-full space-y-4 text-left">
                <p class="text-sm text-slate-700">ยืนยันการแก้ไขรายการนี้?</p>
                <div class="flex justify-end gap-4 text-sm">
                    <a href="#" class="text-slate-500 hover:text-slate-900">ยกเลิก</a>
                    <button type="submit" form="transaction-form" class="text-teal-700 font-medium hover:text-teal-800">ยืนยัน</button>
                </div>
            </div>
        </div>
    @else
        <button type="submit"
                class="w-full rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">
            บันทึก
        </button>
    @endif
</form>
