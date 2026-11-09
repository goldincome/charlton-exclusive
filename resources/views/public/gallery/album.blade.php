@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(40px, 5vw, 72px);">
    <div class="container-x">
        <nav aria-label="Breadcrumb" style="font-size: 14px; color: var(--mist-300); margin-bottom: 20px;">
            <a href="{{ route('home') }}">Home</a> <span aria-hidden="true">/</span>
            <a href="{{ route('gallery.index') }}">Gallery</a> <span aria-hidden="true">/</span>
            <span aria-current="page" style="color: var(--ivory-100);">{{ $album->name }}</span>
        </nav>

        <h1 style="font-size: clamp(36px, 4.6vw, 56px); margin: 0 0 12px;">{{ $album->name }}</h1>
        @if ($album->description)
            <p style="font-size: 17px; color: var(--mist-300); max-width: 640px; margin-bottom: 32px;">{{ $album->description }}</p>
        @endif

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; margin-top: 32px;">
            @forelse ($photos as $photo)
                <a href="{{ route('gallery.photo', $photo) }}" data-lightbox="{{ route('gallery.photo', $photo) }}" data-alt="{{ $photo->alt_text }}" style="display: block; border-radius: 4px; overflow: hidden; aspect-ratio: 4/3; background: var(--navy-800);">
                    <img src="{{ route('gallery.thumb', $photo) }}" alt="{{ $photo->alt_text ?: $album->name }}" width="400" height="300" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                </a>
            @empty
                <div class="card-dark" style="grid-column: 1 / -1;"><p style="color: var(--mist-300);">This album will contain photographs from {{ $album->name }}.</p></div>
            @endforelse
        </div>

        @if ($photos->hasPages())
            <div style="margin-top: 40px;">{{ $photos->links() }}</div>
        @endif
    </div>
</section>
@endsection