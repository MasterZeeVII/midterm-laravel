<div class="space-y-4 border-t border-slate-200 pt-8">
    <h2 class="text-base font-semibold">หมวดหมู่</h2>

    <ul class="divide-y divide-slate-100 text-sm">
        @forelse ($cats as $cat)
            @php $isIncome = $cat->cat_type === 'รายรับ'; @endphp
            <li class="flex items-center justify-between gap-3 py-2">
                {{-- The name doubles as a filter: tapping it scopes the whole
                     dashboard to that category. --}}
                <a href="{{ route('home', array_merge($viewQuery, ['cat' => $cat->cat_id])) }}"
                   class="flex min-w-0 items-center gap-2 hover:text-teal-700"
                   title="ดูเฉพาะหมวดหมู่นี้">
                    <span class="h-2 w-2 shrink-0 rounded-sm {{ $isIncome ? 'bg-teal-600' : 'bg-red-600' }}"></span>
                    <span class="truncate">{{ $cat->cat_name }}</span>
                </a>
                <span class="shrink-0 space-x-2 text-slate-400">
                    <a href="{{ route('home', array_merge($viewQuery, ['edit_cat' => $cat->cat_id])) }}" class="hover:text-slate-900">แก้ไข</a>
                    <a href="#delete-cat-{{ $cat->cat_id }}" class="hover:text-red-600">ลบ</a>

                    <div id="delete-cat-{{ $cat->cat_id }}" class="modal-target">
                        <div class="bg-white rounded-md shadow-lg p-6 max-w-sm w-full space-y-4 text-left">
                            <p class="text-sm text-slate-700">
                                ลบหมวดหมู่ "{{ $cat->cat_name }}" ใช่หรือไม่? รายการทั้งหมดในหมวดหมู่นี้จะถูกลบด้วย
                                และไม่สามารถย้อนกลับได้
                            </p>
                            <div class="flex justify-end gap-4 text-sm">
                                <a href="#" class="text-slate-500 hover:text-slate-900">ยกเลิก</a>
                                <form method="POST" action="{{ route('cat.destroy', $cat->cat_id) }}">
                                    @csrf
                                    @method('DELETE')
                                    @include('content.filter-fields')
                                    <button type="submit" class="text-red-600 font-medium hover:text-red-700">ลบ</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </span>
            </li>
        @empty
            <li class="py-2 text-slate-400">ยังไม่มีหมวดหมู่</li>
        @endforelse
    </ul>

    <form id="category-form" class="flex items-end gap-3" method="POST"
          action="{{ $editingCat ? route('cat.update', $editingCat->cat_id) : route('cat.store') }}">
        @csrf
        @if ($editingCat)
            @method('PUT')
        @endif
        @include('content.filter-fields')

        <div class="min-w-0 flex-1">
            <label for="cat_name" class="block text-xs text-slate-500 mb-1">
                {{ $editingCat ? 'แก้ไขชื่อหมวดหมู่' : 'หมวดหมู่ใหม่' }}
            </label>
            <input type="text" id="cat_name" name="cat_name" placeholder="เช่น ค่าอาหาร" required
                   value="{{ old('cat_name', $editingCat?->cat_name) }}"
                   class="field">
            @error('cat_name')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="w-24 shrink-0">
            <label for="cat_type" class="block text-xs text-slate-500 mb-1">ประเภท</label>
            <select id="cat_type" name="cat_type" class="field">
                <option value="รายรับ" @selected(old('cat_type', $editingCat?->cat_type) === 'รายรับ')>รายรับ</option>
                <option value="รายจ่าย" @selected(old('cat_type', $editingCat?->cat_type) === 'รายจ่าย')>รายจ่าย</option>
            </select>
        </div>

        @if ($editingCat)
            <a href="#confirm-cat-save" class="shrink-0 py-1.5 text-sm font-medium text-teal-700 hover:text-teal-800">บันทึก</a>

            <div id="confirm-cat-save" class="modal-target">
                <div class="bg-white rounded-md shadow-lg p-6 max-w-sm w-full space-y-4 text-left">
                    <p class="text-sm text-slate-700">ยืนยันการแก้ไขหมวดหมู่นี้?</p>
                    <div class="flex justify-end gap-4 text-sm">
                        <a href="#" class="text-slate-500 hover:text-slate-900">ยกเลิก</a>
                        <button type="submit" form="category-form" class="text-teal-700 font-medium hover:text-teal-800">ยืนยัน</button>
                    </div>
                </div>
            </div>

            <a href="{{ route('home', $viewQuery) }}" class="shrink-0 py-1.5 text-sm text-slate-500 hover:text-slate-900">ยกเลิก</a>
        @else
            <button type="submit" class="shrink-0 py-1.5 text-sm font-medium text-teal-700 hover:text-teal-800">เพิ่ม</button>
        @endif
    </form>

    @error('cat_type')
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
