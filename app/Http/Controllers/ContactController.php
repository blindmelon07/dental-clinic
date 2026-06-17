<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageMail;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:150'],
            'email'   => ['required', 'email', 'max:150'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $recipient = SiteSetting::instance()->email;

        if ($recipient) {
            Mail::to($recipient)->send(new ContactMessageMail(
                name: $data['name'],
                email: $data['email'],
                phone: $data['phone'] ?? null,
                subject: $data['subject'],
                messageBody: $data['message'],
            ));
        }

        return back()->with('success', "Thanks for reaching out! We'll get back to you as soon as possible.");
    }
}
