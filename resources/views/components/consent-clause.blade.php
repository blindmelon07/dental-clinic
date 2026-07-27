@props(['name', 'title', 'description'])

<div class="border border-slate-100 rounded-xl p-4">
    <h4 class="font-heading text-sm font-semibold text-slate-900 mb-1.5">{{ $title }}</h4>
    <p class="text-xs text-slate-500 leading-relaxed mb-3">{{ $description }}</p>
    <div class="max-w-[140px]">
        <label for="{{ $name }}" class="block text-xs font-medium text-slate-700 mb-1">Initial</label>
        <input id="{{ $name }}" type="text" name="{{ $name }}" value="{{ old($name) }}" maxlength="10" class="input-field">
    </div>
</div>
