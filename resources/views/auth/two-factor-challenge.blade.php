@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(56px, 7vw, 96px);">
    <div class="container-x" style="max-width: 480px;">
        <p class="eyebrow" style="color: var(--gold-500);">Two-factor authentication</p>
        <h1 style="font-size: clamp(36px, 4.6vw, 52px); margin: 18px 0 20px;">Enter your code</h1>
        <p style="font-size: 17px; color: var(--mist-300); margin-bottom: 32px;">
            Enter the six-digit code from your authenticator app.
        </p>

        @if ($errors->any())
            <div role="alert" style="border: 1px solid #F0A7B2; background: rgba(158,43,60,.16); color: #F0A7B2; padding: 14px 16px; border-radius: 4px; margin-bottom: 20px;">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="card-dark">
            <form method="POST" action="{{ route('two-factor.login') }}" style="display: grid; gap: 18px;">
                @csrf
                <div>
                    <label class="field-label" for="code">Authentication code</label>
                    <input class="field-dark" id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" autofocus style="font-size: 24px; letter-spacing: 0.2em; text-align: center;">
                </div>

                <button type="submit" class="btn btn-gold" style="width: 100%;">Verify</button>
            </form>

            <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid rgba(201,169,97,.25);">
                <p style="color: var(--mist-300); margin-bottom: 12px; font-size: 14px;">Or use a recovery code:</p>
                <form method="POST" action="{{ route('two-factor.login') }}" style="display: grid; gap: 14px;">
                    @csrf
                    <input class="field-dark" name="recovery_code" type="text" autocomplete="one-time-code" placeholder="XXXXX-XXXXX">
                    <button type="submit" class="btn btn-line" style="width: 100%;">Use recovery code</button>
                </form>
            </div>
        </div>

        <div style="margin-top: 24px; text-align: center;">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="background: none; border: none; color: var(--gold-500); font-size: 14px; cursor: pointer; text-decoration: underline;">Sign out</button>
            </form>
        </div>
    </div>
</section>
@endsection