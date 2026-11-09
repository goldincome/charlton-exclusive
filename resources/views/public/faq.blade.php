@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(56px, 7vw, 96px);">
    <div class="container-x">
        <p class="eyebrow" style="color: var(--gold-500);">Questions</p>
        <h1 style="font-size: clamp(36px, 4.6vw, 56px); margin: 18px 0 20px;">Frequently asked</h1>
        <p style="font-size: 18px; color: var(--mist-300); max-width: 640px;">If your question is not answered here, write to <a href="mailto:info@charltonexclusiveclub.co.uk" class="link-gold">info@charltonexclusiveclub.co.uk</a>.</p>
    </div>
</section>

<section style="background: var(--ivory-100); color: var(--ink-900); padding-block: clamp(56px, 7vw, 88px);">
    <div class="container-x" style="max-width: 920px;">
        @forelse ($groups as $group => $items)
            <div style="margin-bottom: 48px;">
                <h2 style="font-size: 28px; margin-bottom: 16px;">{{ $group }}</h2>
                <div style="display: grid; gap: 12px;">
                    @foreach ($items as $item)
                        <details style="background: #fff; border: 1px solid #E0D5B8; border-radius: 4px; padding: 20px 22px;">
                            <summary style="font-weight: 600; font-size: 17px; cursor: pointer; min-height: 28px;">{{ $item->question }}</summary>
                            <div style="margin-top: 12px; color: var(--slate-600);">{!! nl2br(e($item->answer)) !!}</div>
                        </details>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="card-light">
                <p>Our FAQ is being prepared. In the meantime, please <a href="{{ route('contact') }}" class="link-claret">contact the club</a>.</p>
            </div>
        @endforelse
    </div>
</section>

<section style="padding-block: 64px; text-align: center;">
    <div class="container-x">
        <h2 style="font-size: clamp(26px, 3vw, 36px);">Still wondering?</h2>
        <p style="margin: 12px auto 24px; max-width: 520px; color: var(--mist-300);">The club secretary will reply within 14 days.</p>
        <a href="{{ route('contact') }}" class="btn btn-gold">Contact the club</a>
    </div>
</section>
@endsection