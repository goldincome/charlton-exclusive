@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(48px, 6vw, 80px);">
    <div class="container-x">
        <nav aria-label="Breadcrumb" style="font-size: 14px; color: var(--mist-300); margin-bottom: 20px;">
            <a href="{{ route('home') }}">Home</a> <span aria-hidden="true">/</span>
            <a href="{{ route('rooms.index') }}">Meeting rooms</a> <span aria-hidden="true">/</span>
            <a href="{{ route('rooms.show', $hall->slug) }}">{{ $hall->name }}</a> <span aria-hidden="true">/</span>
            <span aria-current="page" style="color: var(--ivory-100);">Request a booking</span>
        </nav>

        <div style="display: flex; flex-wrap: wrap; gap: 48px; align-items: flex-start;">
            <div style="flex: 1 1 460px;">
                <p class="eyebrow" style="color: var(--gold-500);">Book {{ $hall->name }}</p>
                <h1 style="font-size: clamp(34px, 4.4vw, 52px); margin: 14px 0 20px;">Request a booking</h1>
                <p style="font-size: 17px; color: var(--mist-300); margin-bottom: 28px;">
                    Choose your date, time and number of guests. The club will confirm your request within 72 hours. No payment is taken until the booking is approved.
                </p>

                <div class="card-dark" style="margin-bottom: 28px;">
                    <dl style="display: grid; gap: 10px; font-size: 15px; margin: 0;">
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(201,169,97,.25); padding-bottom: 8px;">
                            <dt style="color: var(--mist-300);">Capacity</dt>
                            <dd style="margin: 0;">{{ $hall->capacity }} guests</dd>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(201,169,97,.25); padding-bottom: 8px;">
                            <dt style="color: var(--mist-300);">Minimum</dt>
                            <dd style="margin: 0;">{{ $hall->min_hours }} hours</dd>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <dt style="color: var(--mist-300);">Changeover</dt>
                            <dd style="margin: 0;">{{ $hall->buffer_minutes }} min buffer</dd>
                        </div>
                    </dl>
                </div>

                @if ($memberRate)
                    <p style="font-size: 15px; color: var(--mist-300); margin-bottom: 20px;">
                        Your member rate: <strong style="color: var(--champagne-200);">£{{ number_format($memberRate / 100, 0) }}</strong> per hour
                    </p>
                @endif

                <ul style="margin: 0 0 28px 1.1rem; padding: 0; color: var(--mist-300); font-size: 15px;">
                    <li style="margin-bottom: 8px;">Booking slot opens 30 minutes before your start time</li>
                    <li style="margin-bottom: 8px;">Full refund if cancelled 7+ days before start</li>
                    <li style="margin-bottom: 8px;">50% refund if cancelled 48 hours to 7 days before</li>
                    <li>No refund if cancelled within 48 hours</li>
                </ul>
            </div>

            <aside style="flex: 1 1 420px;">
                <div class="card-dark">
                    <h2 style="font-size: 26px; margin-bottom: 20px;">Booking details</h2>

                    @if ($errors->any())
                        <div role="alert" style="border: 1px solid #F0A7B2; background: rgba(158,43,60,.16); color: #F0A7B2; padding: 14px 16px; border-radius: 4px; margin-bottom: 20px;">
                            Please check the highlighted fields and try again.
                        </div>
                    @endif

                    @guest
                        <div role="alert" style="border: 1px solid #C9A961; background: rgba(201,169,97,.12); color: var(--champagne-200); padding: 14px 16px; border-radius: 4px; margin-bottom: 20px;">
                            <p style="margin: 0 0 12px;"><strong>Sign in to continue</strong></p>
                            <p style="margin: 0 0 14px;">You need an account to request a booking.</p>
                            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                                <a href="{{ route('login') }}" class="btn btn-gold btn-sm">Sign in</a>
                                <a href="{{ route('register') }}" class="btn btn-line btn-sm">Create account</a>
                            </div>
                        </div>
                    @endguest

                    <form method="POST" action="{{ route('account.book.room.store', $hall) }}" style="display: grid; gap: 18px;" @guest disabled @endguest>
                        @csrf
                        <div>
                            <label class="field-label" for="start_time">Start date & time</label>
                            <input class="field-dark" id="start_time" name="start_time" type="datetime-local" value="{{ old('start_time') }}" required @error('start_time') aria-invalid="true" aria-describedby="start_time-error" @enderror @guest disabled @endguest>
                            @error('start_time')<p class="error-text" id="start_time-error">{{ $message }}</p>@enderror
                            <p style="font-size: 13px; color: var(--mist-300); margin-top: 6px;">Bookings must be in {{ $hall->min_hours }}-hour multiples</p>
                        </div>

                        <div>
                            <label class="field-label" for="end_time">End date & time</label>
                            <input class="field-dark" id="end_time" name="end_time" type="datetime-local" value="{{ old('end_time') }}" required @error('end_time') aria-invalid="true" aria-describedby="end_time-error" @enderror @guest disabled @endguest>
                            @error('end_time')<p class="error-text" id="end_time-error">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="field-label" for="guests">Number of guests</label>
                            <input class="field-dark" id="guests" name="guests" type="number" min="1" max="{{ $hall->capacity }}" value="{{ old('guests', 1) }}" required @guest disabled @endguest>
                            @error('guests')<p class="error-text">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="field-label" for="notes">Notes <span style="font-weight: 400; color: var(--mist-300);">(optional)</span></label>
                            <textarea class="field-dark" id="notes" name="notes" rows="3" @guest disabled @endguest>{{ old('notes') }}</textarea>
                            @error('notes')<p class="error-text">{{ $message }}</p>@enderror
                        </div>

                        <label style="display: flex; gap: 12px; align-items: flex-start; font-size: 14px; color: var(--mist-300);">
                            <input type="checkbox" name="rules_ack" value="1" required @checked(old('rules_ack')) style="margin-top: 4px; width: 20px; height: 20px;" @guest disabled @endguest>
                            <span>I accept the <a href="{{ route('legal', 'club-rules') }}" class="link-gold">club rules</a> and the cancellation policy above.</span>
                        </label>
                        @error('rules_ack')<p class="error-text">{{ $message }}</p>@enderror

                        <button type="submit" class="btn btn-gold" style="width: 100%;" @guest disabled @endguest>Send booking request</button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection