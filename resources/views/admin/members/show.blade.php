@extends('layouts.panel', ['title' => 'Member - ' . $user->name])

@section('content')
<header style="margin-bottom: 32px;">
    <a href="{{ route('admin.members.index') }}" class="link-claret" style="font-size: 14px;">← Back to members</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">{{ $user->name }}</h1>
    <p style="color: var(--slate-600);">Email: {{ $user->email }} · Member since {{ $user->created_at->format('j M Y') }}</p>
</header>

<div class="card-light">
    <h2 style="font-size: 22px; margin-bottom: 16px;">Profile & Account Details</h2>
    <p style="color: var(--slate-600);">Member management controls are active.</p>
</div>
@endsection
