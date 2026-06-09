<div>
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="font-heading text-3xl font-bold text-slate-900">My Profile</h1>
        <p class="text-slate-500 mt-1.5">Manage your profile picture and account details.</p>
    </div>

    @if($this->patient)
    <div class="max-w-2xl space-y-6">

        {{-- Profile Picture Card --}}
        <div class="card p-8">
            <h2 class="font-heading text-lg font-semibold text-slate-900 mb-6">Profile Picture</h2>

            <div class="flex flex-col sm:flex-row items-center gap-8">

                {{-- Avatar preview --}}
                <div class="flex-shrink-0 relative group">
                    @if($photo)
                        {{-- Live preview of new upload --}}
                        <img src="{{ $photo->temporaryUrl() }}"
                             alt="Preview"
                             class="w-32 h-32 rounded-full object-cover border-4 border-cyan-200 shadow-md">
                        <div class="absolute -bottom-1 -right-1 w-7 h-7 bg-cyan-500 rounded-full flex items-center justify-center shadow">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    @elseif($this->patient->photo)
                        {{-- Existing photo --}}
                        <img src="{{ $this->patient->photoUrl() }}"
                             alt="{{ $this->patient->first_name }}"
                             class="w-32 h-32 rounded-full object-cover border-4 border-cyan-200 shadow-md">
                    @else
                        {{-- Initials placeholder --}}
                        <div class="w-32 h-32 rounded-full bg-gradient-to-br from-cyan-400 to-teal-500 flex items-center justify-center border-4 border-cyan-200 shadow-md">
                            <span class="text-4xl font-bold text-white font-heading">
                                {{ strtoupper(substr($this->patient->first_name, 0, 1)) }}
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Upload controls --}}
                <div class="flex-1 w-full">
                    <form wire:submit="uploadPhoto">

                        <div
                            x-data="{ dragging: false }"
                            x-on:dragover.prevent="dragging = true"
                            x-on:dragleave.prevent="dragging = false"
                            x-on:drop.prevent="dragging = false"
                            :class="dragging ? 'border-cyan-400 bg-cyan-50' : 'border-slate-300 bg-slate-50 hover:border-cyan-400 hover:bg-cyan-50/50'"
                            class="relative border-2 border-dashed rounded-2xl p-6 text-center transition-colors duration-150 cursor-pointer"
                        >
                            <input
                                type="file"
                                wire:model="photo"
                                accept="image/jpeg,image/png,image/webp"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                id="photo-upload"
                            >

                            <div class="pointer-events-none">
                                <div class="w-10 h-10 bg-cyan-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-slate-700">
                                    Drag & drop or <span class="text-cyan-600">click to browse</span>
                                </p>
                                <p class="text-xs text-slate-400 mt-1">JPG, PNG, or WEBP — max 10 MB</p>
                            </div>
                        </div>

                        @error('photo')
                            <p class="mt-2 text-xs text-red-600 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.834-1.964-.834-2.732 0L3.07 16.5C2.3 17.333 3.262 19 4.802 19z"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror

                        <div class="flex items-center gap-3 mt-4">
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="photo,uploadPhoto"
                                @class([
                                    'btn-primary flex-1 justify-center',
                                    'opacity-50 cursor-not-allowed' => !$photo,
                                ])
                                @disabled(!$photo)
                            >
                                <span wire:loading.remove wire:target="uploadPhoto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Save Picture
                                </span>
                                <span wire:loading wire:target="uploadPhoto" class="flex items-center gap-2">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    Uploading…
                                </span>
                            </button>

                            @if($this->patient->photo)
                                <button
                                    type="button"
                                    wire:click="removePhoto"
                                    wire:confirm="Remove your profile picture?"
                                    class="btn-secondary text-red-600 border-red-200 hover:bg-red-50"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Remove
                                </button>
                            @endif
                        </div>

                        {{-- Upload progress bar --}}
                        <div wire:loading wire:target="photo" class="mt-3">
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-cyan-500 rounded-full animate-pulse w-2/3"></div>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Preparing image…</p>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- Account Info (read-only) --}}
        <div class="card p-8">
            <h2 class="font-heading text-lg font-semibold text-slate-900 mb-5">Account Information</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Full Name</dt>
                    <dd class="text-sm font-medium text-slate-900">{{ $this->patient->full_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Patient ID</dt>
                    <dd class="text-sm font-medium text-cyan-700">{{ $this->patient->patient_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Email</dt>
                    <dd class="text-sm font-medium text-slate-900">{{ auth()->user()->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Phone</dt>
                    <dd class="text-sm font-medium text-slate-900">{{ $this->patient->phone }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Date of Birth</dt>
                    <dd class="text-sm font-medium text-slate-900">{{ $this->patient->date_of_birth->format('F d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Gender</dt>
                    <dd class="text-sm font-medium text-slate-900 capitalize">{{ $this->patient->gender->value }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Address</dt>
                    <dd class="text-sm font-medium text-slate-900">{{ $this->patient->address }}, {{ $this->patient->city }}</dd>
                </div>
            </dl>
            <p class="text-xs text-slate-400 mt-6">To update your personal details, please contact the clinic.</p>
        </div>

    </div>
    @endif
</div>
