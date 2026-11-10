<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\JournalPost;
use Illuminate\Contracts\View\View;

class JournalController extends Controller
{
    public function index(): View
    {
        return view('public.journal.index', [
            'posts' => JournalPost::query()->published()->with('author')->latest('published_at')->paginate(9),
        ]);
    }

    public function show(string $slug): View
    {
        $post = JournalPost::query()->published()->where('slug', $slug)->with('author')->firstOrFail();

        return view('public.journal.show', [
            'post' => $post,
            'more' => JournalPost::query()->published()->whereKeyNot($post->id)->latest('published_at')->take(3)->get(),
        ]);
    }
}
