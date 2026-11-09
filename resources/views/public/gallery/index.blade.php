@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(56px, 7vw, 96px);">
    <div class="container-x">
        <p class="eyebrow" style="color: var(--gold-500);">Gallery</p>
        <h1 style="font-size: clamp(38px, 5vw, 60px); margin: 18px 0 22px;">Moments at the club</h1>
        <p style="font-size: 19px; color: var(--mist-300); max-width: 640px;">
            A selection of photographs from club events and member gatherings. Some albums are visible to members only.
        </p>
    </div>
</section>

@if ($albums->isNotEmpty())
    <section style="background: var(--ivory-100); color: var(--ink-900); padding-block: clamp(48px, 6vw, 72px);">
        <div class="container-x">
            <h2 style="font-size: 30px; margin-bottom: 24px;">Albums</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px;">
                @foreach ($albums as $album)
                    @php $photo = $album->photos->first(); @endphp
                    @if ($photo || $album->photos->count() > 0)
                        <a href="{{ route('gallery.album', $album->slug) }}" class="card-light" style="padding: 0; overflow: hidden; display: block;">
                            <div style="height: 200px; background: #E9E1CB; overflow: hidden;">
                                @if ($photo)
                                    <img src="{{ route('gallery.thumb', $photo) }}" alt="{{ $photo->alt_text ?: $album->name }}" width="400" height="300" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div class="placeholder" style="height: 100%; border: 0;">[Album cover]</div>
                                @endif
                            </div>
                            <div style="padding: 20px;">
                                <h3 style="font-size: 22px; margin-bottom: 6px;">{{ $album->name }}</h3>
                                <p style="color: var(--slate-600); font-size: 14px;">{{ $album->photos->count() }} {{ Str::plural('photo', $album->photos->count()) }}</p>
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endif

<section style="padding-block: clamp(48px, 6vw, 72px);">
    <div class="container-x">
        <h2 style="font-size: 30px; margin-bottom: 24px;">Recent photographs</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px;">
            @forelse ($photos as $photo)
                <a href="{{ route('gallery.photo', $photo) }}" data-lightbox="{{ route('gallery.photo', $photo) }}" data-alt="{{ $photo->alt_text }}" style="display: block; border-radius: 4px; overflow: hidden; aspect-ratio: 4/3; background: var(--navy-800);">
                    <img src="{{ route('gallery.thumb', $photo) }}" alt="{{ $photo->alt_text ?: 'Gallery photograph' }}" width="400" height="300" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                </a>
            @empty
                <div class="card-dark"><p style="color: var(--mist-300);">Photographs from club events will appear here.</p></div>
            @endforelse
        </div>

        @if ($photos->hasPages())
            <div style="margin-top: 40px;">{{ $photos->links() }}</div>
        @endif
    </div>
</section>
@endsection