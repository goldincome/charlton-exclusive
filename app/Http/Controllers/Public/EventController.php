<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Contracts\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        return view('public.events.index', [
            'upcoming' => Event::query()->active()->upcoming()->get(),
            'past' => Event::query()->where('ends_at', '<', now())->orderByDesc('starts_at')->take(6)->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $event = Event::query()->where('slug', $slug)->firstOrFail();

        return view('public.events.show', [
            'event' => $event,
            'upcoming' => Event::query()->active()->upcoming()->whereKeyNot($event->id)->take(3)->get(),
        ]);
    }
}
