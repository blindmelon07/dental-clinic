@props(['name', 'options'])

<div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2">
    @foreach ($options as $value => $optionLabel)
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="{{ $name }}[]" value="{{ $value }}"
                   @checked(collect(old($name, []))->contains($value))
                   class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
            {{ $optionLabel }}
        </label>
    @endforeach
</div>
