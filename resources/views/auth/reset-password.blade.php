@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(56px, 7vw, 96px);">
    <div class="container-x" style="max-width: 480px;">
        <p class="eyebrow" style="color: var(--gold-500);">Reset password</p>
        <h1 style="font-size: clamp(36px, 4.6vw, 52px); margin: 18px 0 20px;">Set a new password</h1>

        @if ($errors->any())
            <div role="alert" style="border: 1px solid #F0A7B2; background: rgba(158,43,60,.16); color: #F0A7B2; padding: 14px 16px; border-radius: 4px; margin-bottom: 20px;">
                Please check the highlighted fields and try again.
            </div>
        @endif

        <div class="card-dark">
            <form method="POST" action="{{ route('password.update') }}" style="display: grid; gap: 18px;">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <label class="field-label" for="email">Email address</label>
                    <input class="field-dark" id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="email">
                    @error('email')<p class="error-text">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="field-label" for="password">New password</label>
                    <input class="field-dark" id="password" name="password" type="password" required autocomplete="new-password">
                    @error('password')<p class="error-text">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="field-label" for="password_confirmation">Confirm password</label>
                    <input class="field-dark" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-gold" style="width: 100%;">Reset password</button>
            </form>
        </div>
    </div>
</section>
@endsection