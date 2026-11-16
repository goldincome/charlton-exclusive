@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(48px, 6vw, 80px);">
    <div class="container-x">
        <nav aria-label="Breadcrumb" style="font-size: 14px; color: var(--mist-300); margin-bottom: 20px;">
            <a href="{{ route('home') }}">Home</a> <span aria-hidden="true">/</span>
            <a href="{{ route('dining') }}">Dining</a> <span aria-hidden="true">/</span>
            <span aria-current="page" style="color: var(--ivory-100);">Book a table</span>
        </nav>

        <div style="display: flex; flex-wrap: wrap; gap: 48px; align-items: flex-start;">
            <div style="flex: 1 1 460px;">
                <p class="eyebrow" style="color: var(--gold-500);">Table booking</p>
                <h1 style="font-size: clamp(34px, 4.4vw, 52px); margin: 14px 0 20px;">{{ $table->name }}</h1>
                <p style="font-size: 17px; color: var(--mist-300); margin-bottom: 28px;">
                    Book a table for your visit. The club will confirm your reservation within 72 hours.
                </p>

                <div class="card-dark" style="margin-bottom: 28px;">
                    <dl style="display: grid; gap: 10px; font-size: 15px; margin: 0;">
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(201,169,97,.25); padding-bottom: 8px;">
                            <dt style="color: var(--mist-300);">Capacity</dt>
                            <dd style="margin: 0;">{{ $table->capacity }} guests</dd>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(201,169,97,.25); padding-bottom: 8px;">
                            <dt style="color: var(--mist-300);">Category</dt>
                            <dd style="margin: 0;">{{ $table->category->name ?? 'Standard' }}</dd>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <dt style="color: var(--mist-300);">Location</dt>
                            <dd style="margin: 0;">{{ $table->location ?: 'Main dining room' }}</dd>
                        </div>
                    </dl>
                </div>

                <p style="font-size: 15px; color: var(--mist-300);">
                    Table bookings are requested and confirmed by the club. No payment is taken for table reservations.
                </p>
            </div>

            <aside style="flex: 1 1 400px;">
                <div class="card-dark">
                    <h2 style="font-size: 26px; margin-bottom: 20px;">Request a table</h2>

                    @if ($errors->any())
                        <div role="alert" style="border: 1px solid #F0A7B2; background: rgba(158,43,60,.16); color: #F0A7B2; padding: 14px 16px; border-radius: 4px; margin-bottom: 20px;">
                            Please check the highlighted fields and try again.
                        </div>
                    @endif

                    @guest
                        <div role="alert" style="border: 1px solid #C9A961; background: rgba(201,169,97,.12); color: var(--champagne-200); padding: 14px 16px; border-radius: 4px; margin-bottom: 20px;">
                            <p style="margin: 0 0 12px;"><strong>Sign in to continue</strong></p>
                            <p style="margin: 0 0 14px;">You need an account to request a table booking.</p>
                            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                                <a href="{{ route('login') }}" class="btn btn-gold btn-sm">Sign in</a>
                                <a href="{{ route('register') }}" class="btn btn-line btn-sm">Create account</a>
                            </div>
                        </div>
                    @endguest

                    <form method="POST" action="{{ route('account.book.table.store', $table) }}" style="display: grid; gap: 18px;" @guest disabled @endguest>
                        @csrf
                        <div>
                            <label class="field-label" for="date">Date</label>
                            <input class="field-dark" id="date" name="date" type="date" value="{{ old('date') }}" required @guest disabled @endguest>
                            @error('date')<p class="error-text">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="field-label" for="time">Time</label>
                            <input class="field-dark" id="time" name="time" type="time" value="{{ old('time') }}" required @guest disabled @endguest>
                            @error('time')<p class="error-text">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="field-label" for="guests">Number of guests</label>
                            <input class="field-dark" id="guests" name="guests" type="number" min="1" max="{{ $table->capacity }}" value="{{ old('guests', 1) }}" required @guest disabled @endguest>
                            @error('guests')<p class="error-text">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="btn btn-gold" style="width: 100%;" @guest disabled @endguest>Request this table</button>
                        <p style="font-size: 13px; color: var(--mist-300); margin-top: -8px;">The club will confirm your table by email.</p>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('date');
    if (dateInput) {
        dateInput.addEventListener('click', function() {
            if (typeof this.showPicker === 'function') {
                try { this.showPicker(); } catch (e) {}
            }
        });
    }
});
</script>
@endsection