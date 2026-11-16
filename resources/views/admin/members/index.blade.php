@extends('layouts.panel', ['title' => 'Members Directory'])

@section('content')
<header style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin: 0 0 8px;">Members Directory</h1>
    <p style="color: var(--slate-600);">Manage club members, membership status, and privileges.</p>
</header>

<div style="display: grid; gap: 16px;">
    @forelse ($members as $member)
        <div class="card-light">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="font-size: 20px; margin: 0 0 4px;">
                        <a href="{{ route('admin.members.show', $member) }}" style="text-decoration: none; color: inherit;">
                            {{ $member->name }}
                        </a>
                    </h3>
                    <p style="color: var(--slate-600); font-size: 14px; margin: 0;">{{ $member->email }} · Member No: <strong style="color: var(--ink-900);">{{ $member->memberNumber() }}</strong> · Joined {{ $member->created_at->format('j M Y') }}</p>
                </div>
                <div>
                    <span class="badge badge-green">Member</span>
                </div>
            </div>
        </div>
    @empty
        <div class="card-light">
            <p style="margin: 0; color: var(--slate-600);">No members found.</p>
        </div>
    @endforelse
</div>

@if ($members->hasPages())
    <div style="margin-top: 32px;">{{ $members->links() }}</div>
@endif
@endsection
