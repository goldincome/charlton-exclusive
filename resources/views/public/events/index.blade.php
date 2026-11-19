@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(56px, 7vw, 96px);">
    <div class="container-x">
        <p class="eyebrow" style="color: var(--gold-500);">What’s on</p>
        <h1 style="font-size: clamp(38px, 5vw, 60px); margin: 18px 0 22px;">The club <em style="color: var(--champagne-200);">diary</em></h1>
        <p style="font-size: 19px; color: var(--mist-300); max-width: 620px;">Ticketed events are requested online and confirmed after payment. Members receive their published discount automatically.</p>
    </div>
</section>

<section style="background: var(--ivory-100); color: var(--ink-900); padding-block: clamp(56px, 7vw, 88px);">
    <div class="container-x">
        <h2 style="font-size: 30px; margin-bottom: 28px;">Upcoming</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
            @forelse ($upcoming as $event)
                <article class="card-light" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                    <div style="height: 180px; background: #E9E1CB; overflow: hidden; display: flex; align-items: center; justify-content: center; color: var(--slate-600);">
                        @if ($event->og_image)
                            <img src="{{ route('media.public', $event->og_image) }}" alt="{{ $event->name }}" width="400" height="180" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                        @else
                            [Event image]
                        @endif
                    </div>
                    <div style="padding: 24px; display: flex; flex-direction: column; flex: 1;">
                        <p class="eyebrow" style="font-size: 12px; color: var(--claret-700);">
                            <time datetime="{{ $event->starts_at->toIso8601String() }}">{{ $event->starts_at->format('D j M · H:i') }}</time>
                        </p>
                        <h3 style="font-size: 24px; margin: 10px 0 8px;">{{ $event->name }}</h3>
                        <p style="color: var(--slate-600); margin-bottom: 16px;">{{ \Illuminate\Support\Str::limit($event->description, 130) }}</p>
                        <p style="font-weight: 600; margin-bottom: 16px;">
                            £{{ number_format($event->price_per_ticket / 100, 2) }} per ticket
                            @if ($event->members_only)<span class="badge badge-gold" style="margin-left: 8px; background: rgba(122,31,46,.08); color: var(--claret-700); border-color: rgba(122,31,46,.3);">Members only</span>@endif
                        </p>
                        <a href="{{ route('events.show', $event->slug) }}" class="link-claret" style="margin-top: auto;">Event details</a>
                    </div>
                </article>
            @empty
                <div class="card-light"><p>The next season is being planned. <a href="{{ route('contact') }}" class="link-claret">Ask what’s coming up.</a></p></div>
            @endforelse
        </div>
    </div>
</section>

@if ($past->isNotEmpty())
    <section style="padding-block: clamp(48px, 6vw, 80px);">
        <div class="container-x">
            <h2 style="font-size: 30px; margin-bottom: 24px;">Recently at the club</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
                @foreach ($past as $event)
                    <div class="card-dark">
                        <p class="eyebrow" style="font-size: 12px; color: var(--gold-500);">{{ $event->starts_at->format('M Y') }}</p>
                        <h3 style="font-size: 21px; margin-top: 8px;">{{ $event->name }}</h3>
                        <a href="{{ route('events.show', $event->slug) }}" class="link-gold" style="display: inline-block; margin-top: 12px;">See the event</a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection