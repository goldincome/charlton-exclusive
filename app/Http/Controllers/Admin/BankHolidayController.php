<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankHoliday;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BankHolidayController extends Controller
{
    public function index(): View
    {
        return view('admin.off-dates.index', [
            'bankHolidays' => BankHoliday::query()->orderBy('date', 'desc')->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'unique:bank_holidays,date'],
            'name' => ['required', 'string', 'max:120'],
        ]);

        BankHoliday::query()->create($data);

        return redirect()->route('admin.off-dates.index')->with('status', 'Off-date / Bank holiday added successfully.');
    }

    public function destroy(BankHoliday $bankHoliday): RedirectResponse
    {
        $bankHoliday->delete();

        return redirect()->route('admin.off-dates.index')->with('status', 'Off-date removed successfully.');
    }
}
