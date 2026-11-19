<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use App\Models\HallFeature;
use App\Models\HallImage;
use App\Models\Redirect;
use App\Models\RoomRate;
use App\Services\StorageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HallController extends Controller
{
    public function __construct(private StorageService $storage) {}

    public function index(): View
    {
        return view('admin.halls.index', [
            'halls' => Hall::query()->withCount('rates')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.halls.form', ['hall' => new Hall]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $hall = DB::transaction(function () use ($request, $data) {
            $hall = Hall::query()->create($data);
            $this->syncExtras($request, $hall);

            return $hall;
        });

        return redirect()->route('admin.halls.edit', $hall)->with('status', 'Room created.');
    }

    public function edit(Hall $hall): View
    {
        return view('admin.halls.form', [
            'hall' => $hall->load(['rates', 'features', 'images']),
        ]);
    }

    public function update(Request $request, Hall $hall): RedirectResponse
    {
        $data = $this->validated($request, $hall);
        $oldSlug = $hall->slug;

        DB::transaction(function () use ($request, $hall, $data, $oldSlug) {
            $hall->update($data);

            if ($oldSlug !== $hall->slug) {
                Redirect::query()->updateOrCreate(
                    ['from_path' => '/meeting-rooms/'.$oldSlug.'/'],
                    ['to_path' => '/meeting-rooms/'.$hall->slug.'/', 'status_code' => 301]
                );
            }

            $this->syncExtras($request, $hall);
        });

        return back()->with('status', 'Room updated.');
    }

    public function destroy(Hall $hall): RedirectResponse
    {
        $hall->delete();

        return redirect()->route('admin.halls.index')->with('status', 'Room deleted.');
    }

    public function destroyImage(HallImage $image): RedirectResponse
    {
        $this->storage->delete($image->path);
        $image->delete();

        return back()->with('status', 'Room image deleted.');
    }

    private function validated(Request $request, ?Hall $hall = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('halls', 'slug')->ignore($hall?->id)],
            'description' => ['nullable', 'string'],
            'capacity' => ['required', 'integer', 'min:1'],
            'capacity_seated' => ['nullable', 'integer', 'min:0'],
            'area' => ['nullable', 'string', 'max:80'],
            'buffer_minutes' => ['required', 'integer', 'min:0', 'max:180'],
            'min_hours' => ['required', 'integer', 'min:1', 'max:12'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:190'],
            'active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:10240'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:10240'],
            'ratings' => ['nullable', 'array'],
            'ratings.*.standard_price_per_hour' => ['nullable', 'numeric', 'min:0'],
            'features' => ['nullable', 'string'],
        ]);

        $data['slug'] = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $data['active'] = $request->boolean('active');

        unset($data['image'], $data['images'], $data['ratings'], $data['features']);

        return $data;
    }

    private function syncExtras(Request $request, Hall $hall): void
    {
        if ($request->hasFile('image')) {
            $stored = $this->storage->storeImage($request->file('image'), 'public/halls');
            $hall->images()->create(['path' => $stored['path'], 'alt_text' => $hall->name]);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $stored = $this->storage->storeImage($file, 'public/halls');
                $hall->images()->create(['path' => $stored['path'], 'alt_text' => $hall->name]);
            }
        }

        foreach ($request->input('ratings', []) as $dayType => $rate) {
            if (! isset($rate['standard_price_per_hour']) || $rate['standard_price_per_hour'] === '') {
                $hall->rates()->where('day_type', $dayType)->delete();

                continue;
            }

            RoomRate::query()->updateOrCreate(
                ['hall_id' => $hall->id, 'day_type' => $dayType],
                [
                    'standard_price_per_hour' => (int) round(((float) $rate['standard_price_per_hour']) * 100),
                ]
            );
        }

        if ($request->filled('features')) {
            $hall->features()->delete();
            foreach (array_filter(array_map('trim', explode("\n", $request->string('features')))) as $feature) {
                HallFeature::query()->create(['hall_id' => $hall->id, 'name' => $feature]);
            }
        }
    }
}
