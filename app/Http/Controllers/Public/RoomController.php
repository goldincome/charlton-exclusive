<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AreaPage;
use App\Models\Hall;
use Illuminate\Contracts\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        return view('public.rooms.index', [
            'halls' => Hall::query()->active()->with(['rates', 'images', 'features'])->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $hall = Hall::query()->active()->where('slug', $slug)->with(['rates', 'images', 'features', 'hours'])->firstOrFail();

        $memberRate = null;
        if (auth()->check() && auth()->user()->canGetMemberRates()) {
            $memberRate = optional($hall->rates->first())->memberPricePerHour();
        }

        return view('public.rooms.show', [
            'hall' => $hall,
            'others' => Hall::query()->active()->whereKeyNot($hall->id)->take(3)->get(),
            'memberRate' => $memberRate,
        ]);
    }

    public function area(string $area): View
    {
        $page = AreaPage::query()->active()->where('slug', $area)->first();

        if ($page) {
            return view('public.rooms.area', ['page' => $page]);
        }

        return $this->show($area);
    }
}
