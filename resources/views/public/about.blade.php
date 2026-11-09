@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(56px, 7vw, 96px);">
    <div class="container-x">
        <p class="eyebrow" style="color: var(--gold-500);">About the club</p>
        <h1 style="font-size: clamp(38px, 5vw, 60px); margin: 18px 0 24px; max-width: 760px;" class="text-balance">
            A private address, <em style="color: var(--champagne-200);">quietly kept</em>.
        </h1>
        <p style="font-size: 19px; color: var(--mist-300); max-width: 640px;">
            Charlton Exclusive Club exists for people who value good food, good company and a room that is ready when they are.
            We are a members' club in South East London, run by a small team and guided by a committee drawn from the membership.
        </p>
    </div>
</section>

<section style="background: var(--ivory-100); color: var(--ink-900); padding-block: clamp(56px, 7vw, 88px);">
    <div class="container-x" style="display: flex; flex-wrap: wrap; gap: 64px;">
        <div style="flex: 1 1 420px;">
            <p class="eyebrow" style="color: var(--claret-700);">Our purpose</p>
            <h2 style="font-size: clamp(28px, 3.4vw, 40px); margin: 14px 0 20px;">To make belonging feel effortless</h2>
            <div class="prose-club">
                <p>The club was formed around a simple idea: that a members' club should feel like an extension of home, with better food, a better cellar and a diary worth keeping.</p>
                <p>Members join by application and are reviewed by the committee; the club takes care of the rest.</p>
                <p>Our rooms are open to members and their guests, and to approved hire. We publish our rates, our rules and our policies so that nothing is a surprise.</p>
            </div>
        </div>

        <div style="flex: 1 1 380px; display: grid; gap: 16px; align-content: start;">
            <div class="card-light">
                <h3 style="font-size: 22px; margin-bottom: 10px;">Reviewed by the committee</h3>
                <p style="color: var(--slate-600);">Every application is reviewed. It keeps the room right.</p>
            </div>
            <div class="card-light">
                <h3 style="font-size: 22px; margin-bottom: 10px;">Run for members</h3>
                <p style="color: var(--slate-600);">A committee of members reviews applications and advises the club.</p>
            </div>
            <div class="card-light">
                <h3 style="font-size: 22px; margin-bottom: 10px;">Accessible by design</h3>
                <p style="color: var(--slate-600);">Step-free access, accessible facilities and a published accessibility statement.</p>
            </div>
        </div>
    </div>
</section>

<section style="padding-block: clamp(56px, 7vw, 88px);">
    <div class="container-x">
        <p class="eyebrow" style="color: var(--gold-500);">The team</p>
        <h2 style="font-size: clamp(28px, 3.4vw, 40px); margin: 14px 0 16px;">Who you will meet</h2>
        <p style="color: var(--mist-300); max-width: 620px; margin-bottom: 40px;">Photographs and biographies are published only with consent.</p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px;">
            @forelse ($team as $member)
                <article class="card-dark">
                    @if ($member->photo_path)
                        <img src="{{ route('media.public', $member->photo_path) }}" alt="Portrait of {{ $member->name }}"
                             width="400" height="480" loading="lazy"
                             style="width: 100%; height: 300px; object-fit: cover; border-radius: 4px; margin-bottom: 20px;">
                    @else
                        <div class="placeholder" style="height: 220px; margin-bottom: 20px;">[Portrait: {{ $member->name }}]</div>
                    @endif
                    <h3 style="font-size: 24px;">{{ $member->name }}</h3>
                    @if ($member->role)<p class="eyebrow" style="font-size: 12px; color: var(--gold-500); margin-top: 8px;">{{ $member->role }}</p>@endif
                    @if ($member->bio)<p style="color: var(--mist-300); margin-top: 12px;">{{ $member->bio }}</p>@endif
                </article>
            @empty
                <div class="card-dark">
                    <p style="color: var(--mist-300);">Team biographies will be published here as consent is confirmed.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<section style="background: var(--gold-500); color: var(--navy-900); padding-block: 56px; text-align: center;">
    <div class="container-x">
        <h2 style="font-size: clamp(28px, 3.4vw, 40px);">Come and see the rooms</h2>
        <p style="margin: 12px auto 24px; max-width: 520px;">Arrange a visit with the club secretary, or begin your application online.</p>
        <a href="{{ route('contact') }}" class="btn btn-navy">Arrange a visit</a>
    </div>
</section>
@endsection