<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookableItem;
use App\Models\Section;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookableItemController extends Controller
{
    public function index(): View
    {
        return view('admin.bookable-items.index', [
            'items' => BookableItem::query()->with('section')->orderBy('section_id')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.bookable-items.form', ['item' => new BookableItem(['is_active' => true, 'pricing_unit' => 'flat']), 'sections' => Section::query()->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        BookableItem::query()->create($this->validated($request));
        return redirect()->route('admin.bookable-items.index')->with('status', 'Bookable item created.');
    }

    public function edit(BookableItem $bookableItem): View
    {
        return view('admin.bookable-items.form', ['item' => $bookableItem, 'sections' => Section::query()->orderBy('name')->get()]);
    }

    public function update(Request $request, BookableItem $bookableItem): RedirectResponse
    {
        $bookableItem->update($this->validated($request));
        return back()->with('status', 'Bookable item updated.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'], // pounds in form
            'pricing_unit' => ['required', 'in:hour,day,flat'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['price'] = (int) round(((float) $data['price']) * 100);
        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }
}
