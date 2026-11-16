@extends('layouts.panel', ['title' => 'Register Walk-in Application'])

@section('content')
<header style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin: 0 0 8px;">Register Walk-in Application</h1>
    <p style="color: var(--slate-600); max-width: 820px;">
        For applicants who submitted physical application forms and ID cards in person. Select whether the applicant is an existing user or a new account, search by name, email, ref no, or phone no directly from the dropdown, upload documents, assign the membership package, and notify them for payment.
    </p>
</header>

@if ($errors->any())
    <div style="background: #FFEBEE; color: #C62828; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #D32F2F;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card-light" style="max-width: 800px;">
    <form method="POST" action="{{ route('admin.applications.store') }}" enctype="multipart/form-data" style="display: grid; gap: 20px;">
        @csrf

        <div>
            <label class="field-label">Account Type <span style="color: var(--claret-700);">*</span></label>
            <div style="display: flex; gap: 24px; margin-top: 8px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500;">
                    <input type="radio" name="account_type" value="new" id="account_type_new" @checked(old('account_type', 'new') === 'new') onchange="toggleAccountType()">
                    <span>New Account</span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500;">
                    <input type="radio" name="account_type" value="existing" id="account_type_existing" @checked(old('account_type') === 'existing') onchange="toggleAccountType()">
                    <span>Existing Account</span>
                </label>
            </div>
        </div>

        <div id="existing_user_section" style="display: none;">
            <label class="field-label" for="user_search_input">Select Existing User <span style="color: var(--claret-700);">*</span></label>
            <input type="text" class="field" id="user_search_input" list="users_datalist" placeholder="Search by name, email, ref no or phone..." style="min-height: 48px;" autocomplete="off" oninput="onUserSearchInput(this)" onchange="onUserSearchChange(this)">
            <input type="hidden" name="user_id" id="user_id_hidden" value="{{ old('user_id') }}">
            <datalist id="users_datalist">
                @foreach ($users as $u)
                    @php
                        $refNo = $u->memberProfile?->member_number ?? $u->applications()->latest()->value('reference');
                        $display = $u->name.' — '.$u->email.($refNo ? ' ('.$refNo.')' : '');
                    @endphp
                    <option value="{{ $display }}" data-id="{{ $u->id }}" data-name="{{ $u->name }}" data-email="{{ $u->email }}" data-phone="{{ $u->phone }}"></option>
                @endforeach
            </datalist>
            <p style="font-size: 12px; color: var(--slate-600); margin: 4px 0 0;">Search by name, email, ref no or phone no. Shows 50 most recent records initially.</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div>
                <label class="field-label" for="name">Full Name <span style="color: var(--claret-700);">*</span></label>
                <input type="text" class="field" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Arthur Pendelton" style="min-height: 48px;">
            </div>
            <div>
                <label class="field-label" for="email">Email Address <span style="color: var(--claret-700);">*</span></label>
                <input type="email" class="field" id="email" name="email" value="{{ old('email') }}" required placeholder="e.g. arthur@example.com" style="min-height: 48px;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div>
                <label class="field-label" for="phone">Phone Number</label>
                <input type="text" class="field" id="phone" name="phone" value="{{ old('phone') }}" placeholder="e.g. +44 20 7946 0912" style="min-height: 48px;">
            </div>
            <div>
                <label class="field-label" for="plan_id">Membership Package <span style="color: var(--claret-700);">*</span></label>
                <select class="field" id="plan_id" name="plan_id" required style="min-height: 48px;">
                    <option value="">Select membership plan...</option>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>
                            {{ $plan->name }} (£{{ number_format($plan->annual_fee / 100, 2) }}/yr)
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="field-label" for="id_document">ID Card / Verification Document (JPG, PNG, PDF, max 10MB) <span style="color: var(--claret-700);">*</span></label>
            <input type="file" class="field" id="id_document" name="id_document" required accept=".jpg,.jpeg,.png,.pdf" style="padding: 10px;">
            <p style="font-size: 13px; color: var(--slate-600); margin: 4px 0 0;">Upload scanned copy or photo of the applicant's physical ID card presented in person.</p>
        </div>

        <div>
            <label class="field-label" for="cv_document">Supporting Document / CV / Notes (Optional)</label>
            <input type="file" class="field" id="cv_document" name="cv_document" accept=".pdf,.doc,.docx" style="padding: 10px;">
        </div>

        <div>
            <label class="field-label" for="notes">Admin Notes / Physical Submission Details</label>
            <textarea class="field" id="notes" name="notes" rows="3" placeholder="Notes on physical application form verification...">{{ old('notes') }}</textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 10px;">
            <a href="{{ route('admin.applications.index') }}" class="btn btn-line-dark" style="min-height: 48px; display: inline-flex; align-items: center;">Cancel</a>
            <button type="submit" class="btn btn-navy" style="min-height: 48px;">Register &amp; Notify for Payment</button>
        </div>
    </form>
