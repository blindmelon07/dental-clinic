@php
    $statePath  = $getStatePath();
    $state      = $getState();
    $isDisabled = $isDisabled();
@endphp

@once
    <style>[x-cloak]{display:none!important}</style>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
@endonce

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-cloak
        x-data="{
            activeTab: 'draw',
            pad: null,
            state: $wire.entangle('{{ $statePath }}'),

            init() {
                this.$watch('activeTab', (tab) => {
                    if (tab === 'draw') this.$nextTick(() => this.initPad());
                });
                this.$watch('state', () => {
                    if (this.pad && ! this.state) this.pad.clear();
                });
                this.$nextTick(() => this.initPad());
            },

            initPad() {
                if (typeof SignaturePad === 'undefined') {
                    setTimeout(() => this.initPad(), 150);
                    return;
                }
                const canvas = this.$refs.canvas;
                if (!canvas) return;
                canvas.width  = canvas.parentElement.offsetWidth || 600;
                canvas.height = 160;
                if (this.pad) { this.pad.off(); }
                this.pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });
                @if(!$isDisabled)
                    this.pad.addEventListener('endStroke', () => {
                        this.state = this.pad.toDataURL();
                    });
                @else
                    this.pad.off();
                @endif
            },

            clearDraw() {
                if (this.pad) this.pad.clear();
                this.state = null;
            },

            handleUpload(event) {
                const file = event.target.files[0];
                if (!file) return;
                if (file.size > 2 * 1024 * 1024) {
                    alert('File is too large. Maximum size is 2MB.');
                    event.target.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.state = e.target.result;
                };
                reader.readAsDataURL(file);
            },

            removeSignature() {
                this.state = null;
                if (this.pad) this.pad.clear();
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            }
        }"
        class="space-y-3"
    >
        @if(! $isDisabled)
        {{-- Tab switcher --}}
        <div class="flex gap-1 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-1 w-fit">
            <button
                type="button"
                @click="activeTab = 'draw'"
                :class="activeTab === 'draw'
                    ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm font-medium'
                    : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                class="px-4 py-1.5 text-sm rounded-md transition-all"
            >
                ✏️ Draw
            </button>
            <button
                type="button"
                @click="activeTab = 'upload'"
                :class="activeTab === 'upload'
                    ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm font-medium'
                    : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                class="px-4 py-1.5 text-sm rounded-md transition-all"
            >
                📎 Upload
            </button>
        </div>

        {{-- Draw tab --}}
        <div x-show="activeTab === 'draw'">
            <canvas
                x-ref="canvas"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white cursor-crosshair"
                style="touch-action: none; display: block;"
            ></canvas>
            <button
                type="button"
                x-on:click="clearDraw()"
                class="mt-2 text-sm text-red-600 hover:text-red-800 hover:underline"
            >
                Clear
            </button>
        </div>

        {{-- Upload tab --}}
        <div x-show="activeTab === 'upload'">
            <div style="border: 2px dashed #d1d5db; border-radius: 0.5rem; padding: 1.25rem; background: #f9fafb; display: flex; flex-direction: column; gap: 0.75rem;">
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">
                    Select a signature image file (PNG, JPG — max 2 MB)
                </p>
                <input
                    x-ref="fileInput"
                    type="file"
                    accept="image/png,image/jpeg,image/gif"
                    style="font-size: 0.875rem; color: #374151; cursor: pointer;"
                    @change="handleUpload($event)"
                />
            </div>
        </div>
        @endif

        {{-- Preview — only rendered in DOM when state is truthy --}}
        <template x-if="state">
            <div class="space-y-1">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Preview</p>
                <div class="inline-block border border-gray-200 dark:border-gray-700 rounded-lg bg-white p-2">
                    <img :src="state" class="max-h-24 max-w-xs object-contain" alt="Signature preview" />
                </div>
                @if(! $isDisabled)
                <div>
                    <button
                        type="button"
                        x-on:click="removeSignature()"
                        class="text-sm text-red-600 hover:text-red-800 hover:underline"
                    >
                        Remove signature
                    </button>
                </div>
                @endif
            </div>
        </template>

        @if($isDisabled)
        <template x-if="!state">
            <div class="text-sm text-gray-400 italic">No signature recorded</div>
        </template>
        @endif
    </div>
</x-dynamic-component>
