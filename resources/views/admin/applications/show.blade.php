@extends('layouts.panel', ['title' => 'Application - ' . $application->user->name])

@section('content')
<header style="margin-bottom: 32px;">
    <a href="{{ route('admin.applications.index') }}" class="link-claret" style="font-size: 14px;">← Back to applications</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">{{ $application->user->name }}</h1>
    <p style="color: var(--slate-600);">Category: {{ $application->category->name }} · Status: {{ ucfirst(str_replace('_', ' ', $application->status)) }}</p>
</header>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px; margin-bottom: 32px;">
    <div>
        <div class="card-light" style="margin-bottom: 24px;">
            <h2 style="font-size: 22px; margin-bottom: 16px;">Candidate Details</h2>
            <dl style="display: grid; gap: 12px;">
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">Full Name</dt>
                    <dd style="margin: 0; font-weight: 600;">{{ $application->user->name }}</dd>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">Email</dt>
                    <dd style="margin: 0;">{{ $application->user->email }}</dd>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">Profession / Bio</dt>
                    <dd style="margin: 0;">{{ $application->profession ?? 'N/A' }}</dd>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <dt style="color: var(--slate-600);">Submitted At</dt>
                    <dd style="margin: 0;">{{ $application->submitted_at?->format('j M Y, H:i') ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>

        @if ($application->sponsorships->isNotEmpty())
            <div class="card-light">
                <h2 style="font-size: 22px; margin-bottom: 16px;">Sponsorships</h2>
                <div style="display: grid; gap: 12px;">
                    @foreach ($application->sponsorships as $sponsorship)
                        <div class="card-light" style="background: #fff; border: 1px solid #E0D5B8;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <p style="font-weight: 600; margin-bottom: 4px;">{{ $sponsorship->sponsor->name }}</p>
                                    <p style="font-size: 14px; color: var(--slate-600); margin: 0;">{{ $sponsorship->sponsor->email }}</p>
                                </div>
                                <span class="badge badge-{{ $sponsorship->status === 'accepted' ? 'green' : 'gold' }}">
                                    {{ ucfirst($sponsorship->status) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <aside>
        <div class="card-light" style="background: var(--navy-900); color: var(--ivory-100); margin-bottom: 20px;">
            <h2 style="font-size: 22px; margin-bottom: 16px;">Decision & Actions</h2>

            <form method="POST" action="{{ route('admin.applications.decide', $application) }}" style="margin-bottom: 16px;">
                @csrf
                <label class="field-label" for="decision" style="color: var(--ivory-100);">Decision</label>
                <select name="decision" id="decision" class="field-dark" style="margin-bottom: 12px;" required>
                    <option value="">Select decision...</option>
                    <option value="approve">Approve</option>
                    <option value="reject">Reject</option>
                </select>

                <label class="field-label" for="notes" style="color: var(--ivory-100);">Notes / Reason</label>
                <textarea name="notes" id="notes" rows="3" class="field-dark" placeholder="Optional notes..." style="margin-bottom: 12px;"></textarea>

                <button type="submit" class="btn btn-gold" style="width: 100%;">Submit decision</button>
            </form>
        </div>
    </aside>
</div>
@endsection
