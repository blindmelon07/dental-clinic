<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Observers\AppointmentObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Appointment::observe(AppointmentObserver::class);
    }
}
