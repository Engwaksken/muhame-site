<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('public.contact');
    }

    public function store(Request $request): RedirectResponse
    {
        // Honeypot: the "website" field is hidden from people, so only bots fill it in.
        // Pretend it worked so the bot gets no signal to adapt.
        if (filled($request->input('website'))) {
            return redirect()->route('contact')->with('status', 'Thank you! Your message has been sent.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $message = ContactMessage::create([...$validated, 'ip_address' => $request->ip()]);

        $recipient = SiteSetting::current()->contact_email ?: config('mail.from.address');

        if ($recipient) {
            Mail::to($recipient)->queue(new ContactMessageReceived($message));
        }

        return redirect()->route('contact')->with('status', 'Thank you! Your message has been sent.');
    }
}
