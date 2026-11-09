@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(56px, 7vw, 96px);">
    <div class="container-x" style="max-width: 820px;">
        <p class="eyebrow" style="color: var(--gold-500);">Meeting rooms in</p>
        <h1 style="font-size: clamp(36px, 4.6vw, 56px); margin: 18px 0 20px;">{{ $page->name }}</h1>
        <div class="prose-club" style="color: var(--ivory-100);">
            {!! $page->body !!}
        </div>
        <div style="margin-top: 36px; display: flex; flex-wrap: wrap; gap: 16px;">
            <a href="{{ route('rooms.index') }}" class="btn btn-gold">View all rooms</a>
            <a href="{{ route('contact') }}" class="btn btn-line">Ask about availability</a>
        </div>

        <p style="font-size: 14px; color: var(--mist-300); margin-top: 32px;">
            Serving {{ $page->name }} and nearby: Woolwich, Greenwich, Blackheath, Westcombe Park, Plumstead, Kidbrooke, Eltham, Thamesmead and Abbey Wood.
        </p>
    </div>
</section>
@endsection