@extends('layouts.panel', ['title' => 'Apply for membership'])

@section('content')
<header style="margin-bottom: 32px;">
    <a href="{{ route('account.applications.index') }}" class="link-claret" style="font-size: 14px;">← Back to applications</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">Apply for membership</h1>
    <p style="color: var(--slate-600);">Choose your category, sections and sponsors.</p>
</header>

@if ($errors->any())
    <div role="alert" style="background: #FEEBEE; color: #C62828; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #E53935;">
        Please check the highlighted fields and try again.
    </div>
@endif

<form method="POST" action="{{ route('account.applications.store') }}" style="display: grid; gap: 32px;">
    @csrf

    <div class="card-light">
        <h2 style="font-size: 22px; margin-bottom: 20px;">Choose a membership category</h2>
        <div style="display: grid; gap: 14px;">
            @foreach ($categories as $category)
                <label style="display: block; padding: 18px; border: 2px solid #E0D5B8; border-radius: 4px; cursor: pointer;">
                    <div style="display: flex; gap: 14px;">
                        <input type="radio" name="membership_category_id" value="{{ $category->id }}" required @checked(old('membership_category_id') == $category->id) style="margin-top: 4px; width: 20px; height: 20px;">
                        <div style="flex: 1;">
                            <strong style="display: block; font-size: 20px; margin-bottom: 6px;">{{ $category->name }}</strong>
                            <p style="color: var(--slate-600); margin-bottom: 12px;">{{ $category->description }}</p>
                            <p style="font-weight: 600; color: var(--claret-700);">
                                Entrance: £{{ number_format($category->entrance_fee / 100, 2) }} · 
                                Annual: £{{ number_format($category->annual_fee / 100, 2) }}
                            </p>
                            <p style="font-size: 13px; color: var(--slate-600); margin-top: 8px;">Minimum {{ $category->min_sections }} sections required</p>
                        </div>
                    </div>
                </label>
            @endforeach
        </div>
        @error('membership_category_id')<p class="error-text">{{ $message }}</p>@enderror
    </div>

    <div class="card-light">
        <h2 style="font-size: 22px; margin-bottom: 20px;">Choose your sections</h2>
        <p style="color: var(--slate-600); margin-bottom: 16px;">Select at least two sections that match your interests.</p>
        <div style="display: grid; gap: 12px;">
            @foreach ($categories->flatMap->sections->unique('id') as $section)
                <label style="display: flex; gap: 14px; align-items: flex-start; padding: 14px; border: 1px solid #E0D5B8; border-radius: 4px; cursor: pointer;">
                    <input type="checkbox" name="sections[]" value="{{ $section->id }}" @checked(in_array($section->id, old('sections', []))) style="margin-top: 4px; width: 20px; height: 20px;">
                    <div style="flex: 1;">
                        <strong style="display: block; margin-bottom: 4px;">{{ $section->name }}</strong>
                        <p style="font-size: 14px; color: var(--slate-600); margin: 0 0 6px;">{{ $section->description }}</p>
                        <p style="font-size: 14px; font-weight: 600; color: var(--claret-700);">£{{ number_format($section->fee / 100, 2) }}{{ $section->recurring_annually ? ' / year' : '' }}</p>
                    </div>
                </label>
            @endforeach
        </div>
        @error('sections')<p class="error-text">{{ $message }}</p>@enderror
    </div>

    <div class="card-light">
        <h2 style="font-size: 22px; margin-bottom: 20px;">Nominate two sponsors</h2>
        <p style="color: var(--slate-600); margin-bottom: 16px;">Each sponsor must be a member of at least three years standing and in good financial standing.</p>
        <div style="display: grid; gap: 18px;">
            <div>
                <label class="field-label" for="sponsor_1">First sponsor email</label>
                <input class="field-light" id="sponsor_1" name="sponsors[0]" type="email" value="{{ old('sponsors.0') }}" required>
                @error('sponsors.0')<p class="error-text">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="field-label" for="sponsor_2">Second sponsor email</label>
                <input class="field-light" id="sponsor_2" name="sponsors[1]" type="email" value="{{ old('sponsors.1') }}" required>
                @error('sponsors.1')<p class="error-text">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="card-light">
        <label style="display: flex; gap: 12px; align-items: flex-start; font-size: 15px;">
            <input type="checkbox" name="terms" value="1" required @checked(old('terms')) style="margin-top: 4px; width: 20px; height: 20px;">
            <span>I have read and accept the <a href="{{ route('legal', 'club-rules') }}" class="link-claret" target="_blank">club rules</a> and <a href="{{ route('legal', 'terms') }}" class="link-claret" target="_blank">terms and conditions</a>.</span>
        </label>
        @error('terms')<p class="error-text">{{ $message }}</p>@enderror
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
        <button type="submit" class="btn btn-navy">Submit application</button>
        <a href="{{ route('account.applications.index') }}" class="btn btn-line-dark">Cancel</a>
    </div>
</form>
@endsection