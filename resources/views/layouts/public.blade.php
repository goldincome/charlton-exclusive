@php
    use App\Models\AppConfig;

    $clubName = 'Charlton Exclusive Club';
    $clubAddress = 'Unit 6, Block 3 Woolwich, Dockyard Industrial Estate, Woolwich Church St, Charlton, London SE18 5PQ';
    $clubPhone = '+44 (0) 2032 474747';
    $clubEmail = 'info@charltonexclusiveclub.co.uk';

    $pageTitle = $title ?? $clubName.' | Private Members\' Club, SE London';
    $pageDescription = $description ?? 'A private members\' club in Charlton, South East London: dining, sports sections, events and meeting rooms. Apply for membership today.';
    $pageCanonical = $canonical ?? url()->current();
    $pageImage = $ogImage ?? asset('images/og-default.jpg');
    $robots = $robots ?? 'index, follow';

    $organizationSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $clubName,
        'url' => url('/'),
        'logo' => asset('images/logo.svg'),
        'telephone' => $clubPhone,
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Unit 6, Block 3 Woolwich, Dockyard Industrial Estate, Woolwich Church St',
            'addressLocality' => 'Charlton, London',
            'postalCode' => 'SE18 5PQ',
            'addressCountry' => 'GB',
        ],
        'sameAs' => array_values(array_filter([
            AppConfig::get('social_facebook'),
            AppConfig::get('social_instagram'),
            AppConfig::get('social_linkedin'),
        ])),
    ];

    $websiteSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $clubName,
        'url' => url('/'),
    ];
@endphp
<!doctype html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $pageCanonical }}">

    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:site_name" content="{{ $clubName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $pageCanonical }}">
    <meta property="og:image" content="{{ $pageImage }}">
    <meta property="og:locale" content="en_GB">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $pageImage }}">

    <meta name="theme-color" content="#0B1426">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">

    <script type="application/ld+json">{!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($websiteSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    @isset($schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    @endisset

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>

<div style="background: var(--navy-900); min-height: 100vh; display: flex; flex-direction: column;">
    @include('partials.header')

    <main id="main" style="flex: 1;">
        @if (session('status'))
            <div role="status" style="background: rgba(201,169,97,.14); border-bottom: 1px solid rgba(201,169,97,.35);">
                <div class="container-x" style="padding-block: 14px; color: var(--champagne-200); font-size: 15px;">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials.footer')
</div>

@include('partials.cookie-banner')
</body>
</html>
