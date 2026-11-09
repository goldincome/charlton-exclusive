@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(56px, 7vw, 96px);">
    <div class="container-x">
        <p class="eyebrow" style="color: var(--gold-500);">The journal</p>
        <h1 style="font-size: clamp(38px, 5vw, 60px); margin: 18px 0 22px;">Essays, notices and <em style="color: var(--champagne-200);">club news</em></h1>
        <p style="font-size: 19px; color: var(--mist-300); max-width: 640px;">
            Long-form writing from committee, staff and members, published with permission.
        </p>
    </div>
</section>

<section style="background: var(--ivory-100); color: var(--ink-900); padding-block: clamp(56px, 7vw, 88px);">
    <div class="container-x">
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 32px;">
            @forelse ($posts as $post)
                <article class="card-light" style="display: flex; flex-direction: column;">
                    <p class="eyebrow" style="font-size: 12px; color: var(--claret-700);">
                        <time datetime="{{ $post->published_at->toIso8601String() }}">{{ $post->published_at->format('j F Y') }}</time>
                    </p>
                    <h2 style="font-size: 26px; margin: 10px 0 12px;">
                        <a href="{{ route('journal.show', $post->slug) }}" style="color: inherit; text-decoration: none;">{{ $post->title }}</a>
                    </h2>
                    @if ($post->excerpt)
                        <p style="color: var(--slate-600); margin-bottom: 16px;">{{ $post->excerpt }}</p>
                    @endif
                    @if ($post->author)
                        <p style="font-size: 14px; color: var(--slate-500); margin-bottom: 16px;">By {{ $post->author->name }}</p>
                    @endif
                    <a href="{{ route('journal.show', $post->slug) }}" class="link-claret" style="margin-top: auto;">Read the post</a>
                </article>
            @empty
                <div class="card-light" style="grid-column: 1 / -1;">
                    <p>The journal will publish essays, notices and club news. <a href="{{ route('contact') }}" class="link-claret">Suggest a topic.</a></p>
                </div>
            @endforelse
        </div>

        @if ($posts->hasPages())
            <div style="margin-top: 48px;">{{ $posts->links() }}</div>
        @endif
    </div>
</section>
@endsection