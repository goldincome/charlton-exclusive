# Charlton Exclusive Club: SEO, AI Indexing and Design Addendum

Companion to Specification v3.2 (Laravel Edition). Applies to the **public website only**. The admin, staff and member panels, checkout and account areas are explicitly excluded (see section 12).

No tool here can give real search volumes. The keyword lists below are **targets to validate** in Google Search Console and Google Keyword Planner after launch. Nobody can guarantee rankings; this spec maximises eligibility and quality.

---

## 1. Critical technical requirement: server-rendered public pages

A client-only JavaScript app sends crawlers an empty shell, which hurts indexing and AI retrieval (many AI crawlers do not run JavaScript). So:

- Public pages must be **server-side rendered to full HTML**, with title, meta, canonical, schema and content present in the initial response.
- The stack: **Laravel 13 with Blade views**, which render complete HTML on the server by default. No client-side framework is needed for public pages; Alpine.js is used only for small enhancements (menus, lightbox, booking widget). Panels and checkout are Blade (optionally Livewire) behind login.
- Dynamic public pages (rooms, events, gallery, sections) render from MySQL on the server, with caching (Laravel cache and full-page or fragment caching, invalidated on content change).
- A shared Blade layout (`layouts/public.blade.php`) outputs the title, meta description, canonical, Open Graph, Twitter tags and JSON-LD from per-page data, so no public page can ship without them.

---

## 2. Public URL structure and added pages

Lowercase, hyphenated, trailing slash policy chosen once and enforced with 301s. Slugs are stored with history so renamed pages 301 to the new URL.

| URL | Page | Indexed |
| --- | --- | --- |
| `/` | Home | Yes |
| `/about/` | About, history, how the club is run (E-E-A-T) | Yes |
| `/membership/` | Categories, benefits, pricing | Yes |
| `/membership/apply/` | Application start | Yes |
| `/membership/sections/{slug}/` | One page per section (gym, tennis, snooker...) | Yes |
| `/meeting-rooms/` | Room listing | Yes |
| `/meeting-rooms/{slug}/` | Room detail and booking CTA | Yes |
| `/meeting-rooms/{area}/` | Area landing pages (section 5) | Yes |
| `/events/`, `/events/{slug}/` | Events | Yes |
| `/dining/` | Century Dining Room and menu (added) | Yes |
| `/gallery/`, `/gallery/{album}/` | Public photos only | Yes |
| `/faq/` | FAQ (added) | Yes |
| `/journal/`, `/journal/{slug}/` | Articles and news with named authors (added) | Yes |
| `/contact/` | Contact, map, NAP | Yes |
| `/club-rules/`, `/terms/`, `/privacy/`, `/cookies/`, `/accessibility/` | Legal | Yes (low priority) |
| `/login/`, `/forgot-password/` | Auth | **No** |

---

## 3. Keyword strategy

### Geography

The registered address is **SE18 5PQ (Woolwich postcode)**, but the club trades as "Charlton" (SE7). Target both names.

| Tier | Areas | Use |
| --- | --- | --- |
| 1 (core) | Charlton, Woolwich, Greenwich, Blackheath, Westcombe Park | Area pages and all core copy |
| 2 (near) | Plumstead, Kidbrooke, Eltham, Thamesmead, Abbey Wood, Lewisham, Deptford | Area pages |
| 3 (wider SE London) | Bexleyheath, Welling, Sidcup, Catford, Erith, Bromley | Mentions in a "serving South East London" block; area page only if there is real demand in Search Console |

### Keyword clusters (examples to map and validate)

| Cluster | Example target queries | Page |
| --- | --- | --- |
| Meeting rooms | meeting room hire Charlton; meeting rooms Woolwich; boardroom hire Greenwich; hourly meeting room South East London; meeting room hire SE18 / SE7 | `/meeting-rooms/` and area pages |
| Members' club | private members club South East London; members club Greenwich; social club Charlton; join a private members club London SE | `/`, `/membership/` |
| Events and venue | private event venue Charlton; party venue South East London; corporate event space Woolwich; venue hire Greenwich | `/events/`, `/meeting-rooms/` |
| Dining | private dining Charlton; group dining Greenwich; party food ordering Woolwich | `/dining/` |
| Sections | snooker club Charlton; tennis club Woolwich; gym membership Greenwich (only the sections that exist) | each section page |
| Brand | charlton exclusive club; century dining room | Home, `/dining/` |

One primary keyword per page; secondary terms placed naturally. No keyword stuffing.

---

## 4. On-page rules and templates

Every public page needs: a unique `<title>` (aim 60 characters or fewer), unique meta description (150-155), exactly one `<h1>`, self-referencing canonical, Open Graph and Twitter tags (1200x630 image), logical heading order, descriptive internal links, and the NAP footer.

| Page | Title | Meta description |
| --- | --- | --- |
| Home | Charlton Exclusive Club \| Private Members' Club, SE London | A private members' club in Charlton, South East London: dining, sports sections, events and meeting rooms. Apply for membership today. |
| Meeting rooms | Meeting Room Hire in Charlton & Woolwich \| CEC | Hire elegant meeting rooms in Charlton, SE London from 3 hours. VAT-inclusive hourly rates, AV equipment and dining on site. Request your date online. |
| Area page | Meeting Rooms Near Greenwich \| Charlton Exclusive Club | Looking for meeting room hire near Greenwich? \[Travel time and route\] to our Charlton venue. View rooms, rates and book. |
| Events | Events at Charlton Exclusive Club \| What's On | \[Upcoming event types and dates, ticket info\] |

Titles and descriptions are editable fields per room, event, section and article (section 14).

---

## 5. Area landing pages: avoid doorway spam

Google treats near-duplicate pages that only swap the place name as spam. Each area page needs **at least 400 words of unique, useful content**:

- Real travel information from that area to the club (stations, bus routes, drive time, parking). **The club must supply or verify this; I have not invented it.**
- Which rooms suit visitors from there, local business context, and a map link.
- A short FAQ specific to that area.
- Links to the rooms and booking CTA.

Launch with Tier 1 only (about 5 pages), then add more based on Search Console data.

---

## 6. Structured data (JSON-LD, server-rendered)

| Page | Schema |
| --- | --- |
| Site-wide | `Organization` (name, logo, url, sameAs profiles, telephone, address), `WebSite` |
| Home, Contact | `LocalBusiness` with address, geo, openingHoursSpecification, telephone, areaServed (the Tier 1-2 areas), image |
| Rooms | `Product`-style `Offer` or `Place` with price in GBP, `ImageObject`; `BreadcrumbList` |
| Events | `Event` with name, startDate (ISO with timezone), endDate, eventStatus, eventAttendanceMode, location, offers (price, currency, availability, url), image |
| Dining | `Restaurant`/`Menu` markup if the menu is public |
| FAQ | `FAQPage` (no longer yields rich results for most sites, but still helps machines understand the page) |
| Articles | `Article` with named `author` (`Person`), datePublished, dateModified |
| All inner pages | `BreadcrumbList` |

Schema must mirror visible content exactly. Do **not** mark up your own testimonials as `Review` stars (Google does not show self-serving review rich results). Validate in CI with the Schema.org validator and Google's Rich Results Test.

---

## 7. E-E-A-T (experience, expertise, authoritativeness, trust)

Build real signals; never fabricate.

- **About page:** the club's story, ownership, why it exists, Charlton/Woolwich heritage, company number, registered address.
- **People:** named committee and management with short bios and photos (with their consent). Named authors with bios on every journal article, plus "last updated" dates.
- **Experience:** real photos and short write-ups of actual events (the gallery, public images only), real member quotes with permission.
- **Trust:** HTTPS everywhere, visible phone, email and address, transparent prices, cancellation and refund summary on booking pages, legal pages one click away, accessibility statement, secure-payment (Stripe) notice.
- **Off-site:** a verified Google Business Profile, consistent NAP, real reviews, local press and partner links (chamber of commerce, local business directories, borough listings).
- **Content quality:** original room, event and dining content. No AI-generated filler pages, and no unreviewed claims.

---

## 8. AI indexing (search engines and AI answer engines)

1. **Server-rendered semantic HTML** (section 1): clear headings, lists, tables for rates and amenities, short answer-first paragraphs, a visible FAQ.
2. **Entity clarity:** the same name, address, phone and descriptions everywhere (site, schema, GBP, directories).
3. **robots.txt policy:** allow search and user-triggered AI agents so the club can be cited. Training crawlers are a choice; the default below allows them, and a single config switch blocks them. Crawler names as published in 2026: `OAI-SearchBot`, `ChatGPT-User`, `GPTBot` (OpenAI), `Claude-SearchBot`, `Claude-User`, `ClaudeBot` (Anthropic), `PerplexityBot`, `Perplexity-User`, `Google-Extended` and `Applebot-Extended` (opt-out tokens, not crawlers). `Googlebot` also powers Google's AI Overviews, so never block it. Re-check the list before launch because it changes.
4. **Bing:** register Bing Webmaster Tools and submit the sitemap; use **IndexNow** to ping on content changes. Several AI assistants draw on Bing's index.
5. **`/llms.txt`:** include a short Markdown file describing the club and linking key pages. No major engine has confirmed it affects ranking or citations, so treat it as a low-cost extra.
6. **Freshness:** real `lastmod` and visible "updated" dates on events, rates and rules.

---

## 9. Sitemap and robots.txt

**Sitemap** (generated dynamically, cached, regenerated on content change):

- `/sitemap.xml` is a **sitemap index** pointing to `sitemap-pages.xml`, `-rooms.xml`, `-events.xml`, `-gallery.xml` (with `<image:image>` entries for public photos), `-areas.xml`, `-sections.xml`, `-journal.xml`.
- Includes only canonical, indexable, 200-status public URLs. Excludes noindex pages, redirects, 404s, members-only photos, and everything in section 12.
- `lastmod` is the real last content change from the database. Omit `priority` and `changefreq` (Google ignores them).
- Events: keep past events indexable with "event ended" status and link to upcoming events, not 404.
- Submit in Google Search Console and Bing Webmaster Tools; list in robots.txt.

**robots.txt (served dynamically, environment-aware):**

```
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /staff/
Disallow: /member/
Disallow: /account/
Disallow: /dashboard/
Disallow: /checkout/
Disallow: /api/
Disallow: /uploads/private/

# AI search and user-triggered agents: allowed
User-agent: OAI-SearchBot
Allow: /
User-agent: ChatGPT-User
Allow: /
User-agent: Claude-SearchBot
Allow: /
User-agent: Claude-User
Allow: /
User-agent: PerplexityBot
Allow: /
User-agent: Perplexity-User
Allow: /

# Training crawlers: configurable (default allow)
User-agent: GPTBot
Allow: /
User-agent: ClaudeBot
Allow: /
User-agent: Google-Extended
Allow: /

Sitemap: https://[DOMAIN]/sitemap.xml
```

Staging and dev environments: `Disallow: /` plus `noindex` header plus basic auth.

---

## 10. Technical SEO and performance

- **Core Web Vitals targets:** LCP 2.5 s or less, INP 200 ms or less, CLS 0.1 or less (mobile, 75th percentile).
- **Images (stored locally):** generate WebP/AVIF in multiple widths at upload (queued job using Intervention Image), serve `srcset`/`sizes`, explicit width and height, lazy-load below the fold, preload the hero image, descriptive alt text (required field on upload), meaningful file names, long-cache headers with fingerprinted URLs.
- Fonts: self-host, `font-display: swap`, subset, preload headings font.
- gzip/Brotli, HTTP/2, CDN or reverse-proxy caching for public pages.
- One canonical per page, HTTPS and one hostname (301 `www` to apex or the reverse), custom 404 with helpful links, 301s for any URL change.
- Filters, sort and search results use `noindex, follow`; pagination uses normal crawlable links.
- Mobile-first, accessible (WCAG 2.1 AA) markup: accessibility and SEO overlap.

---

## 11. Local and off-site SEO checklist (outside the codebase)

Google Business Profile (categories, photos, hours, services, posts), Bing Places, Apple Business Connect, consistent NAP, reviews process (ask real customers, reply to all), local directories, partnerships with nearby businesses and organisations, Search Console and Analytics (consent-based) from day one.

---

## 12. What must NOT be indexed or exposed

| Area | Controls |
| --- | --- |
| `/admin`, `/staff`, `/member`, `/account`, `/dashboard` | robots.txt disallow **and** `X-Robots-Tag: noindex, nofollow` header **and** authentication (robots.txt alone does not stop indexing) |
| Login, reset, application steps after step 1, booking forms, checkout, payment return pages | `noindex`, excluded from sitemap |
| Members-only gallery photos | Never in HTML for logged-out users, never in sitemap, served only through authenticated routes |
| `/api` and uploads directory | Disallowed; private uploads never publicly served |
| Staging/dev | Auth wall plus noindex |
| Sitemap and logs | Never list private URLs |

---

## 13. Measurement

Search Console (queries, indexing, CWV), Bing Webmaster Tools, GBP insights, consent-based analytics, server-log tracking of AI crawler visits, and monthly rank tracking of the section 3 keywords by area.

---

## 14. Data-model and test additions

**Tables/fields:** `seo_title`, `seo_description`, `slug`, `og_image` on rooms, events, sections, journal posts and area pages; `slug_history` and `redirect`; `area_page`; `journal_post` and `author_profile`; `team_profile`; `faq_item`; `testimonial` (consent flag).

**Laravel implementation notes:** `robots.txt` and the sitemap files are served by controllers (not static files) so they stay environment-aware and dynamic; the sitemap can use `spatie/laravel-sitemap` or a custom generator run by the Task Scheduler and on content change. `X-Robots-Tag: noindex, nofollow` is applied by a middleware on the `/admin`, `/staff`, `/member`, `/account`, `/dashboard` and checkout route groups. Slug history and 301s are handled by a redirect middleware reading `slug_history` and `redirect`. IndexNow pings are sent from a queued job. Migrations, Eloquent models and Form Requests (for the editable `seo_title`, `seo_description`, `slug` and `og_image` fields) replace the previous Prisma and Zod layer.

**Automated checks (CI, Pest):** every public page has a unique title and description, one H1, a canonical and valid JSON-LD; the sitemap contains no noindex or private URLs; `robots.txt` and the sitemap return 200; no public route returns members-only images; Lighthouse mobile SEO and accessibility scores of 95 or more; Core Web Vitals budget checks.

---

# Part B. UI Design Direction

"Aesthetic" interpreted for "ecstatic": a design that feels beautiful and carries a clear message.

## 15. Message and concept

**Core message:** *quiet confidence; a private address in South East London where you belong.*

**Concept: "The Dockyard Room."** A refined interior at the Thames, drawing on Charlton and Woolwich's maritime heritage without clichés. Navy suggests the river at dusk, champagne gold suggests lamplight and polished brass, ivory suggests linen and calm.

**Design pillars:** discretion (not flashy), warmth (a club, not a bank), clarity (booking and joining are easy), and heritage (rooted in the place).

**Primary tagline:** "A private address in South East London." Alternatives: "Belong somewhere remarkable." / "Where Charlton meets."

**Tone of voice:** warm, assured, plain English. Short sentences. Invitation, not hard sell ("Request an invitation", "Reserve your date").

## 16. Palette (WCAG contrast checked)

| Token | Hex | Use |
| --- | --- | --- |
| `--navy-900` | `#0B1426` | Primary dark background, footer |
| `--navy-800` | `#16233F` | Cards on dark, header |
| `--gold-500` | `#C9A961` | Accents, buttons, hairlines, **only on navy** |
| `--champagne-200` | `#E8D8A8` | Highlights, hover, headings on navy |
| `--ivory-100` | `#F5EFE0` | Light section background |
| `--ink-900` | `#1A1D24` | Body text on ivory |
| `--slate-600` | `#565D6B` | Secondary text on ivory (5.77:1) |
| `--claret-700` | `#7A1F2E` | Links and small accents on ivory (8.87:1) |
| `--mist-300` | `#B9C1D3` | Secondary text on navy (10.18:1) |

