@extends('layouts.panel', ['title' => 'Operating Hours'])

@section('content')
<header style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin: 0 0 8px;">Operating Hours</h1>
    <p style="color: var(--slate-600);">Configure site-wide operating hours for club facilities and meeting room bookings.</p>
</header>

@if ($errors->any())
    <div role="alert" style="background: #FEEBEE; color: #C62828; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #E53935;">
        <ul style="margin: 0 0 0 18px;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.operating-hours.update') }}" style="display: grid; gap: 24px;">
    @csrf
    @method('PUT')

    <div class="card-light" style="display: grid; gap: 16px;">
        @php
            $weekdays = [
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
                0 => 'Sunday',
            ];
        @endphp
        <div style="display: grid; gap: 12px;">
            @foreach($weekdays as $wNum => $wName)
                @php
                    $hr = $hours->firstWhere('weekday', $wNum);
                @endphp
                <div style="display: grid; grid-template-columns: 160px 1fr 1fr 120px; gap: 16px; align-items: center; background: #fff; padding: 16px; border: 1px solid #E0D5B8; border-radius: 4px;">
                    <strong>{{ $wName }}</strong>
                    <div>
                        <label style="font-size: 12px; display: block; color: var(--slate-600);">Opens At</label>
                        <input class="field-light" name="hours[{{ $wNum }}][opens_at]" type="time" value="{{ old('hours.'.$wNum.'.opens_at', $hr?->opens_at ? \Illuminate\Support\Carbon::parse($hr->opens_at)->format('H:i') : '08:00') }}">
                    </div>
                    <div>
                        <label style="font-size: 12px; display: block; color: var(--slate-600);">Closes At</label>
                        <input class="field-light" name="hours[{{ $wNum }}][closes_at]" type="time" value="{{ old('hours.'.$wNum.'.closes_at', $hr?->closes_at ? \Illuminate\Support\Carbon::parse($hr->closes_at)->format('H:i') : '23:00') }}">
                    </div>
                    <div>
                        <label style="display: flex; gap: 8px; align-items: center; cursor: pointer; margin-top: 18px;">
                            <input type="checkbox" name="hours[{{ $wNum }}][is_open]" value="1" @checked(old('hours.'.$wNum.'.is_open', $hr?->is_open ?? true)) style="width: 18px; height: 18px;">
                            <span>Open</span>
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div>
        <button type="submit" class="btn btn-navy">Save Operating Hours</button>
    </div>
</form>
@endsection
