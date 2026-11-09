@extends('layouts.panel', ['title' => 'Sections'])

@section('content')
<header style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
    <div>
        <h1 style="font-size: 32px; margin: 0 0 8px;">Sections</h1>
        <p style="color: var(--slate-600);">Manage section access, pause status, and plan discounts applied when a member uses a section.</p>
    </div>
    <a href="{{ route('admin.sections.create') }}" class="btn btn-navy btn-sm">Add section</a>
</header>

<div class="card-light" style="padding: 0; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f6f1e4; text-align: left;">
                <th style="padding: 14px 16px;">Name</th>
                <th style="padding: 14px 16px;">Access</th>
                <th style="padding: 14px 16px;">Type</th>
                <th style="padding: 14px 16px;">Status</th>
                <th style="padding: 14px 16px;">Plan discounts</th>
                <th style="padding: 14px 16px;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sections as $section)
                <tr style="border-top: 1px solid #E0D5B8;">
                    <td style="padding: 14px 16px;">
                        <strong>{{ $section->name }}</strong><br>
                        <span style="color: var(--slate-600); font-size: 13px;">{{ $section->slug }}</span>
                    </td>
                    <td style="padding: 14px 16px;">{{ $section->access_type === 'open' ? 'Open to all' : 'Members only' }}</td>
                    <td style="padding: 14px 16px;">{{ ucfirst($section->type ?? 'slot') }}</td>
                    <td style="padding: 14px 16px;">{{ $section->active ? 'Active' : 'Paused' }}</td>
                    <td style="padding: 14px 16px;">{{ $section->discounts_count }}</td>
                    <td style="padding: 14px 16px; white-space: nowrap;">
                        <a href="{{ route('admin.sections.edit', $section) }}" class="link-claret">Edit</a>
                        <form method="POST" action="{{ route('admin.sections.pause', $section) }}" style="display:inline; margin-left:10px;">
                            @csrf
                            <button type="submit" class="link-claret" style="background:none;border:none;padding:0;cursor:pointer;">{{ $section->active ? 'Pause' : 'Resume' }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="padding: 18px 16px; color: var(--slate-600);">No sections yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
