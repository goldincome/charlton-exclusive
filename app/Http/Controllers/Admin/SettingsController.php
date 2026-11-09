<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use App\Models\AuditLog;
use App\Models\BankHoliday;
use App\Models\ContactMessage;
use App\Models\LegalDocument;
use App\Models\NotificationOutbox;
use App\Models\Section;
use App\Models\VatRate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'config' => AppConfig::query()->orderBy('key')->get(),
            'vatRates' => VatRate::query()->orderBy('product_type')->get(),
            'bankHolidays' => BankHoliday::query()->orderBy('date')->get(),
            'sections' => Section::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'config' => ['nullable', 'array'],
            'config.*' => ['nullable', 'string', 'max:2000'],
        ]);

        foreach ($data['config'] ?? [] as $key => $value) {
            AppConfig::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->audit($request, 'config.updated', ['keys' => array_keys($data['config'] ?? [])]);

        return back()->with('status', 'Configuration saved.');
    }

    public function updateVat(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rates' => ['required', 'array'],
            'rates.*' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        foreach ($data['rates'] as $type => $rate) {
            VatRate::query()->updateOrCreate(['product_type' => $type], ['rate' => $rate]);
        }

        return back()->with('status', 'VAT rates updated.');
    }

    public function storeBankHoliday(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'unique:bank_holidays,date'],
            'name' => ['required', 'string', 'max:120'],
        ]);

        BankHoliday::query()->create($data);

        return back()->with('status', 'Bank holiday added.');
    }

    public function storeSection(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'fee' => ['required', 'integer', 'min:0'],
        ]);

        Section::query()->create($data + [
            'slug' => \Illuminate\Support\Str::slug($data['name']),
            'recurring_annually' => true,
            'active' => true,
        ]);

        return back()->with('status', 'Section created.');
    }

    public function auditLogs(): View
    {
        return view('admin.audit.index', [
            'logs' => AuditLog::query()->with('user')->latest()->paginate(30),
        ]);
    }

    public function outbox(): View
    {
        return view('admin.outbox.index', [
            'messages' => NotificationOutbox::query()->latest()->paginate(30),
        ]);
    }

    public function contactMessages(): View
    {
        return view('admin.contact.index', [
            'messages' => ContactMessage::query()->latest()->paginate(20),
        ]);
    }

    public function markContactHandled(ContactMessage $message): RedirectResponse
    {
        $message->update(['handled' => true]);

        return back()->with('status', 'Marked as handled.');
    }

    public function legal(): View
    {
        return view('admin.legal.index', [
            'documents' => LegalDocument::query()->orderBy('key')->get(),
        ]);
    }

    public function storeLegal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:60'],
            'title' => ['required', 'string', 'max:160'],
            'version' => ['required', 'string', 'max:20'],
            'body' => ['required', 'string'],
            'effective_at' => ['required', 'date'],
        ]);

        LegalDocument::query()->create($data + ['active' => true]);

        return back()->with('status', 'Legal document version published.');
    }

    private function audit(Request $request, string $action, array $new = []): void
    {
        AuditLog::query()->create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'new_values' => $new,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}