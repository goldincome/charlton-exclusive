@extends('layouts.panel', ['title' => 'Settings'])

@section('content')
<header style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin: 0 0 8px;">Club settings</h1>
    <p style="color: var(--slate-600);">Configure VAT rates, bank holidays, sections and general settings.</p>
</header>

<div style="display: grid; gap: 32px;">
    <div class="card-light">
        <h2 style="font-size: 22px; margin-bottom: 16px;">VAT rates</h2>
        <form method="POST" action="{{ route('admin.settings.vat') }}">
            @csrf
            <div style="display: grid; gap: 16px;">
                @foreach ($vatRates as $rate)
                    <div>
                        <label class="field-label" for="rate_{{ $rate->product_type }}">{{ ucfirst($rate->product_type) }} (%)</label>
                        <input class="field-light" id="rate_{{ $rate->product_type }}" name="rates[{{ $rate->product_type }}]" type="number" step="0.01" min="0" max="100" value="{{ $rate->rate }}" style="max-width: 160px;">
                    </div>
                @endforeach
            </div>
            <button type="submit" class="btn btn-navy btn-sm" style="margin-top: 16px;">Save VAT rates</button>
        </form>
    </div>

    <div class="card-light">
        <h2 style="font-size: 22px; margin-bottom: 16px;">Configuration keys</h2>
        <p style="color: var(--slate-600); margin-bottom: 16px;">Company number, ICO number, discount values, sponsor caps, etc.</p>
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            <div style="display: grid; gap: 14px;">
                @foreach ($config->take(10) as $item)
                    <div>
                        <label class="field-label" for="config_{{ $item->key }}">{{ ucfirst(str_replace('_', ' ', $item->key)) }}</label>
                        <input class="field-light" id="config_{{ $item->key }}" name="config[{{ $item->key }}]" value="{{ $item->value }}">
                    </div>
                @endforeach
            </div>
            <button type="submit" class="btn btn-navy btn-sm" style="margin-top: 16px;">Save configuration</button>
        </form>

        @if ($config->count() > 10)
            <p style="margin-top: 16px; font-size: 14px; color: var(--slate-600);">Showing first 10 keys. Total: {{ $config->count() }}</p>
        @endif
    </div>

    <div class="card-light">
        <h2 style="font-size: 22px; margin-bottom: 16px;">Sections</h2>
        <div style="display: grid; gap: 10px; margin-bottom: 20px;">
            @foreach ($sections as $section)
                <div style="padding: 12px; background: #fff; border: 1px solid #E0D5B8; border-radius: 4px;">
                    <strong>{{ $section->name }}</strong> · £{{ number_format($section->fee / 100, 2) }}{{ $section->recurring_annually ? ' / year' : '' }}
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-navy btn-sm" onclick="document.getElementById('section-form').style.display='block';">Add section</button>

        <form id="section-form" method="POST" action="{{ route('admin.settings.sections') }}" style="display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid #E0D5B8;">
            @csrf
            <div style="display: grid; gap: 14px;">
                <div>
                    <label class="field-label" for="section_name">Section name</label>
                    <input class="field-light" id="section_name" name="name" required>
                </div>
                <div>
                    <label class="field-label" for="section_description">Description</label>
                    <textarea class="field-light" id="section_description" name="description" rows="2"></textarea>
                </div>
                <div>
                    <label class="field-label" for="section_fee">Annual fee (pence)</label>
                    <input class="field-light" id="section_fee" name="fee" type="number" min="0" required>
                </div>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 14px;">
                <button type="submit" class="btn btn-navy btn-sm">Save section</button>
                <button type="button" class="btn btn-line-dark btn-sm" onclick="document.getElementById('section-form').style.display='none';">Cancel</button>
            </div>
        </form>
    </div>

    <div class="card-light">
        <h2 style="font-size: 22px; margin-bottom: 16px;">Bank holidays</h2>
        <div style="display: grid; gap: 8px; margin-bottom: 16px;">
            @foreach ($bankHolidays->take(8) as $holiday)
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #E0D5B8;">
                    <span>{{ $holiday->name }}</span>
                    <span style="color: var(--slate-600);">{{ $holiday->date->format('j M Y') }}</span>
                </div>
            @endforeach
        </div>
        <p style="font-size: 14px; color: var(--slate-600);">Add bank holidays to apply special rates. Showing {{ $bankHolidays->take(8)->count() }} of {{ $bankHolidays->count() }}.</p>
    </div>
</div>
@endsection