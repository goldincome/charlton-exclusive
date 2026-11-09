@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(56px, 7vw, 96px);">
    <div class="container-x" style="max-width: 860px;">
        <p class="eyebrow" style="color: var(--gold-500);">Section</p>
        <h1 style="font-size: clamp(36px, 4.6vw, 56px); margin: 18px 0 20px;">{{ $section->name }}</h1>
        <p style="font-size: 18px; color: var(--mist-300); margin-bottom: 32px;">{{ $section->description }}</p>

        <div class="card-dark">
            <p style="font-family: var(--font-display); font-size: 28px; color: var(--champagne-200);">{{ $section->access_type === 'open' ? 'Open to members and non-members' : 'Members only' }}</p>
            <p style="color: var(--mist-300); margin-bottom: 8px;">@if($section->access_type === 'open') Anyone can order this service. @else Membership plan discounts are calculated and applied each time you use this section. @endif</p>
        </div>

        @if ($categories->isNotEmpty())
            <h2 style="font-size: 28px; margin: 48px 0 20px;">Available with these categories</h2>
            <div style="display: grid; gap: 14px;">
                @foreach ($categories as $category)
                    <div class="card-dark" style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: center;">
                        <div>
                            <h3 style="font-size: 22px;">{{ $category->name }}</h3>
                            <p style="color: var(--mist-300);">£{{ number_format($category->annual_fee / 100, 2) }} / year</p>
                        </div>
                        <a href="{{ route('account.applications.start') }}" class="link-gold">Apply</a>
                    </div>
                @endforeach
            </div>
        @endif

        <div style="margin-top: 48px; display: flex; flex-wrap: wrap; gap: 16px;">
            <a href="{{ route('membership.index') }}" class="btn btn-line">All membership options</a>
            <a href="{{ route('contact') }}" class="btn btn-line">Ask about this section</a>
        </div>
    </div>
</section>
@endsection