<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(): View
    {
        return view('admin.events.index', [
            'events' => Event::query()->orderByDesc('starts_at')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.events.form', ['event' => new Event()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Event::query()->create($this->validated($request));

        return redirect()->route('admin.events.index')->with('status', 'Event created.');
    }

    public function edit(Event $event): View
    {
        return view('admin.events.form', ['event' => $event]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $event->update($this->validated($request, $event));

        return back()->with('status', 'Event updated.');
    }

    public function cancel(Event $event): RedirectResponse
    {
        $event->update(['cancelled_at' => now()]);

        return back()->with('status', 'Event cancelled. Confirmed ticket holders will be refunded in full.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.events.index')->with('status', 'Event deleted.');
    }

    private function validated(Request $request, ?Event $event = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('events', 'slug')->ignore($event?->id)],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'capacity' => ['required', 'integer', 'min:1'],
            'tickets_per_booking' => ['required', 'integer', 'min:1'],
            'price_per_ticket' => ['required', 'integer', 'min:0'],
            'member_discount_type' => ['required', 'in:percent,flat'],
            'member_discount_value' => ['required', 'numeric', 'min:0'],
            'venue' => ['nullable', 'string', 'max:120'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:190'],
            'members_only' => ['boolean'],
            'active' => ['boolean'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['members_only'] = $request->boolean('members_only');
        $data['active'] = $request->boolean('active', true);

        return $data;
    }
}