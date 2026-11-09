<footer style="background: var(--navy-900); border-top: 1px solid rgba(201,169,97,.3); padding: 64px 0 32px; margin-top: auto;">
    <div class="container-x">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px;">
            <div>
                <div style="font-family: var(--font-display); font-size: 26px; font-weight: 600;">Charlton</div>
                <div class="eyebrow" style="font-size: 11px; color: var(--gold-500); margin-bottom: 16px;">Exclusive Club</div>
                <p style="color: var(--mist-300); font-size: 14px;">A private address in South East London.</p>
                <address style="font-style: normal; color: var(--mist-300); font-size: 14px; margin-top: 16px;">
                    Unit 6, Block 3 Woolwich<br>
                    Dockyard Industrial Estate<br>
                    Woolwich Church St<br>
                    Charlton, London SE18 5PQ
                </address>
                <p style="margin-top: 12px; font-size: 14px;">
                    <a href="tel:+442032474747" style="color: var(--champagne-200);">+44 (0) 2032 474747</a><br>
                    <a href="mailto:info@charltonexclusiveclub.co.uk" style="color: var(--champagne-200);">info@charltonexclusiveclub.co.uk</a>
                </p>
            </div>

            <div>
                <div class="eyebrow" style="font-size: 12px; color: var(--gold-500); margin-bottom: 16px;">The club</div>
                <div style="display: grid; gap: 8px; font-size: 15px;">
                    <a href="{{ route('about') }}">About</a>
                    <a href="{{ route('membership.index') }}">Membership</a>
                    <a href="{{ route('gallery.index') }}">Gallery</a>
                    <a href="{{ route('journal.index') }}">Journal</a>
                    <a href="{{ route('faq') }}">FAQ</a>
                </div>
            </div>

            <div>
                <div class="eyebrow" style="font-size: 12px; color: var(--gold-500); margin-bottom: 16px;">Book</div>
                <div style="display: grid; gap: 8px; font-size: 15px;">
                    <a href="{{ route('rooms.index') }}">Meeting rooms</a>
                    <a href="{{ route('events.index') }}">Events</a>
                    <a href="{{ route('dining') }}">Dining</a>
                    <a href="{{ route('contact') }}">Contact</a>
                    <a href="{{ route('account.applications.start') }}">Request an invitation</a>
                </div>
            </div>

            <div>
                <div class="eyebrow" style="font-size: 12px; color: var(--gold-500); margin-bottom: 16px;">Legal</div>
                <div style="display: grid; gap: 8px; font-size: 15px;">
                    <a href="{{ route('legal', 'club-rules') }}">Club rules</a>
                    <a href="{{ route('legal', 'terms') }}">Terms</a>
                    <a href="{{ route('legal', 'privacy') }}">Privacy</a>
                    <a href="{{ route('legal', 'cookies') }}">Cookies and settings</a>
                    <a href="{{ route('legal', 'accessibility') }}">Accessibility</a>
                </div>
            </div>
        </div>

        <div style="margin-top: 48px; padding-top: 24px; border-top: 1px solid rgba(201,169,97,.3); color: var(--mist-300); font-size: 13px; display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between;">
            <span>© {{ date('Y') }} Charlton Exclusive Club. Company no. {{ \App\Models\AppConfig::get('company_number', '[COMPANY NUMBER]') }} · VAT no. {{ \App\Models\AppConfig::get('vat_number', '[VAT NUMBER]') }}</span>
            <button type="button" data-cookie-settings class="link-gold" style="background: none; border: 0; font: inherit; cursor: pointer;">Cookie settings</button>
        </div>
    </div>
</footer>
