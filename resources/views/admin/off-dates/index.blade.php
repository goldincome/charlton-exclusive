@extends('layouts.panel', ['title' => 'Off-dates & Bank Holidays'])

@section('content')
<header style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 32px; margin: 0 0 8px;">Off-dates & Bank Holidays</h1>
        <p style="color: var(--slate-600); margin: 0;">Manage public holidays and special off-dates when standard operating hours or peak pricing apply.</p>
    </div>
</header>

@if ($errors->any())
    <div role="alert" style="background: #FEEBEE; color: #C62828; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #E53935;">
        <ul style="margin: 0 0 0 18px;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 32px; align-items: start;">
    <!-- Add form -->
    <div class="card-light" style="padding: 24px; display: grid; gap: 16px;">
        <h2 style="font-size: 20px; margin: 0;">Add Off-Date / Bank Holiday</h2>
        <form method="POST" action="{{ route('admin.off-dates.store') }}" style="display: grid; gap: 16px;">
            @csrf
            <div>
                <label class="field-label" for="date">Date</label>
                <input class="field-light" id="date" name="date" type="date" required value="{{ old('date') }}">
            </div>
            <div>
                <label class="field-label" for="name">Holiday / Title</label>
                <input class="field-light" id="name" name="name" type="text" required value="{{ old('name') }}" placeholder="e.g. Christmas Day">
            </div>
            <button type="submit" class="btn btn-navy">Add Off-Date</button>
        </form>
    </div>

    <!-- List table -->
    <div class="card-light" style="padding: 24px; display: grid; gap: 16px;">
        <h2 style="font-size: 20px; margin: 0;">Configured Off-Dates</h2>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--slate-200); text-align: left;">
                        <th style="padding: 10px;">Date</th>
                        <th style="padding: 10px;">Title</th>
                        <th style="padding: 10px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bankHolidays as $holiday)
                        <tr style="border-bottom: 1px solid var(--slate-200);">
                            <td style="padding: 12px 10px; font-weight: 500;">{{ \Illuminate\Support\Carbon::parse($holiday->date)->format('D, M j, Y') }}</td>
                            <td style="padding: 12px 10px;">{{ $holiday->name }}</td>
                            <td style="padding: 12px 10px; text-align: right;">
                                <form action="{{ route('admin.off-dates.destroy', $holiday) }}" method="POST" onsubmit="return confirm('Delete this off-date?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm" style="background: #FEEBEE; color: #C62828; border: 1px solid #E53935; padding: 4px 8px; font-size: 12px;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="padding: 24px; text-align: center; color: var(--slate-600);">No off-dates configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>
            {{ $bankHolidays->links() }}
        </div>
    </div>
</div>
@endsection
