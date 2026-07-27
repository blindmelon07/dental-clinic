@props(['name', 'label', 'required' => false])

<label class="flex items-center gap-3 py-1.5 cursor-pointer select-none">
    <span class="relative inline-flex items-center shrink-0">
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name)) @required($required) class="peer sr-only">
        <span class="w-10 h-6 rounded-full bg-slate-200 peer-checked:bg-cyan-600 transition-colors duration-150"></span>
        <span class="absolute left-1 top-1 w-4 h-4 rounded-full bg-white shadow-sm transition-transform duration-150 peer-checked:translate-x-4"></span>
    </span>
    <span class="text-sm text-slate-700">{{ $label }}</span>
    @if ($required)
        <span class="text-red-500" aria-hidden="true">*</span>
    @endif
</label>
