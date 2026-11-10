<?php

use App\Http\Controllers\Account\ApplicationController;
use App\Http\Controllers\Account\CartController;
use App\Http\Controllers\Account\DashboardController;
use App\Http\Controllers\Account\InvoiceController;
use App\Http\Controllers\Account\MembershipUpgradeController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\BookableItemController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\HallController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\FoodOrderController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\DiningController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\GalleryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\JournalController;
use App\Http\Controllers\Public\MediaController;
use App\Http\Controllers\Public\MembershipController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\RoomController;
use App\Http\Controllers\Public\SeoController;
use App\Http\Controllers\Staff\BookingController as StaffBookingController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website (server-rendered, indexable)
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');
Route::get('/about', PageController::class.'@about')->name('about');
Route::get('/faq', PageController::class.'@faq')->name('faq');

Route::get('/membership', [MembershipController::class, 'index'])->name('membership.index');
Route::get('/membership/apply', [MembershipController::class, 'apply'])->name('membership.apply')->middleware('noindex');
Route::get('/membership/sections/{slug}', [MembershipController::class, 'section'])->name('membership.section');

Route::get('/meeting-rooms', [RoomController::class, 'index'])->name('rooms.index');
Route::get('/meeting-rooms/{slug}', [RoomController::class, 'show'])->name('rooms.show');
Route::get('/meeting-rooms/{area}', [RoomController::class, 'area'])->name('rooms.area');

Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');

Route::get('/dining', DiningController::class)->name('dining');

Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/gallery/{slug}', [GalleryController::class, 'album'])->name('gallery.album');

Route::get('/journal', [JournalController::class, 'index'])->name('journal.index');
Route::get('/journal/{slug}', [JournalController::class, 'show'])->name('journal.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:5,1');

Route::get('/club-rules', [PageController::class, 'legal'])->defaults('key', 'club-rules')->name('legal.club-rules');
Route::get('/terms', [PageController::class, 'legal'])->defaults('key', 'terms')->name('legal.terms');
Route::get('/privacy', [PageController::class, 'legal'])->defaults('key', 'privacy')->name('legal.privacy');
Route::get('/cookies', [PageController::class, 'legal'])->defaults('key', 'cookies')->name('legal.cookies');
Route::get('/accessibility', [PageController::class, 'accessibility'])->name('legal.accessibility');
Route::get('/legal/{key}', [PageController::class, 'legal'])->name('legal');

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/llms.txt', [SeoController::class, 'llms'])->name('llms');

// Public media (rooms, events, menu imagery) and visibility-checked gallery.
Route::get('/media/{path}', [MediaController::class, 'publicFile'])->where('path', '.*')->name('media.public');
Route::get('/gallery/photo/{photo}', [MediaController::class, 'gallery'])->name('gallery.photo');
Route::get('/gallery/photo/{photo}/thumb', [MediaController::class, 'thumbnail'])->name('gallery.thumb');

// Booking entry points (auth required to submit, but viewing the form is public).
Route::get('/book/room/{hall}', [BookingController::class, 'createRoom'])->name('book.room');
Route::get('/book/event/{event}', [BookingController::class, 'createEvent'])->name('book.event');
Route::get('/book/table/{table}', [BookingController::class, 'createTable'])->name('book.table');
Route::get('/schedule/slots', [BookingController::class, 'getTimeSlots'])->name('time.slots');

