@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(48px, 6vw, 80px);">
    <div class="container-x">
        <p class="eyebrow" style="color: var(--gold-500);">Legal</p>
        <h1 style="font-size: clamp(34px, 4.4vw, 52px); margin: 16px 0 16px;">{{ $document?->title ?? $fallbackTitle }}</h1>
        @if ($document)
            <p style="color: var(--mist-300); font-size: 15px;">Version {{ $document->version }} · Effective {{ $document->effective_at->format('j F Y') }}</p>
        @endif
    </div>
</section>

<section style="background: var(--ivory-100); color: var(--ink-900); padding-block: clamp(48px, 6vw, 80px);">
    <div class="container-x">
        <div class="prose-club">
            @if ($document)
                {!! $document->body !!}
            @else
                <p>We are preparing this document for publication. For a copy of our current policy, please write to <a href="mailto:info@charltonexclusiveclub.co.uk">info@charltonexclusiveclub.co.uk</a>.</p>
                <p>Our published policies include club rules, terms and conditions, privacy, cookies and accessibility.</p>
            @endif
        </div>

        <div style="margin-top: 48px; padding-top: 24px; border-top: 1px solid #D8CBAA;">
            <p style="font-size: 14px; color: var(--slate-600);">
                Charlton Exclusive Club · Unit 6, Block 3 Woolwich, Dockyard Industrial Estate, Woolwich Church St, Charlton, London SE18 5PQ ·
                Company no. {{ \App\Models\AppConfig::get('company_number', '[COMPANY NUMBER]') }} ·
                VAT no. {{ \App\Models\AppConfig::get('vat_number', '[VAT NUMBER]') }} ·
                ICO no. {{ \App\Models\AppConfig::get('ico_number', '[ICO NUMBER]') }}
            </p>
        </div>
    </div>
</section>
@endsection