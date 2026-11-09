@extends('layouts.panel', ['title' => 'Bookable items'])

@section('content')
<header style="margin-bottom: 32px; display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:center;">
    <div>
        <h1 style="font-size: 32px; margin: 0 0 8px;">Bookable items</h1>
        <p style="color: var(--slate-600);">Section services/items for the clean booking/cart design. Mapped legacy resources can coexist with `source_type/source_id`.</p>
    </div>
    <a href="{{ route('admin.bookable-items.create') }}" class="btn btn-navy btn-sm">Add item</a>
</header>

<div class="card-light" style="padding:0; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead><tr style="background:#f6f1e4; text-align:left;"><th style="padding:14px 16px;">Name</th><th style="padding:14px 16px;">Section</th><th style="padding:14px 16px;">Price</th><th style="padding:14px 16px;">Status</th><th style="padding:14px 16px;"></th></tr></thead>
        <tbody>
            @forelse ($items as $item)
                <tr style="border-top:1px solid #E0D5B8;">
                    <td style="padding:14px 16px;"><strong>{{ $item->name }}</strong>@if($item->source_type)<br><span style="font-size:12px; color:var(--slate-600);">{{ class_basename($item->source_type) }} #{{ $item->source_id }}</span>@endif</td>
                    <td style="padding:14px 16px;">{{ $item->section?->name }} ({{ $item->section?->access_type === 'open' ? 'open' : 'members' }})</td>
                    <td style="padding:14px 16px;">£{{ number_format($item->price/100,2) }} / {{ $item->pricing_unit }}</td>
                    <td style="padding:14px 16px;">{{ $item->is_active ? 'Active' : 'Inactive' }}</td>
                    <td style="padding:14px 16px;"><a href="{{ route('admin.bookable-items.edit', $item) }}" class="link-claret">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="5" style="padding:18px 16px; color:var(--slate-600);">No bookable items. Run <code>php artisan sections:sync-bookable-items</code> to map legacy resources.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
