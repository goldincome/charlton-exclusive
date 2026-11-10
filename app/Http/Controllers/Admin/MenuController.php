<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemAllergen;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(): View
    {
        return view('admin.menu.index', [
            'categories' => MenuCategory::query()->with(['items.allergenFlags'])->orderBy('order')->get(),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        MenuCategory::query()->create($data + ['active' => true]);

        return back()->with('status', 'Menu category created.');
    }

    public function storeItem(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'menu_category_id' => ['required', 'exists:menu_categories,id'],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'integer', 'min:0'],
            'discount_exempt' => ['boolean'],
            'allergens' => ['nullable', 'array'],
            'allergens.*' => ['in:'.implode(',', MenuItem::ALLERGENS)],
        ]);

        $item = MenuItem::query()->create([
            'menu_category_id' => $data['menu_category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'discount_exempt' => $request->boolean('discount_exempt'),
            'active' => true,
        ]);

        foreach ($request->input('allergens', []) as $allergen) {
            MenuItemAllergen::query()->create(['menu_item_id' => $item->id, 'allergen' => $allergen]);
        }

        return back()->with('status', 'Menu item created.');
    }

    public function updateItem(Request $request, MenuItem $item): RedirectResponse
    {
        $item->update($request->validate([
            'name' => ['required', 'string', 'max:160'],
            'price' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'active' => ['boolean'],
        ]) + ['active' => $request->boolean('active')]);

        return back()->with('status', 'Menu item updated.');
    }

    public function destroyItem(MenuItem $item): RedirectResponse
    {
        $item->delete();

        return back()->with('status', 'Menu item removed.');
    }
}
