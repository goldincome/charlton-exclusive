@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(56px, 7vw, 96px);">
    <div class="container-x" style="max-width: 540px;">
        <p class="eyebrow" style="color: var(--gold-500);">Join</p>
        <h1 style="font-size: clamp(36px, 4.6vw, 52px); margin: 18px 0 20px;">Create an account</h1>
        <p style="font-size: 17px; color: var(--mist-300); margin-bottom: 32px;">
            Create an account to request bookings and apply for membership.
        </p>

        @if ($errors->any())
            <div role="alert" style="border: 1px solid #F0A7B2; background: rgba(158,43,60,.16); color: #F0A7B2; padding: 14px 16px; border-radius: 4px; margin-bottom: 20px;">
                Please check the highlighted fields and try again.
            </div>
        @endif

        <div class="card-dark">
            <form method="POST" action="{{ route('register') }}" style="display: grid; gap: 18px;">
                @csrf
                <div>
                    <label class="field-label" for="name">Full name</label>
                    <input class="field-dark" id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
                    @error('name')<p class="error-text">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="field-label" for="email">Email address</label>
                    <input class="field-dark" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
                    @error('email')<p class="error-text">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="field-label" for="password">Password</label>
                    <input class="field-dark" id="password" name="password" type="password" required autocomplete="new-password">
                    @error('password')<p class="error-text">{{ $message }}</p>@enderror
                    <p style="font-size: 13px; color: var(--mist-300); margin-top: 6px;">At least 8 characters</p>
                </div>

                <div>
                    <label class="field-label" for="password_confirmation">Confirm password</label>
                    <input class="field-dark" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                </div>

                <label style="display: flex; gap: 12px; align-items: flex-start; font-size: 14px; color: var(--mist-300);">
                    <input type="checkbox" name="terms" value="1" required style="margin-top: 4px; width: 20px; height: 20px;">
                    <span>I agree to the <a href="{{ route('legal', 'terms') }}" class="link-gold" target="_blank">terms and conditions</a> and <a href="{{ route('legal', 'privacy') }}" class="link-gold" target="_blank">privacy policy</a>.</span>
                </label>

                <button type="submit" class="btn btn-gold" style="width: 100%;">Create account</button>
            </form>
        </div>

        <div style="margin-top: 24px; text-align: center;">
            <p style="color: var(--mist-300);">Already have an account? <a href="{{ route('login') }}" class="link-gold">Sign in</a></p>
        </div>
    </div>
</section>
@endsection