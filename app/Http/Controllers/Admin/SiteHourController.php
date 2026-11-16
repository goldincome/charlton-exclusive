<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteHour;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiteHourController extends Controller
{
    public function index(): View
    {
        $hours = SiteHour::query()->orderBy('weekday')->get();
        for ($i = 0; $i <= 6; $i++) {
            if (! $hours->contains('weekday', $i)) {
                SiteHour::query()->create([
                    'weekday' => $i,
                    'opens_at' => '08:00:00',
                    'closes_at' => '23:00:00',
                    'is_open' => true,
                ]);
            }
        }
        $hours = SiteHour::query()->orderBy('weekday')->get();

        return view('admin.operating-hours.index', compact('hours'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'hours' => ['required', 'array'],
            'hours.*.opens_at' => ['nullable', 'date_format:H:i'],
            'hours.*.closes_at' => ['nullable', 'date_format:H:i'],
            'hours.*.is_open' => ['boolean'],
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->input('hours', []) as $weekday => $info) {
                SiteHour::query()->updateOrCreate(
                    ['weekday' => (int) $weekday],
                    [
                        'opens_at' => $info['opens_at'] ?? '08:00:00',
                        'closes_at' => $info['closes_at'] ?? '23:00:00',
                        'is_open' => isset($info['is_open']),
                    ]
                );
            }
        });

        return back()->with('status', 'Operating hours updated successfully.');
    }
}
