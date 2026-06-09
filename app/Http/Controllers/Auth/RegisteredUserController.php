<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users',
            'password'   => 'required|string|min:8|confirmed',
            'phone'      => 'required|string|max:20',
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'date_of_birth' => 'required|date|before:today',
            'gender'     => 'required|in:male,female,other',
            'address'    => 'required|string',
            'city'       => 'required|string|max:100',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
            'phone'    => $request->phone,
        ]);

        $user->assignRole('patient');

        $clinic = Clinic::where('is_active', true)->first() ?? Clinic::first();

        if ($clinic) {
            $user->update(['clinic_id' => $clinic->id]);

            Patient::create([
                'user_id'        => $user->id,
                'clinic_id'      => $clinic->id,
                'patient_number' => 'PT-' . date('Ymd') . '-' . str_pad(
                    Patient::whereDate('created_at', today())->count() + 1,
                    4, '0', STR_PAD_LEFT
                ),
                'first_name'     => $request->first_name,
                'last_name'      => $request->last_name,
                'date_of_birth'  => $request->date_of_birth,
                'gender'         => $request->gender,
                'phone'          => $request->phone,
                'email'          => $request->email,
                'address'        => $request->address,
                'city'           => $request->city,
            ]);
        }

        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            Log::error('Registered event failed: ' . $e->getMessage(), ['user_id' => $user->id]);
        }

        try {
            $user->load('patient');
            Mail::to($user->email)->send(new WelcomeMail($user));
        } catch (\Throwable $e) {
            Log::error('WelcomeMail failed: ' . $e->getMessage(), ['user_id' => $user->id]);
        }

        Auth::login($user);

        return redirect()->route('patient.dashboard');
    }
}