</div>

<script>
let userMap = new Map();
@foreach ($users as $u)
@php
    $refNo = $u->memberProfile?->member_number ?? $u->applications()->latest()->value('reference');
    $display = $u->name.' — '.$u->email.($refNo ? ' ('.$refNo.')' : '');
@endphp
userMap.set("{{ $display }}", { id: "{{ $u->id }}", name: "{{ $u->name }}", email: "{{ $u->email }}", phone: "{{ $u->phone }}" });
@endforeach

let searchTimer = null;

function toggleAccountType() {
    const isExisting = document.getElementById('account_type_existing').checked;
    const existingSection = document.getElementById('existing_user_section');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const userSearchInput = document.getElementById('user_search_input');
    const userIdHidden = document.getElementById('user_id_hidden');

    if (isExisting) {
        existingSection.style.display = 'block';
        userSearchInput.required = true;
        nameInput.readOnly = true;
        emailInput.readOnly = true;
        nameInput.style.background = '#f1f5f9';
        emailInput.style.background = '#f1f5f9';
        onUserSearchChange(userSearchInput);
    } else {
        existingSection.style.display = 'none';
        userSearchInput.required = false;
        userSearchInput.value = '';
        userIdHidden.value = '';
        nameInput.readOnly = false;
        emailInput.readOnly = false;
        nameInput.style.background = '';
        emailInput.style.background = '';
    }
}

function onUserSearchInput(input) {
    const val = input.value;

    if (val === '') {
        document.getElementById('user_id_hidden').value = '';
        document.getElementById('name').value = '';
        document.getElementById('email').value = '';
        document.getElementById('phone').value = '';
    } else {
        const match = userMap.get(val);
        if (match) {
            document.getElementById('user_id_hidden').value = match.id;
            document.getElementById('name').value = match.name;
            document.getElementById('email').value = match.email;
            document.getElementById('phone').value = match.phone || '';
        } else {
            document.getElementById('user_id_hidden').value = '';
        }
    }

    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        fetch('{{ route("admin.applications.users-search") }}?q=' + encodeURIComponent(val))
            .then(res => res.json())
            .then(users => {
                const datalist = document.getElementById('users_datalist');
                datalist.innerHTML = '';
                users.forEach(u => {
                    const refNo = u.member_number || '';
                    const displayText = `${u.name} — ${u.email}${refNo ? ' (' + refNo + ')' : ''}`;
                    userMap.set(displayText, { id: u.id, name: u.name, email: u.email, phone: u.phone });
                    const opt = document.createElement('option');
                    opt.value = displayText;
                    datalist.appendChild(opt);
                });
            });
    }, 300);
}

function onUserSearchChange(input) {
    onUserSearchInput(input);
}

document.addEventListener('DOMContentLoaded', () => {
    toggleAccountType();
    const oldUserId = "{{ old('user_id') }}";
    if (oldUserId) {
        for (const [text, data] of userMap.entries()) {
            if (String(data.id) === String(oldUserId)) {
                document.getElementById('user_search_input').value = text;
                document.getElementById('user_id_hidden').value = data.id;
                document.getElementById('name').value = data.name;
                document.getElementById('email').value = data.email;
                document.getElementById('phone').value = data.phone || '';
                break;
            }
        }
    }
});
</script>
@endsection
