@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(56px, 7vw, 96px);">
    <div class="container-x" style="display: flex; flex-wrap: wrap; gap: 64px;">
        <div style="flex: 1 1 380px;">
            <p class="eyebrow" style="color: var(--gold-500);">Contact</p>
            <h1 style="font-size: clamp(36px, 4.6vw, 56px); margin: 18px 0 20px;">Write to the club</h1>
            <p style="font-size: 18px; color: var(--mist-300); margin-bottom: 32px;">
                For membership, room hire, events or press enquiries, use the form or contact the secretary directly.
            </p>

            <address style="font-style: normal; margin-bottom: 24px;">
                Charlton Exclusive Club<br>
                Unit 6, Block 3 Woolwich<br>
                Dockyard Industrial Estate<br>
                Woolwich Church St<br>
                Charlton, London SE18 5PQ
            </address>

            <p style="margin-bottom: 8px;"><a href="tel:+442032474747" class="link-gold">+44 (0) 2032 474747</a></p>
            <p style="margin-bottom: 24px;"><a href="mailto:info@charltonexclusiveclub.co.uk" class="link-gold">info@charltonexclusiveclub.co.uk</a></p>

            <p style="font-size: 14px; color: var(--mist-300);">
                We aim to reply within 14 days. Membership applications are made through the online application form.
            </p>
        </div>

        <div style="flex: 1 1 440px;">
            <div class="card-dark">
                <h2 style="font-size: 26px; margin-bottom: 20px;">Send a message</h2>

                @if ($errors->any())
                    <div role="alert" style="border: 1px solid #F0A7B2; background: rgba(158,43,60,.16); color: #F0A7B2; padding: 14px 16px; border-radius: 4px; margin-bottom: 20px;">
                        Please check the highlighted fields and try again.
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}" style="display: grid; gap: 18px;">
                    @csrf
                    <div>
                        <label class="field-label" for="name">Your name</label>
                        <input class="field-dark" id="name" name="name" value="{{ old('name') }}" required autocomplete="name" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                        @error('name')<p class="error-text" id="name-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="field-label" for="email">Email</label>
                        <input class="field-dark" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                        @error('email')<p class="error-text" id="email-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="field-label" for="phone">Phone <span style="font-weight: 400; color: var(--mist-300);">(optional)</span></label>
                        <input class="field-dark" id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel">
                    </div>
                    <div>
                        <label class="field-label" for="message">Message</label>
                        <textarea class="field-dark" id="message" name="message" rows="6" required @error('message') aria-invalid="true" aria-describedby="message-error" @enderror>{{ old('message') }}</textarea>
                        @error('message')<p class="error-text" id="message-error">{{ $message }}</p>@enderror
                    </div>
                    <label style="display: flex; gap: 12px; align-items: flex-start; font-size: 14px; color: var(--mist-300);">
                        <input type="checkbox" name="consent" value="1" required style="margin-top: 4px; width: 20px; height: 20px;" @checked(old('consent'))>
                        <span>I agree that the club may use my details to respond to this enquiry, as described in the <a href="{{ route('legal', 'privacy') }}" class="link-gold">privacy policy</a>.</span>
                    </label>
                    @error('consent')<p class="error-text">{{ $message }}</p>@enderror

                    <button type="submit" class="btn btn-gold" style="justify-self: start;">Send message</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection