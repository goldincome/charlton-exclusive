<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Page not found · Charlton Exclusive Club</title>
    @vite(['resources/css/app.css'])
</head>
<body style="background: var(--navy-900); color: var(--ivory-100); font-family: var(--font-body); margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 32px;">
    <div style="text-align: center; max-width: 540px;">
        <p style="font-family: var(--font-display); font-size: 80px; color: var(--gold-500); line-height: 1; margin: 0 0 20px;">404</p>
        <h1 style="font-size: clamp(28px, 4vw, 42px); margin: 0 0 16px;">Page not found</h1>
        <p style="font-size: 17px; color: var(--mist-300); margin: 0 0 32px;">
            The page you are looking for has been moved, removed or never existed.
        </p>
        <div style="display: flex; flex-wrap: wrap; gap: 14px; justify-content: center;">
            <a href="{{ route('home') }}" class="btn btn-gold">Return home</a>
            <a href="{{ route('contact') }}" class="btn btn-line">Contact the club</a>
        </div>
    </div>
</body>
</html>