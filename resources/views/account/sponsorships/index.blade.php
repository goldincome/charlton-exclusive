@extends('layouts.panel', ['title' => 'Sponsorship requests'])

@section('content')
<header style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin: 0 0 8px;">Sponsorship requests</h1>
    <p style="color: var(--slate-600);">Members have nominated you to support their application.</p>
</header>

<div style="display: grid; gap: 20px;">
    @forelse ($sponsorships as $sponsorship)
        <div class="card-light">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: flex-start; margin-bottom: 16px;">
                <div>
                    <h3 style="font-size: 24px; margin: 0 0 6px;">{{ $sponsorship->application->user->name }}</h3>
                    <p style="color: var(--slate-600);">Applying for {{ $sponsorship->application->category->name }}</p>
                </div>
                <span class="badge badge-{{ $sponsorship->status === 'accepted' ? 'green' : ($sponsorship->status === 'declined' ? 'red' : 'gold') }}">
                    {{ ucfirst($sponsorship->status) }}
                </span>
            </div>

            @if ($sponsorship->status === 'pending')
                <div style="padding: 14px; background: rgba(201,169,97,.08); border: 1px solid rgba(201,169,97,.3); border-radius: 4px; margin-bottom: 16px;">
                    <p style="margin: 0; font-size: 14px;">
                        <strong>Action required:</strong> Please confirm that you can support this application, or provide a reason if you must decline.
                    </p>
                </div>

                <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                    <form method="POST" action="{{ route('account.sponsorships.accept', $sponsorship) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-navy btn-sm">Accept sponsorship</button>
                    </form>

                    <button type="button" class="btn btn-line-dark btn-sm" onclick="document.getElementById('decline-form-{{ $sponsorship->id }}').style.display='block';">
                        Decline
                    </button>
                </div>

                <form id="decline-form-{{ $sponsorship->id }}" method="POST" action="{{ route('account.sponsorships.decline', $sponsorship) }}" style="display: none; margin-top: 16px; padding-top: 16px; border-top: 1px solid #E0D5B8;">
                    @csrf
                    <label class="field-label" for="reason-{{ $sponsorship->id }}">Reason for declining</label>
                    <textarea class="field-light" id="reason-{{ $sponsorship->id }}" name="reason" rows="3" required></textarea>
                    <div style="display: flex; gap: 10px; margin-top: 12px;">
                        <button type="submit" class="btn btn-line-dark btn-sm">Submit decline</button>
                        <button type="button" class="btn btn-line-dark btn-sm" onclick="document.getElementById('decline-form-{{ $sponsorship->id }}').style.display='none';">Cancel</button>
                    </div>
                </form>
            @elseif ($sponsorship->status === 'accepted')
                <p style="color: var(--slate-600); font-size: 14px;">You accepted this sponsorship {{ $sponsorship->responded_at?->format('j F Y') }}.</p>

                <form method="POST" action="{{ route('account.sponsorships.withdraw', $sponsorship) }}" style="margin-top: 16px;" onsubmit="return confirm('Are you sure you want to withdraw your support?');">
                    @csrf
                    <label class="field-label" for="withdraw-reason-{{ $sponsorship->id }}">Reason for withdrawal</label>
                    <textarea class="field-light" id="withdraw-reason-{{ $sponsorship->id }}" name="reason" rows="3" required></textarea>
                    <button type="submit" class="btn btn-line-dark btn-sm" style="margin-top: 10px;">Withdraw support</button>
                </form>
            @elseif ($sponsorship->status === 'declined')
                <p style="color: var(--slate-600); font-size: 14px;">You declined this request {{ $sponsorship->responded_at?->format('j F Y') }}.</p>
                @if ($sponsorship->reason)
                    <p style="font-size: 14px; margin-top: 8px;"><strong>Reason:</strong> {{ $sponsorship->reason }}</p>
                @endif
            @endif
        </div>
    @empty
        <div class="card-light">
            <p>You have no sponsorship requests at this time.</p>
        </div>
    @endforelse
</div>
@endsection