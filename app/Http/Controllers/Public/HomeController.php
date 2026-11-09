<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\GalleryPhoto;
use App\Models\Hall;
use App\Models\MembershipCategory;
use App\Models\Section;
use App\Models\Testimonial;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('public.home', [
            'categories' => MembershipCategory::query()->active()->orderBy('entrance_fee')->get(),
            'sections' => Section::query()->active()->orderBy('name')->get(),
            'halls' => Hall::query()->active()->with(['rates', 'images'])->take(2)->get(),
            'events' => Event::query()->active()->upcoming()->with('albums')->take(3)->get(),
            'photos' => GalleryPhoto::query()->public()->latest()->take(6)->get(),
            'testimonials' => Testimonial::query()->visible()->take(1)->get(),
        ]);
    }
}