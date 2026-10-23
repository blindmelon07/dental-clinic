<?php

namespace Tests\Feature;

use App\Mail\ContactMessageMail;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_sends_the_message_to_the_clinic(): void
    {
        Mail::fake();
        SiteSetting::instance()->update(['email' => 'clinic@example.test']);

        $this->from('/contact')->post('/contact', [
            'name'    => 'Jane Doe',
            'email'   => 'jane@example.test',
            'subject' => 'Schedule question',
            'message' => 'Are you open on Saturdays?',
        ])->assertRedirect('/contact')->assertSessionHas('success');

        Mail::assertSent(ContactMessageMail::class, fn (ContactMessageMail $mail) =>
            $mail->hasTo('clinic@example.test')
            && $mail->hasSubject('New Contact Message: Schedule question')
        );
    }
}
