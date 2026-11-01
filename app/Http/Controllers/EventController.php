<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Support\SafeHtml;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $showPast = $request->query('when') === 'past';

        return view('public.events.index', [
            'showPast' => $showPast,
            'events' => ($showPast ? Event::query()->past() : Event::query()->upcoming())->paginate(12)->withQueryString(),
        ]);
    }

    public function show(string $slug): View
    {
        $event = Event::query()->published()->where('slug', $slug)->firstOrFail();
        $safeBody = SafeHtml::clean($event->body);

        return view('public.events.show', [
            'event' => $event,
            'safeBody' => $safeBody,
            'description' => $event->summary ?: Str::limit(strip_tags($safeBody), 155),
        ]);
    }
}