Measured contrasts: ivory on navy 16.03:1; gold on navy 8.17:1; ink on ivory 14.7:1; navy text on gold buttons 8.17:1. **Gold on ivory fails (1.96:1): never use gold text on light backgrounds**, use claret or ink there. Verify the final shades again in the build.

```css
:root {
  --navy-900:#0B1426; --navy-800:#16233F; --gold-500:#C9A961;
  --champagne-200:#E8D8A8; --ivory-100:#F5EFE0; --ink-900:#1A1D24;
  --slate-600:#565D6B; --claret-700:#7A1F2E; --mist-300:#B9C1D3;
  --font-display:"Playfair Display", Georgia, serif;
  --font-body:"Inter", system-ui, -apple-system, "Segoe UI", sans-serif;
}
```

## 17. Typography

- **Headings:** Playfair Display, weight 500-600, with italic for one emphasised word per heading. Letter-spacing slightly open on small uppercase eyebrows.
- **Body:** Inter, 17-18 px, line-height 1.65, max line length about 70 characters.
- Scale 1.25 ratio (H1 about 56/44 px desktop/mobile). Self-hosted, subset, `font-display: swap`.

## 18. Imagery and graphic language

- Warm, real interior and event photography (members, dining, rooms), graded with a subtle navy tone. No stock handshake or generic skyline images.
- A subtle **river-contour line pattern** in gold at low opacity as a section background texture, a nod to the Thames and the Dockyard.
- Thin gold hairline dividers, generous whitespace, 8 px spacing grid, 4 px corner radius (crisp, not playful).
- Icons: thin-stroke line icons in champagne.

## 19. Layout and components

**Home page order:** (1) hero with tagline, one primary CTA "Request an invitation" and one secondary "Hire a room"; (2) three pillars: Dine, Gather, Belong; (3) membership teaser with categories; (4) meeting rooms with rates; (5) events carousel; (6) gallery strip; (7) testimonials and trust bar (real); (8) location with map and travel info; (9) final CTA band; (10) footer with NAP, links, legal.

| Component | Style |
| --- | --- |
| Header | Navy, logo left, gold underline on active link, sticky, "Book" button right; mobile drawer menu |
| Primary button | Gold fill, navy text, 44 px min height, champagne hover, ivory focus ring (3 px) |
| Secondary button | 1 px gold outline, ivory text on navy |
| Cards | `--navy-800` on navy sections, white-ivory with thin border on light sections; image on top, hairline separator |
| Booking widget | Clear stepper (Request, Approved, Pay, Confirmed) with status badges so the user always knows where they are |
| Forms | Large labels above fields, inline validation, plain-language errors |
| Rates table | Responsive table that collapses to stacked cards on mobile |
| Cookie banner | Compact, bottom, equal-weight Accept and Reject buttons |

## 20. Motion and interaction

Short fades and 8-12 px rises (200-300 ms), image hover zoom 1.03, no autoplay video with sound, no parallax. Respect `prefers-reduced-motion`. Skeleton loaders, not spinners, for galleries.

## 21. Accessibility and responsive rules

WCAG 2.1 AA, keyboard-visible focus, 44 px touch targets, alt text on every image, no information conveyed by colour alone, and a tested dark-on-light and light-on-dark scheme. Breakpoints 360 / 768 / 1024 / 1280. Mobile-first.

---

## 22. Open items

1. Domain name and canonical host (www or apex).
2. Club-verified travel information for each Tier 1 area page.
3. Which sections (gym, tennis, snooker...) actually exist, to create their pages.
4. Training-crawler policy (default: allow).
5. Named people and photos for the About and committee pages, with consent.
6. Logo files, so the palette can be checked against the real brand.