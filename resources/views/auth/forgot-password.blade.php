@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(56px, 7vw, 96px);">
    <div class="container-x" style="max-width: 480px;">
        <p class="eyebrow" style="color: var(--gold-500);">Reset password</p>
        <h1 style="font-size: clamp(36px, 4.6vw, 52px); margin: 18px 0 20px;">Forgot your password?</h1>
        <p style="font-size: 17px; color: var(--mist-300); margin-bottom: 32px;">
            Enter your email address and we'll send you a link to reset your password.
        </p>

        @if (session('status'))
            <div role="alert" style="border: 1px solid #C9A961; background: rgba(201,169,97,.12); color: var(--champagne-200); padding: 14px 16px; border-radius: 4px; margin-bottom: 20px;">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" style="border: 1px solid #F0A7B2; background: rgba(158,43,60,.16); color: #F0A7B2; padding: 14px 16px; border-radius: 4px; margin-bottom: 20px;">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="card-dark">
            <form method="POST" action="{{ route('password.email') }}" style="display: grid; gap: 18px;">
                @csrf
                <div>
                    <label class="field-label" for="email">Email address</label>
                    <input class="field-dark" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                </div>

                <button type="submit" class="btn btn-gold" style="width: 100%;">Send reset link</button>
            </form>
        </div>

        <div style="margin-top: 24px; text-align: center;">
            <a href="{{ route('login') }}" class="link-gold">← Back to sign in</a>
        </div>
    </div>
</section>
@endsection