/*
|--------------------------------------------------------------------------
| Authenticated account area
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'noindex'])->prefix('account')->name('account.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    Route::post('/book/room/{hall}', [BookingController::class, 'storeHall'])->name('book.room.store');
    Route::post('/book/event/{event}', [BookingController::class, 'storeEvent'])->name('book.event.store');
    Route::post('/book/table/{table}', [BookingController::class, 'storeTable'])->name('book.table.store');

    Route::get('/food-orders', [FoodOrderController::class, 'index'])->name('food-orders.index');
    Route::get('/food-orders/create', [FoodOrderController::class, 'create'])->name('food-orders.create');
    Route::post('/food-orders', [FoodOrderController::class, 'store'])->name('food-orders.store');
    Route::get('/food-orders/{foodOrder}', [FoodOrderController::class, 'show'])->name('food-orders.show');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('/services', [CartController::class, 'services'])->name('services.index');
    Route::post('/cart/add/{item}', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/remove/{item}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::get('/cart/success', [CartController::class, 'success'])->name('cart.success');

    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');

    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/start', [ApplicationController::class, 'start'])->name('applications.start');
    Route::get('/applications/application-form', [ApplicationController::class, 'applicationForm'])->name('applications.form');
    Route::get('/applications/create', [ApplicationController::class, 'create'])->name('applications.create');
    Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
    Route::get('/applications/{application}/pay', [ApplicationController::class, 'pay'])->name('applications.pay');
    Route::post('/applications/{application}/pay', [ApplicationController::class, 'startPayment'])->name('applications.pay.start');
    Route::get('/applications/{application}/payment-success', [ApplicationController::class, 'paymentSuccess'])->name('applications.payment-success');

    Route::get('/membership/upgrade/{plan?}', [MembershipUpgradeController::class, 'show'])->name('membership.upgrade');
    Route::post('/membership/upgrade', [MembershipUpgradeController::class, 'store'])->name('membership.upgrade.store');
});

/*
|--------------------------------------------------------------------------
| Staff area
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'noindex', 'role:admin|staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/', [StaffBookingController::class, 'index'])->name('dashboard');
    Route::get('/bookings', [StaffBookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings/{booking}/check-in', [StaffBookingController::class, 'checkIn'])->name('bookings.check-in');
    Route::post('/bookings/{booking}/complete', [StaffBookingController::class, 'complete'])->name('bookings.complete');
    Route::post('/bookings/{booking}/no-show', [StaffBookingController::class, 'noShow'])->name('bookings.no-show');
});

/*
|--------------------------------------------------------------------------
| Committee area
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'noindex'])->prefix('committee')->name('committee.')->group(function () {
    Route::get('/applications', [AdminApplicationController::class, 'index'])
        ->middleware('role:admin|committee')->name('applications.index');
    Route::get('/applications/{application}', [AdminApplicationController::class, 'show'])
        ->middleware('role:admin|committee')->name('applications.show');
    Route::post('/applications/{application}/interview', [AdminApplicationController::class, 'scheduleInterview'])
        ->middleware('role:admin|committee')->name('applications.interview');
    Route::post('/interviews/{interview}/recommendation', [AdminApplicationController::class, 'recordRecommendation'])
        ->middleware('role:admin|committee')->name('applications.recommendation');
});

/*
|--------------------------------------------------------------------------
| Admin area
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'noindex', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/approve', [AdminBookingController::class, 'approve'])->name('bookings.approve');
    Route::post('/bookings/{booking}/reject', [AdminBookingController::class, 'reject'])->name('bookings.reject');

    Route::resource('halls', HallController::class);
    Route::match(['post', 'put', 'delete'], '/halls/images/{image}', [HallController::class, 'destroyImage'])->name('halls.images.destroy');
    Route::resource('events', AdminEventController::class);
    Route::post('/events/{event}/cancel', [AdminEventController::class, 'cancel'])->name('events.cancel');

    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}', [AdminApplicationController::class, 'show'])->name('applications.show');
    Route::get('/applications/{application}/documents/{type}', [AdminApplicationController::class, 'downloadDocument'])->name('applications.documents');
    Route::post('/applications/{application}/interview', [AdminApplicationController::class, 'scheduleInterview'])->name('applications.interview');
    Route::post('/interviews/{interview}/recommendation', [AdminApplicationController::class, 'recordRecommendation'])->name('applications.recommendation');
    Route::post('/applications/{application}/verify', [AdminApplicationController::class, 'verifyDocuments'])->name('applications.verify');
    Route::post('/applications/{application}/approve', [AdminApplicationController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{application}/reject', [AdminApplicationController::class, 'reject'])->name('applications.reject');

    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::get('/members/{user}', [MemberController::class, 'show'])->name('members.show');
    Route::post('/members/{user}/cancel', [MemberController::class, 'cancelMembership'])->name('members.cancel');
    Route::post('/members/{user}/suspend', [MemberController::class, 'suspend'])->name('members.suspend');
    Route::post('/members/{user}/reinstate', [MemberController::class, 'reinstate'])->name('members.reinstate');
    Route::post('/members/{user}/financial', [MemberController::class, 'toggleFinancial'])->name('members.financial');

    Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
    Route::post('/menu/categories', [MenuController::class, 'storeCategory'])->name('menu.categories.store');
    Route::post('/menu/items', [MenuController::class, 'storeItem'])->name('menu.items.store');
    Route::patch('/menu/items/{item}', [MenuController::class, 'updateItem'])->name('menu.items.update');
    Route::delete('/menu/items/{item}', [MenuController::class, 'destroyItem'])->name('menu.items.destroy');

    Route::get('/gallery', [AdminGalleryController::class, 'index'])->name('gallery.index');
    Route::post('/gallery/albums', [AdminGalleryController::class, 'storeAlbum'])->name('gallery.albums.store');
    Route::post('/gallery/albums/{album}/upload', [AdminGalleryController::class, 'upload'])->name('gallery.upload');
    Route::patch('/gallery/photos/{photo}/visibility', [AdminGalleryController::class, 'updateVisibility'])->name('gallery.visibility');
    Route::delete('/gallery/photos/{photo}', [AdminGalleryController::class, 'destroyPhoto'])->name('gallery.destroy');

    Route::get('/invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
    Route::post('/invoices', [AdminInvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [AdminInvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/invoices/{invoice}/payment', [AdminInvoiceController::class, 'recordPayment'])->name('invoices.payment');
    Route::post('/payments/{payment}/refund', [AdminInvoiceController::class, 'refund'])->name('payments.refund');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    Route::get('/content/journal', [ContentController::class, 'journal'])->name('content.journal');
    Route::post('/content/journal', [ContentController::class, 'storePost'])->name('content.journal.store');
    Route::get('/content/faqs', [ContentController::class, 'faqs'])->name('content.faqs');
    Route::post('/content/faqs', [ContentController::class, 'storeFaq'])->name('content.faqs.store');
    Route::get('/content/team', [ContentController::class, 'team'])->name('content.team');
    Route::post('/content/team', [ContentController::class, 'storeTeamMember'])->name('content.team.store');
    Route::post('/content/testimonials', [ContentController::class, 'storeTestimonial'])->name('content.testimonials.store');
    Route::get('/content/areas', [ContentController::class, 'areas'])->name('content.areas');
    Route::post('/content/areas', [ContentController::class, 'storeArea'])->name('content.areas.store');

    Route::get('/screen', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/vat', [SettingsController::class, 'updateVat'])->name('settings.vat');
    Route::post('/settings/bank-holidays', [SettingsController::class, 'storeBankHoliday'])->name('settings.bank-holidays');
    Route::post('/settings/sections', [SettingsController::class, 'storeSection'])->name('settings.sections');
    Route::get('/audit', [SettingsController::class, 'auditLogs'])->name('audit.index');
    Route::get('/outbox', [SettingsController::class, 'outbox'])->name('outbox.index');
    Route::get('/contact-messages', [SettingsController::class, 'contactMessages'])->name('contact.index');
    Route::post('/contact-messages/{message}', [SettingsController::class, 'markContactHandled'])->name('contact.handled');
    Route::get('/legal', [SettingsController::class, 'legal'])->name('legal.index');
    Route::post('/legal', [SettingsController::class, 'storeLegal'])->name('legal.store');

    Route::resource('sections', SectionController::class)->except(['show', 'destroy']);
    Route::post('/sections/{section}/pause', [SectionController::class, 'togglePause'])->name('sections.pause');
    Route::resource('bookable-items', BookableItemController::class)->except(['show', 'destroy']);
    Route::resource('plans', PlanController::class)->except(['show', 'destroy']);
    Route::post('/plans/{plan}/sync', [PlanController::class, 'sync'])->name('plans.sync');
});

/*
|--------------------------------------------------------------------------
| Checkout + webhooks
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'noindex'])->group(function () {
    Route::post('/checkout/invoice/{invoice}', [StripeController::class, 'checkout'])->name('checkout.invoice');
    Route::get('/checkout/success', [StripeController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/cancelled', [StripeController::class, 'cancelled'])->name('checkout.cancelled');
});

Route::post('/stripe/webhook', [StripeController::class, 'webhook'])->name('stripe.webhook');

// Two-factor setup target for the staff MFA middleware.
Route::middleware(['auth'])->get('/user/two-factor-authentication', function () {
    return redirect()->route('account.dashboard')->with('status', 'Enable two-factor authentication in your security settings.');
})->name('two-factor.setup');
