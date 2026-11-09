@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(40px, 5vw, 72px);">
    <div class="container-x" style="max-width: 860px;">
        <nav aria-label="Breadcrumb" style="font-size: 14px; color: var(--mist-300); margin-bottom: 20px;">
            <a href="{{ route('home') }}">Home</a> <span aria-hidden="true">/</span>
            <a href="{{ route('journal.index') }}">Journal</a> <span aria-hidden="true">/</span>
            <span aria-current="page" style="color: var(--ivory-100);">{{ $post->title }}</span>
        </nav>

        <p class="eyebrow" style="color: var(--gold-500);">
            <time datetime="{{ $post->published_at->toIso8601String() }}">{{ $post->published_at->format('j F Y') }}</time>
        </p>
        <h1 style="font-size: clamp(36px, 4.6vw, 56px); margin: 14px 0 20px;">{{ $post->title }}</h1>

        @if ($post->author)
            <p style="font-size: 17px; color: var(--mist-300); margin-bottom: 40px;">By {{ $post->author->name }}{{ $post->author->title ? ', '.$post->author->title : '' }}</p>
        @endif

        <div class="prose-club" style="color: var(--ivory-100);">
            {!! $post->body !!}
        </div>
    </div>
</section>

@if ($more->isNotEmpty())
    <section style="background: var(--navy-800); padding-block: clamp(48px, 6vw, 72px);">
        <div class="container-x">
            <h2 style="font-size: 30px; margin-bottom: 24px;">More from the journal</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px;">
                @foreach ($more as $other)
                    <a href="{{ route('journal.show', $other->slug) }}" class="card-dark" style="display: block;">
                        <p class="eyebrow" style="font-size: 12px; color: var(--gold-500);">{{ $other->published_at->format('j M Y') }}</p>
                        <h3 style="font-size: 22px; margin-top: 8px;">{{ $other->title }}</h3>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection