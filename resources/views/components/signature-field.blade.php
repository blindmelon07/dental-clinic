@props(['name', 'label', 'required' => false])

<div class="signature-field" data-name="{{ $name }}" data-required="{{ $required ? '1' : '0' }}">
    <label class="block text-sm font-medium text-slate-700 mb-1.5">
        {{ $label }}
        @if ($required)
            <span class="text-red-500" aria-hidden="true">*</span>
        @endif
    </label>

    <div class="flex gap-1 rounded-lg border border-slate-200 bg-slate-50 p-1 w-fit mb-2">
        <button type="button" data-tab-btn="draw" class="sig-tab-btn px-4 py-1.5 text-sm rounded-md transition-all">✏️ Draw</button>
        <button type="button" data-tab-btn="upload" class="sig-tab-btn px-4 py-1.5 text-sm rounded-md transition-all">📎 Upload</button>
    </div>

    <div data-tab="draw">
        <canvas class="sig-canvas w-full rounded-xl border border-slate-300 bg-white cursor-crosshair" style="touch-action: none; height: 160px; display: block;"></canvas>
        <button type="button" data-action="clear" class="mt-2 text-sm text-red-600 hover:text-red-800 hover:underline">Clear</button>
    </div>

    <div data-tab="upload" class="hidden">
        <div class="border-2 border-dashed border-slate-300 rounded-xl p-5 bg-slate-50">
            <p class="text-sm text-slate-500 mb-2">Select a signature image file (PNG, JPG — max 2MB)</p>
            <input type="file" accept="image/png,image/jpeg,image/gif" data-action="upload" class="text-sm text-slate-700">
        </div>
    </div>

    <div data-preview class="mt-2 hidden">
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Preview</p>
        <div class="inline-block border border-slate-200 rounded-lg bg-white p-2">
            <img data-preview-img class="max-h-24 max-w-xs object-contain" alt="Signature preview">
        </div>
        <div>
            <button type="button" data-action="remove" class="text-sm text-red-600 hover:text-red-800 hover:underline">Remove signature</button>
        </div>
    </div>

    <input type="hidden" name="{{ $name }}" value="{{ old($name) }}" data-hidden-input>
    <p data-sig-error class="hidden mt-1 text-xs text-red-600">This signature is required.</p>
    @error($name) <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
</div>
