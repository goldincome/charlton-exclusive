@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(56px, 7vw, 96px);">
    <div class="container-x" style="max-width: 900px;">
        <p class="eyebrow" style="color: var(--gold-500);">Apply</p>
        <h1 style="font-size: clamp(36px, 4.6vw, 56px); margin: 18px 0 20px;">Request an invitation</h1>
        <p style="font-size: 18px; color: var(--mist-300); margin-bottom: 40px;">
            The application form lives inside your member account so you can follow your application's status. You can apply online now or download the form and bring it to the office.
        </p>

        <div class="card-dark" style="margin-bottom: 32px;">
            <h2 style="font-size: 26px; margin-bottom: 14px;">Before you begin</h2>
            <ul style="margin: 0 0 20px 1.1rem; padding: 0; color: var(--mist-300);">
                <li style="margin-bottom: 8px;">Apply online with your contact details, profession, CV and an accepted ID document, or download the PDF form.</li>
                <li style="margin-bottom: 8px;">Bring an accepted original ID to the office so your documents can be verified.</li>
                <li style="margin-bottom: 8px;">An administrator approves your application and chooses your plan, then emails you a payment link.</li>
                <li>Read the <a href="{{ route('legal', 'club-rules') }}" class="link-gold">club rules</a> and <a href="{{ route('legal', 'terms') }}" class="link-gold">terms</a>.</li>
            </ul>
            @auth
                <a href="{{ route('account.applications.start') }}" class="btn btn-gold">Continue to the application</a>
            @else
                <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                    <a href="{{ route('register') }}" class="btn btn-gold">Create an account</a>
                    <a href="{{ route('login') }}" class="btn btn-line">I already have an account</a>
                </div>
            @endauth
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
            @foreach ($categories as $category)
                <div class="card-dark">
                    <h3 style="font-size: 22px;">{{ $category->name }}</h3>
                    <p style="margin: 8px 0; color: var(--mist-300);">{{ $category->description }}</p>
                    <p style="color: var(--champagne-200); font-weight: 600;">£{{ number_format($category->annual_fee / 100, 2) }} / year</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection