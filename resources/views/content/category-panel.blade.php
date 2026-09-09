<div class="space-y-4 border-t border-slate-200 pt-8">
    <h2 class="text-base font-semibold">หมวดหมู่</h2>

    <ul class="divide-y divide-slate-100 text-sm">
        @forelse ($cats as $cat)
            <li class="flex items-center justify-between gap-3 py-2">
                <span class="flex min-w-0 items-center gap-2">
                    <span class="h-2 w-2 shrink-0 rounded-sm {{ $cat->isExpense() ? 'bg-red-600' : 'bg-teal-600' }}"></span>
                    <span class="truncate">{{ $cat->cat_name }}</span>
                </span>
                <span class="shrink-0 space-x-2 text-slate-400">
                    <a href="{{ route('home', ['edit_cat' => $cat->cat_id]) }}" class="hover:text-slate-900">แก้ไข</a>
                    <span class="px-1 text-slate-300">·</span>
                    <form method="POST" action="{{ route('cat.destroy', $cat->cat_id) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="hover:text-red-600">ลบ</button>
                    </form>
                </span>
            </li>
        @empty
            <li class="py-2 text-slate-400">ยังไม่มีหมวดหมู่</li>
        @endforelse
    </ul>

    <form class="flex items-end gap-3" method="POST"
          action="{{ $editingCat ? route('cat.update', $editingCat->cat_id) : route('cat.store') }}">
        @csrf
        @if ($editingCat)
            @method('PUT')
        @endif

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
            @error('cat_type')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="shrink-0 py-1.5 text-sm font-medium text-teal-700 hover:text-teal-800">
            {{ $editingCat ? 'บันทึก' : 'เพิ่ม' }}
        </button>
        @if ($editingCat)
            <a href="{{ route('home') }}" class="shrink-0 py-1.5 text-sm text-slate-500 hover:text-slate-900">ยกเลิก</a>
        @endif
    </form>
</div>
