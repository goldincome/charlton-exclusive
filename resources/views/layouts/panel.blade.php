<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Dashboard' }} · Charlton Exclusive Club</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background: var(--ivory-50); color: var(--ink-900); font-family: var(--font-body); margin: 0;">

<div style="display: flex; min-height: 100vh;">
    <!-- Sidebar -->
    <aside style="width: 260px; background: var(--navy-900); color: var(--ivory-100); padding: 24px 0; flex-shrink: 0;">
        <div style="padding: 0 24px 24px; border-bottom: 1px solid rgba(201,169,97,.25);">
            <a href="{{ route('home') }}" style="font-family: var(--font-display); font-size: 20px; color: var(--champagne-200); text-decoration: none;">
                Charlton Exclusive
            </a>
        </div>

        <nav style="margin-top: 24px;">
            @if (request()->routeIs('account.*') || (!request()->routeIs('staff.*') && !request()->routeIs('admin.*') && !request()->routeIs('committee.*')))
                <!-- Account nav -->
                <a href="{{ route('account.dashboard') }}" class="panel-nav-link {{ request()->routeIs('account.dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('account.bookings.index') }}" class="panel-nav-link {{ request()->routeIs('account.bookings.*') ? 'active' : '' }}">My bookings</a>
                <a href="{{ route('account.cart.index') }}" class="panel-nav-link {{ request()->routeIs('account.cart.*') ? 'active' : '' }}">Cart</a>
                <a href="{{ route('account.food-orders.index') }}" class="panel-nav-link {{ request()->routeIs('account.food-orders.*') ? 'active' : '' }}">Food orders</a>
                <a href="{{ route('account.invoices.index') }}" class="panel-nav-link {{ request()->routeIs('account.invoices.*') ? 'active' : '' }}">Invoices</a>
                <a href="{{ route('account.applications.index') }}" class="panel-nav-link {{ request()->routeIs('account.applications.*') || request()->routeIs('account.membership.*') ? 'active' : '' }}">My membership</a>
                <div style="border-top: 1px solid rgba(201,169,97,.15); margin: 16px 24px;"></div>
                <a href="{{ route('home') }}" class="panel-nav-link">← Back to public site</a>
            @elseif (request()->routeIs('staff.*'))
                <!-- Staff nav -->
                <a href="{{ route('staff.dashboard') }}" class="panel-nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">Staff dashboard</a>
                <a href="{{ route('staff.bookings.index') }}" class="panel-nav-link {{ request()->routeIs('staff.bookings.*') ? 'active' : '' }}">Today's bookings</a>
                <div style="border-top: 1px solid rgba(201,169,97,.15); margin: 16px 24px;"></div>
                @can('viewAny', \App\Models\Application::class)
                    <a href="{{ route('admin.dashboard') }}" class="panel-nav-link">Admin area</a>
                @endcan
                <a href="{{ route('home') }}" class="panel-nav-link">← Back to public site</a>
            @elseif (request()->routeIs('committee.*'))
                <!-- Committee nav -->
                <a href="{{ route('committee.applications.index') }}" class="panel-nav-link {{ request()->routeIs('committee.applications.*') ? 'active' : '' }}">Applications</a>
                <div style="border-top: 1px solid rgba(201,169,97,.15); margin: 16px 24px;"></div>
                <a href="{{ route('account.dashboard') }}" class="panel-nav-link">My account</a>
                <a href="{{ route('home') }}" class="panel-nav-link">← Back to public site</a>
            @elseif (request()->routeIs('admin.*'))
                <!-- Admin nav -->
                <a href="{{ route('admin.dashboard') }}" class="panel-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Admin dashboard</a>
                <a href="{{ route('admin.bookings.index') }}" class="panel-nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">Bookings</a>
                <a href="{{ route('admin.applications.index') }}" class="panel-nav-link {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}">Applications</a>
                <a href="{{ route('admin.plans.index') }}" class="panel-nav-link {{ request()->routeIs('admin.plans.*') ? 'active' : '' }}">Plans</a>
                <a href="{{ route('admin.members.index') }}" class="panel-nav-link {{ request()->routeIs('admin.members.*') ? 'active' : '' }}">Members</a>
                <a href="{{ route('admin.halls.index') }}" class="panel-nav-link {{ request()->routeIs('admin.halls.*') ? 'active' : '' }}">Meeting rooms</a>
                <a href="{{ route('admin.events.index') }}" class="panel-nav-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">Events</a>
                <a href="{{ route('admin.sections.index') }}" class="panel-nav-link {{ request()->routeIs('admin.sections.*') ? 'active' : '' }}">Sections</a>
                <a href="{{ route('admin.menu.index') }}" class="panel-nav-link {{ request()->routeIs('admin.menu.*') ? 'active' : '' }}">Menu</a>
                <a href="{{ route('admin.gallery.index') }}" class="panel-nav-link {{ request()->routeIs('admin.gallery.*') ? 'active' : '' }}">Gallery</a>
                <a href="{{ route('admin.invoices.index') }}" class="panel-nav-link {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">Invoices</a>
                <div style="border-top: 1px solid rgba(201,169,97,.15); margin: 16px 24px;"></div>
                <a href="{{ route('admin.content.journal') }}" class="panel-nav-link {{ request()->routeIs('admin.content.*') ? 'active' : '' }}">Content</a>
                <a href="{{ route('admin.settings.index') }}" class="panel-nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">Settings</a>
                <a href="{{ route('admin.operating-hours.index') }}" class="panel-nav-link {{ request()->routeIs('admin.operating-hours.*') ? 'active' : '' }}">Operating hours</a>
                <a href="{{ route('admin.off-dates.index') }}" class="panel-nav-link {{ request()->routeIs('admin.off-dates.*') ? 'active' : '' }}">Off-dates</a>
                <a href="{{ route('admin.audit.index') }}" class="panel-nav-link {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">Audit log</a>
                <a href="{{ route('admin.reports.index') }}" class="panel-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">Reports</a>
                <div style="border-top: 1px solid rgba(201,169,97,.15); margin: 16px 24px;"></div>
                <a href="{{ route('account.dashboard') }}" class="panel-nav-link">My account</a>
                <a href="{{ route('home') }}" class="panel-nav-link">← Back to public site</a>
            @endif
        </nav>

        <div style="margin-top: auto; padding: 24px; border-top: 1px solid rgba(201,169,97,.15);">
            <p style="font-size: 13px; color: var(--mist-300); margin: 0 0 8px;">{{ auth()->user()?->name ?? 'Guest' }}</p>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" style="background: none; border: none; color: var(--gold-500); font-size: 14px; cursor: pointer; padding: 0;">Sign out</button>
            </form>
        </div>
    </aside>

    <!-- Main content -->
    <main style="flex: 1; padding: 32px 48px;">
        @if (session('status'))
            <div role="alert" style="background: #E8F5E9; color: #2E7D32; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #4CAF50;">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>
</div>

</body>
</html>