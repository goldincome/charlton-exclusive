<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\FaqItem;
use App\Models\LegalDocument;
use App\Models\TeamProfile;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('public.about', [
            'team' => TeamProfile::query()->where('consent_given', true)->orderBy('order')->get(),
        ]);
    }

    public function faq(): View
    {
        return view('public.faq', [
            'groups' => FaqItem::query()->active()->get()->groupBy(fn ($item) => $item->group ?: 'General'),
        ]);
    }

    public function accessibility(): View
    {
        return view('public.legal', [
            'document' => LegalDocument::query()->where('key', 'accessibility')->where('active', true)->latest('effective_at')->first(),
            'fallbackTitle' => 'Accessibility',
            'fallbackKey' => 'accessibility',
        ]);
    }

    public function legal(string $key): View
    {
        $titles = [
            'club-rules' => 'Club Rules',
            'terms' => 'Terms and Conditions',
            'privacy' => 'Privacy Policy',
            'cookies' => 'Cookie Policy',
            'accessibility' => 'Accessibility Statement',
        ];

        abort_unless(array_key_exists($key, $titles), 404);

        return view('public.legal', [
            'document' => LegalDocument::query()->where('key', $key)->where('active', true)->latest('effective_at')->first(),
            'fallbackTitle' => $titles[$key],
            'fallbackKey' => $key,
        ]);
    }
}
