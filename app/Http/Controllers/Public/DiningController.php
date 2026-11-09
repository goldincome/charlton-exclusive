<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use Illuminate\Contracts\View\View;

class DiningController extends Controller
{
    public function __invoke(): View
    {
        return view('public.dining', [
            'categories' => MenuCategory::query()->where('active', true)
                ->with(['items' => fn ($q) => $q->where('active', true)->with('allergenFlags')])
                ->orderBy('order')
                ->get(),
            'allergens' => \App\Models\MenuItem::ALLERGENS,
        ]);
    }
}