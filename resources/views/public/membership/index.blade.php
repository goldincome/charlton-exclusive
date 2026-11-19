@extends('layouts.public')

@section('content')
<section style="position: relative; overflow: hidden; padding-block: clamp(24px, 4vw, 48px);">
    <svg aria-hidden="true" viewBox="0 0 1440 600" preserveAspectRatio="none" style="position: absolute; inset: 0; width: 100%; height: 100%; opacity: .14;" fill="none" stroke="#C9A961" stroke-width="1.2">
        <path d="M-20 360 C 240 290, 460 460, 720 380 S 1180 280, 1460 380"></path>
        <path d="M-20 410 C 240 340, 460 510, 720 430 S 1180 330, 1460 430"></path>
        <path d="M-20 460 C 240 390, 460 560, 720 480 S 1180 380, 1460 480"></path>
    </svg>
    <div class="container-x" style="position: relative;">
        <p class="eyebrow" style="color: var(--gold-500);">Membership</p>
        <h1 style="font-size: clamp(38px, 5vw, 62px); margin: 18px 0 22px; max-width: 820px;" class="text-balance">
            Joined by <em style="color: var(--champagne-200);">introduction</em>, kept by belonging.
        </h1>
        <p style="font-size: 19px; color: var(--mist-300); max-width: 640px; margin-bottom: 32px;">
            Apply online or download the application form. Membership is paid monthly or yearly, with no entrance fee, and only yearly members receive discounts.
        </p>
        <div style="display: flex; flex-wrap: wrap; gap: 16px;">
            <a href="{{ route('account.applications.start') }}" class="btn btn-gold">Begin your application</a>
            <a href="{{ route('faq') }}" class="btn btn-line">Read the FAQ</a>
        </div>
    </div>
</section>

<section style="background: var(--ivory-100); color: var(--ink-900); padding-block: clamp(56px, 7vw, 88px);">
    <div class="container-x">
        <p class="eyebrow" style="color: var(--claret-700);">Categories</p>
        <h2 style="font-size: clamp(30px, 3.8vw, 44px); margin: 14px 0 16px;">Choose how you belong</h2>
        <p style="color: var(--slate-600); max-width: 620px; margin-bottom: 40px;">
            Plan fees are published below. Membership is not subject to VAT. There is no entrance fee — you pay only your chosen plan.
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
            @forelse ($categories as $category)
                <article class="card-light" style="display: flex; flex-direction: column;">
                    <h3 style="font-size: 28px;">{{ $category->name }}</h3>
                    <p style="margin: 10px 0 20px; color: var(--slate-600);">{{ $category->description }}</p>

                    <dl style="display: grid; gap: 10px; margin-bottom: 20px; font-size: 16px;">
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #E0D5B8; padding-bottom: 8px;">
                            <dt>Entrance fee</dt>
                            <dd style="margin: 0; font-weight: 600;">None</dd>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #E0D5B8; padding-bottom: 8px;">
                            <dt>Annual fee</dt>
                            <dd style="margin: 0; font-weight: 600;">£{{ number_format($category->annual_fee / 100, 2) }}</dd>
                        </div>
                        @if ($category->monthlyAvailable())
                            <div style="display: flex; justify-content: space-between;">
                                <dt>Or monthly</dt>
                                <dd style="margin: 0; font-weight: 600;">£{{ number_format($category->monthly_fee / 100, 2) }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($category->benefits)
                        <ul style="margin: 0 0 24px 1.1rem; padding: 0; color: var(--slate-600);">
                            @foreach ($category->benefits as $benefit)
                                <li style="margin-bottom: 6px;">{{ $benefit }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($category->sections->isNotEmpty())
                        <p style="font-size: 14px; color: var(--slate-600); margin-bottom: 16px;">
                            Includes {{ $category->sections->pluck('name')->join(', ') }}.
                        </p>
                    @endif

                    <a href="{{ route('account.applications.start') }}" class="btn btn-navy btn-sm" style="margin-top: auto; justify-self: start;">Apply for {{ $category->name }}</a>
                </article>
            @empty
                <div class="card-light">
                    <p>Membership categories are being confirmed. Please <a href="{{ route('contact') }}" class="link-claret">contact the club</a> for current fees.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<section style="padding-block: clamp(56px, 7vw, 88px);">
    <div class="container-x">
        <p class="eyebrow" style="color: var(--gold-500);">Sections</p>
        <h2 style="font-size: clamp(30px, 3.8vw, 44px); margin: 14px 0 16px;">Find your section</h2>
        <p style="color: var(--mist-300); max-width: 620px; margin-bottom: 40px;">Sections may be open to everyone or members only. Membership plan discounts are applied each time you use a members-only section — not as a yearly section fee.</p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
            @forelse ($sections as $section)
                <article class="card-dark">
                    <h3 style="font-size: 24px;">{{ $section->name }}</h3>
                    <p style="margin: 8px 0 16px; color: var(--mist-300);">{{ $section->description }}</p>
                    <p style="font-weight: 600; color: var(--champagne-200);">{{ $section->access_type === 'open' ? 'Open to members and non-members' : 'Members only · plan discount on use' }}</p>
                    <a href="{{ route('membership.section', $section->slug) }}" class="link-gold" style="display: inline-block; margin-top: 16px;">About this section</a>
                </article>
            @empty
                <div class="card-dark"><p style="color: var(--mist-300);">Sections are being confirmed for the coming year.</p></div>
            @endforelse
        </div>
    </div>
</section>

<section style="background: var(--navy-800); padding-block: 64px;">
    <div class="container-x" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 32px;">
        @foreach ([
            ['1', 'Register', 'Create your account and open the membership application.'],
            ['2', 'Apply online or at the office', 'Apply online with your CV and ID, or download the PDF and bring it to the office.'],
            ['3', 'Document verification', 'Bring an accepted original ID to the office. An administrator verifies your documents and chooses your plan.'],
            ['4', 'Choose billing and pay', 'You receive a payment link, choose monthly or yearly, and your membership activates immediately.'],
        ] as [$number, $title, $copy])
            <div>
                <p style="font-family: var(--font-display); font-size: 40px; color: var(--gold-500); line-height: 1;">{{ $number }}</p>
                <h3 style="font-size: 22px; margin: 12px 0 8px;">{{ $title }}</h3>
                <p style="color: var(--mist-300);">{{ $copy }}</p>
            </div>
        @endforeach
    </div>
</section>
@endsection