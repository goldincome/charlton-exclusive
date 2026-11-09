@extends('layouts.panel', ['title' => 'Apply online'])

@section('content')
<header style="margin-bottom: 32px;">
    <a href="{{ route('account.applications.start') }}" class="link-claret" style="font-size: 14px;">← Back</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">Membership application</h1>
    <p style="color: var(--slate-600); max-width: 760px;">
        Complete your details and upload the requested documents. We will review your application and email you when it has been approved.
        You do not choose a plan or pay at this stage.
    </p>
</header>

@if ($errors->any())
    <div role="alert" style="background: #FEEBEE; color: #C62828; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #E53935;">
        <p style="margin: 0 0 6px; font-weight: 600;">Please check the highlighted fields and try again.</p>
        @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

<form method="POST" action="{{ route('account.applications.store') }}" enctype="multipart/form-data" style="display: grid; gap: 28px;">
    @csrf

    <section class="card-light">
        <h2 style="font-size: 22px; margin: 0 0 18px;">Contact details</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px;">
            <div>
                <label class="field-label" for="phone">Phone number</label>
                <input class="field-light" id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" required>
            </div>
            <div>
                <label class="field-label" for="city">Town / city</label>
                <input class="field-light" id="city" name="city" type="text" value="{{ old('city', $user->city) }}" required>
            </div>
            <div style="grid-column: 1 / -1;">
                <label class="field-label" for="address_line_1">Address line 1</label>
                <input class="field-light" id="address_line_1" name="address_line_1" type="text" value="{{ old('address_line_1', $user->address_line_1) }}" required>
            </div>
            <div style="grid-column: 1 / -1;">
                <label class="field-label" for="address_line_2">Address line 2 <span style="font-weight: 400; color: var(--slate-600);">(optional)</span></label>
                <input class="field-light" id="address_line_2" name="address_line_2" type="text" value="{{ old('address_line_2', $user->address_line_2) }}">
            </div>
            <div>
                <label class="field-label" for="postcode">Postcode</label>
                <input class="field-light" id="postcode" name="postcode" type="text" value="{{ old('postcode', $user->postcode) }}" required>
            </div>
        </div>
    </section>

    <section class="card-light">
        <h2 style="font-size: 22px; margin: 0 0 18px;">Professional details</h2>
        <label class="field-label" for="profession">Profession</label>
        <input class="field-light" id="profession" name="profession" type="text" value="{{ old('profession') }}" placeholder="e.g. Architect, Solicitor, Company director" required>

        <div style="margin-top: 18px;">
            <label class="field-label" for="cv">Curriculum vitae (CV)</label>
            <input class="field-light" id="cv" name="cv" type="file" accept=".pdf,.doc,.docx" required>
            <p style="font-size: 13px; color: var(--slate-600); margin: 6px 0 0;">PDF, DOC or DOCX. Maximum 10MB.</p>
        </div>
    </section>

    <section class="card-light">
        <h2 style="font-size: 22px; margin: 0 0 18px;">Identification</h2>

        <label class="field-label" for="id_type">Type of ID</label>
        <select class="field-light" id="id_type" name="id_type" required>
            <option value="">Select the ID you will bring to the office…</option>
            @foreach ($idTypes as $value => $label)
                <option value="{{ $value }}" @selected(old('id_type') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <div style="margin-top: 18px;">
            <label class="field-label" for="id_document">Upload your ID document</label>
            <input class="field-light" id="id_document" name="id_document" type="file" accept=".pdf,.jpg,.jpeg,.png" required>
            <p style="font-size: 13px; color: var(--slate-600); margin: 6px 0 0;">PDF, JPG or PNG. Maximum 10MB. You must still bring the original ID to the office.</p>
        </div>

        <div style="margin-top: 18px; padding: 14px; background: rgba(201,169,97,.1); border: 1px solid rgba(201,169,97,.3); border-radius: 4px;">
            <p style="margin: 0; font-size: 14px; color: var(--slate-600);">
                After submitting, bring an accepted ID to the office so we can verify your documents and approve your application.
            </p>
        </div>
    </section>

    <section class="card-light">
        <label style="display: flex; gap: 12px; align-items: flex-start; font-size: 15px;">
            <input type="checkbox" name="terms" value="1" required @checked(old('terms')) style="margin-top: 4px; width: 20px; height: 20px;">
            <span>
                I confirm that the information provided is accurate and I accept the
                <a href="{{ route('legal.club-rules') }}" class="link-claret" target="_blank">club rules</a> and
                <a href="{{ route('legal.terms') }}" class="link-claret" target="_blank">terms and conditions</a>.
            </span>
        </label>
        @error('terms')<p class="error-text">{{ $message }}</p>@enderror
    </section>

    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
        <button type="submit" class="btn btn-navy">Submit application</button>
        <a href="{{ route('account.applications.start') }}" class="btn btn-line-dark">Cancel</a>
    </div>
</form>
@endsection
