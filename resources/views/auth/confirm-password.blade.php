@extends('layouts.panel', ['title' => 'Confirm Password'])

@section('content')
<div style="max-width: 480px; margin: 0 auto;">
    <header style="margin-bottom: 32px;">
        <h1 style="font-size: 32px; margin: 0 0 8px;">Confirm your password</h1>
        <p style="color: var(--slate-600);">Please confirm your password before continuing.</p>
    </header>

    @if ($errors->any())
        <div role="alert" style="background: #FEEBEE; color: #C62828; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #E53935;">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card-light">
        <form method="POST" action="{{ route('password.confirm') }}" style="display: grid; gap: 18px;">
            @csrf
            <div>
                <label class="field-label" for="password">Password</label>
                <input class="field-light" id="password" name="password" type="password" required autofocus autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-navy" style="width: 100%;">Confirm</button>
        </form>
    </div>
</div>
@endsection