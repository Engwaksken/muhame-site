<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    private const THANKS = 'Thanks for subscribing! We\'ll be in touch.';

    public function store(Request $request): RedirectResponse
    {
        // Return to the page the form was on, but never to another site.
        $previous = strtok(url()->previous(), '#');
        $previous = str_starts_with($previous, url('/')) ? $previous : route('home');
        $back = fn () => redirect()->to($previous.'#newsletter');

        // Honeypot: only bots fill in the hidden "website" field.
        if (filled($request->input('website'))) {
            return $back()->with('newsletter_status', self::THANKS);
        }

        $validated = $request->validateWithBag('newsletter', [
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        // Same response whether or not the address is already subscribed,
        // so the form can't be used to check who is on the list.
        Subscriber::query()->firstOrCreate(
            ['email' => Str::lower(trim($validated['email']))],
            ['name' => $validated['name'] ?? null, 'ip_address' => $request->ip()],
        );

        return $back()->with('newsletter_status', self::THANKS);
    }
}
