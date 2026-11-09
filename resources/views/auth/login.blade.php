@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(56px, 7vw, 96px);">
    <div class="container-x" style="max-width: 480px;">
        <p class="eyebrow" style="color: var(--gold-500);">Members</p>
        <h1 style="font-size: clamp(36px, 4.6vw, 52px); margin: 18px 0 20px;">Sign in</h1>
        <p style="font-size: 17px; color: var(--mist-300); margin-bottom: 32px;">
            Access your account to manage bookings, applications and membership.
        </p>

        @if ($errors->any())
            <div role="alert" style="border: 1px solid #F0A7B2; background: rgba(158,43,60,.16); color: #F0A7B2; padding: 14px 16px; border-radius: 4px; margin-bottom: 20px;">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('status'))
            <div role="alert" style="border: 1px solid #C9A961; background: rgba(201,169,97,.12); color: var(--champagne-200); padding: 14px 16px; border-radius: 4px; margin-bottom: 20px;">
                {{ session('status') }}
            </div>
        @endif

        <div class="card-dark">
            <form method="POST" action="{{ route('login') }}" style="display: grid; gap: 18px;">
                @csrf
                <div>
                    <label class="field-label" for="email">Email address</label>
                    <input class="field-dark" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                </div>

                <div>
                    <label class="field-label" for="password">Password</label>
                    <input class="field-dark" id="password" name="password" type="password" required autocomplete="current-password">
                </div>

                <label style="display: flex; gap: 12px; align-items: center; font-size: 14px; color: var(--mist-300);">
                    <input type="checkbox" name="remember" value="1" style="width: 20px; height: 20px;">
                    <span>Remember me</span>
                </label>

                <button type="submit" class="btn btn-gold" style="width: 100%;">Sign in</button>
            </form>

            <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid rgba(201,169,97,.25); text-align: center;">
                <a href="{{ route('password.request') }}" class="link-gold">Forgot your password?</a>
            </div>
        </div>

        <div style="margin-top: 24px; text-align: center;">
            <p style="color: var(--mist-300);">Don't have an account? <a href="{{ route('register') }}" class="link-gold">Create one</a></p>
        </div>
    </div>
</section>
@endsection