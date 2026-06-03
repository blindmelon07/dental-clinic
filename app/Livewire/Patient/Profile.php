<?php

namespace App\Livewire\Patient;

use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public $photo;

    #[Computed]
    public function patient(): ?Patient
    {
        return Patient::where('user_id', Auth::id())->first();
    }

    public function uploadPhoto(): void
    {
        $this->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $patient = $this->patient;

        if (! $patient) {
            $this->addError('photo', 'Patient record not found.');
            return;
        }

        // Delete old photo from storage
        if ($patient->photo) {
            Storage::disk('public')->delete($patient->photo);
        }

        $path = $this->photo->store('patient-photos', 'public');

        $patient->update(['photo' => $path]);

        $this->photo = null;
        $this->dispatch('photo-updated');
        session()->flash('success', 'Profile picture updated successfully.');
    }

    public function removePhoto(): void
    {
        $patient = $this->patient;

        if (! $patient || ! $patient->photo) {
            return;
        }

        Storage::disk('public')->delete($patient->photo);
        $patient->update(['photo' => null]);

        session()->flash('success', 'Profile picture removed.');
    }

    public function render()
    {
        return view('livewire.patient.profile')
            ->layout('layouts.patient');
    }
}
