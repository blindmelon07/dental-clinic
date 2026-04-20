<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Clinic;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = Auth::user()->clinic_id ?? Clinic::first()?->id;
        $data['booked_by'] = Auth::id();
        $data['appointment_number'] = Appointment::generateNumber();
        return $data;
    }
}
