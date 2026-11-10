<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use App\Models\AreaPage;
use App\Models\Event;
use App\Models\GalleryAlbum;
use App\Models\Hall;
use App\Models\JournalPost;
use App\Models\Section;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $allowTraining = AppConfig::bool('allow_training_crawlers', true);

        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin/',
            'Disallow: /staff/',
            'Disallow: /member/',
            'Disallow: /account/',
            'Disallow: /dashboard/',
            'Disallow: /checkout/',
            'Disallow: /api/',
            'Disallow: /uploads/private/',
            '',
            '# AI search and user-triggered agents: allowed',
            'User-agent: OAI-SearchBot',
            'Allow: /',
            'User-agent: ChatGPT-User',
            'Allow: /',
            'User-agent: Claude-SearchBot',
            'Allow: /',
            'User-agent: Claude-User',
            'Allow: /',
            'User-agent: PerplexityBot',
            'Allow: /',
            'User-agent: Perplexity-User',
            'Allow: /',
            '',
            '# Training crawlers: configurable',
        ];

        foreach (['GPTBot', 'ClaudeBot', 'Google-Extended'] as $bot) {
            $lines[] = 'User-agent: '.$bot;
            $lines[] = $allowTraining ? 'Allow: /' : 'Disallow: /';
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.url('/sitemap.xml');

        if (! app()->environment('production')) {
            return response("User-agent: *\nDisallow: /\n", 200, ['Content-Type' => 'text/plain']);
        }

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain']);
    }

    public function sitemap(): Response
    {
        $urls = [
            ['loc' => route('home'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('about'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('membership.index'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('membership.apply'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('rooms.index'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('events.index'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('dining'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('gallery.index'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('faq'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('journal.index'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('contact'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('legal', 'club-rules'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('legal', 'terms'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('legal', 'privacy'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('legal', 'cookies'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('legal', 'accessibility'), 'lastmod' => now()->toAtomString()],
        ];

        foreach (Hall::query()->active()->get() as $hall) {
            $urls[] = ['loc' => route('rooms.show', $hall->slug), 'lastmod' => $hall->updated_at->toAtomString()];
        }

        foreach (AreaPage::query()->active()->get() as $area) {
            $urls[] = ['loc' => route('rooms.area', $area->slug), 'lastmod' => $area->updated_at->toAtomString()];
        }

        foreach (Section::query()->active()->get() as $section) {
            $urls[] = ['loc' => route('membership.section', $section->slug), 'lastmod' => $section->updated_at->toAtomString()];
        }

        foreach (Event::query()->where('active', true)->get() as $event) {
            $urls[] = ['loc' => route('events.show', $event->slug), 'lastmod' => $event->updated_at->toAtomString()];
        }

        foreach (GalleryAlbum::query()->get() as $album) {
            $urls[] = ['loc' => route('gallery.album', $album->slug), 'lastmod' => $album->updated_at->toAtomString()];
        }

        foreach (JournalPost::query()->published()->get() as $post) {
            $urls[] = ['loc' => route('journal.show', $post->slug), 'lastmod' => $post->updated_at->toAtomString()];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n    <loc>".e($url['loc'])."</loc>\n    <lastmod>{$url['lastmod']}</lastmod>\n  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function llms(): Response
    {
        $body = "# Charlton Exclusive Club\n\n";
        $body .= 'A private members\' club in Charlton, South East London: dining, sports sections, events and meeting rooms.'."\n\n";
        $body .= "## Key pages\n\n";
        foreach ([
            'Home' => route('home'),
            'Membership' => route('membership.index'),
            'Meeting rooms' => route('rooms.index'),
            'Events' => route('events.index'),
            'Dining' => route('dining'),
            'Contact' => route('contact'),
        ] as $label => $url) {
            $body .= "- [{$label}]({$url})\n";
        }

        return response($body, 200, ['Content-Type' => 'text/plain']);
    }
}
