<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AreaPage;
use App\Models\AuthorProfile;
use App\Models\FaqItem;
use App\Models\JournalPost;
use App\Models\TeamProfile;
use App\Models\Testimonial;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    public function journal(): View
    {
        return view('admin.content.journal', [
            'posts' => JournalPost::query()->with('author')->latest()->paginate(20),
            'authors' => AuthorProfile::query()->orderBy('name')->get(),
        ]);
    }

    public function storePost(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['required', 'string'],
            'author_profile_id' => ['nullable', 'exists:author_profiles,id'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:190'],
            'published_at' => ['nullable', 'date'],
        ]);

        JournalPost::query()->create($data + ['slug' => Str::slug($data['title'])]);

        return back()->with('status', 'Journal post saved.');
    }

    public function faqs(): View
    {
        return view('admin.content.faqs', ['faqs' => FaqItem::query()->orderBy('order')->get()]);
    }

    public function storeFaq(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:190'],
            'answer' => ['required', 'string'],
            'group' => ['nullable', 'string', 'max:80'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        FaqItem::query()->create($data + ['active' => true]);

        return back()->with('status', 'FAQ added.');
    }

    public function team(): View
    {
        return view('admin.content.team', [
            'team' => TeamProfile::query()->orderBy('order')->get(),
            'testimonials' => Testimonial::query()->orderBy('order')->get(),
        ]);
    }

    public function storeTeamMember(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'role' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string'],
            'consent_given' => ['boolean'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        TeamProfile::query()->create($data + ['consent_given' => $request->boolean('consent_given')]);

        return back()->with('status', 'Team member added.');
    }

    public function storeTestimonial(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'quote' => ['required', 'string', 'max:600'],
            'author_name' => ['required', 'string', 'max:120'],
            'author_context' => ['nullable', 'string', 'max:120'],
            'consent_given' => ['boolean'],
        ]);

        Testimonial::query()->create($data + [
            'consent_given' => $request->boolean('consent_given'),
            'active' => true,
        ]);

        return back()->with('status', 'Testimonial added.');
    }

    public function areas(): View
    {
        return view('admin.content.areas', ['areas' => AreaPage::query()->orderBy('name')->get()]);
    }

    public function storeArea(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:190'],
            'active' => ['boolean'],
        ]);

        AreaPage::query()->create($data + [
            'slug' => Str::slug($data['name']),
            'active' => $request->boolean('active'),
        ]);

        return back()->with('status', 'Area page saved.');
    }
}
