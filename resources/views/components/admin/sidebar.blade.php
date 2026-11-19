@props(['active' => ''])

<aside class="admin-sidebar">

    <a href="{{ route('admin.dashboard') }}" class="admin-brand">
        <span class="brand-mark" aria-hidden="true">
            <svg viewBox="0 0 40 40" fill="none">
                <rect x="2" y="2" width="36" height="36" rx="10" fill="#D6B574"/>
                <path d="M11 27V13h5l4 6 4-6h5v14h-5v-7l-4 5-4-5v7h-5Z" fill="#101827"/>
            </svg>
        </span>
        <span class="brand-name">WHOLESALE<span>.</span></span>
    </a>

    <div class="sidebar-label">WORKSPACE</div>

    <nav class="sidebar-navigation">

        <a href="{{ route('admin.dashboard') }}"
           class="sidebar-link {{ $active === 'dashboard' ? 'active' : '' }}">
            <span class="sidebar-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="3" width="8" height="8" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="5" rx="1.5"/>
                    <rect x="14" y="11" width="7" height="10" rx="1.5"/>
                    <rect x="3" y="14" width="8" height="7" rx="1.5"/>
                </svg>
            </span>
            <span class="sidebar-text">Dashboard</span>
        </a>

        <a href="{{ route('admin.vendors.index') }}"
           class="sidebar-link {{ $active === 'vendors' ? 'active' : '' }}">
            <span class="sidebar-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M3 10h18l-1.5-6h-15L3 10Z"/>
                    <path d="M5 10v10h14V10M9 20v-6h6v6"/>
                    <path d="M3 10c0 2 3 2.5 4 0 1 2.5 4 2 5 0 1 2 4 2.5 5 0 1 2 4 2 4 0"/>
                </svg>
            </span>
            <span class="sidebar-text">Vendor Management</span>
        </a>

        <a href="{{ route('admin.products.index') }}"
           class="sidebar-link {{ $active === 'products' ? 'active' : '' }}">
            <span class="sidebar-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="m12 3 9 5-9 5-9-5 9-5Z"/>
                    <path d="m3 12 9 5 9-5M3 16l9 5 9-5"/>
                </svg>
            </span>
            <span class="sidebar-text">Product Management</span>
        </a>

        <a href="{{ route('admin.marketplace.index') }}"
           class="sidebar-link {{ $active === 'marketplace' ? 'active' : '' }}">
            <span class="sidebar-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M3 9h18l-1.5-5h-15L3 9Z"/>
                    <path d="M5 9v11h14V9M9 20v-6h6v6"/>
                    <path d="M3 9c0 2 3 2.5 4 0 1 2.5 4 2 5 0 1 2 4 2.5 5 0 1 2 4 2 4 0"/>
                    <path d="M8 4V2m8 2V2"/>
                </svg>
            </span>
            <span class="sidebar-text">Vendor Marketplace</span>
        </a>

        <a href="{{ route('admin.purchases.index') }}"
           class="sidebar-link {{ $active === 'purchases' ? 'active' : '' }}">
            <span class="sidebar-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M4 4h2l2 11h10l2-8H7"/>
                    <circle cx="10" cy="19" r="1.5"/>
                    <circle cx="17" cy="19" r="1.5"/>
                    <path d="M10 8h7m-3.5-3.5v7"/>
                </svg>
            </span>
            <span class="sidebar-text">Vendor Purchases</span>
        </a>

        <a href="{{ route('admin.inventory.index') }}"
           class="sidebar-link {{ $active === 'inventory' ? 'active' : '' }}">
            <span class="sidebar-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="m12 3 9 5v8l-9 5-9-5V8l9-5Z"/>
                    <path d="m3 8 9 5 9-5M12 13v8M7.5 5.5l9 5"/>
                </svg>
            </span>
            <span class="sidebar-text">Inventory</span>
        </a>

        <a href="{{ route('admin.catalog.index') }}"
           class="sidebar-link {{ $active === 'catalog' ? 'active' : '' }}">
            <span class="sidebar-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M4 4h16v16H4z"/>
                    <path d="M8 8h8M8 12h8M8 16h5"/>
                    <path d="M7 4V2m10 2V2"/>
                </svg>
            </span>
            <span class="sidebar-text">Customer Catalog</span>
        </a>

        <a href="{{ route('admin.categories.index') }}"
           class="sidebar-link {{ $active === 'categories' ? 'active' : '' }}">
            <span class="sidebar-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
            </span>
            <span class="sidebar-text">Category Management</span>
        </a>

    </nav>

    <div class="sidebar-label account-label">ACCOUNT</div>

    <form action="{{ route('admin.logout') }}" method="POST" class="sidebar-logout-form">
        @csrf
        <button type="submit" class="sidebar-link sidebar-logout">
            <span class="sidebar-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5"/>
                    <path d="M14 16l5-4-5-4m5 4H9"/>
                </svg>
            </span>
            <span class="sidebar-text">Sign out</span>
        </button>
    </form>

    <div class="sidebar-bottom">
        <div class="admin-profile">
            <div class="admin-avatar">
                <svg viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="8" r="3.5"/>
                    <path d="M5 20c.5-3.5 3.2-5.5 7-5.5s6.5 2 7 5.5"/>
                </svg>
            </div>

            <div class="admin-profile-details">
                <div class="admin-profile-name">
                    {{ auth()->user()->name ?? 'Administrator' }}
                </div>
                <div class="admin-profile-role">System Administrator</div>
            </div>
        </div>
    </div>

</aside>

<style>
.admin-sidebar {
    width: 258px;
    position: fixed;
    inset: 0 auto 0 0;
    z-index: 1030;
    display: flex;
    flex-direction: column;
    padding: 25px 16px 18px;
    overflow-y: auto;
    background: #080e19;
    border-right: 1px solid #26364a;
    color: #f1f5f9;
}

.admin-brand {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 5px 10px 25px;
    color: #f1f5f9;
    text-decoration: none;
}

.admin-brand:hover {
    color: #fff;
}

.brand-mark,
.brand-mark svg {
    display: block;
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
}

.brand-name {
    font-family: 'Manrope', sans-serif;
    font-size: 19px;
    font-weight: 800;
    letter-spacing: -.7px;
}

.brand-name > span {
    color: #d6b574;
}

.sidebar-label {
    margin: 23px 12px 10px;
    color: #8190a5;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.4px;
}

.sidebar-navigation {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.sidebar-link {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    min-height: 44px;
    padding: 11px 13px;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 8px;
    color: #c2ccda;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    text-align: left;
    text-decoration: none;
    cursor: pointer;
    transition: background .15s ease, color .15s ease;
}

.sidebar-link:hover {
    background: #172438;
    color: #fff;
}

.sidebar-link.active {
    background: #202b3b;
    border-color: #3b4656;
    box-shadow: inset 3px 0 #d6b574;
    color: #e5c98f;
}

.sidebar-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    flex: 0 0 22px;
    color: #d6b574;
}

.sidebar-icon svg {
    width: 19px;
    height: 19px;
    stroke: currentColor;
    stroke-width: 1.7;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.account-label {
    margin-top: 30px;
}

.sidebar-logout-form {
    margin: 0;
}

.sidebar-logout {
    border: 0;
}

.sidebar-logout:hover {
    background: #482727;
    color: #ffb5b5;
}

.sidebar-bottom {
    margin-top: auto;
    padding: 18px 10px 4px;
    border-top: 1px solid #26364a;
}

.admin-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.admin-avatar {
    display: grid;
    place-items: center;
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    background: #26364a;
    border: 1px solid #40536b;
    border-radius: 9px;
    color: #e5c98f;
}

.admin-avatar svg {
    width: 21px;
    height: 21px;
    stroke: currentColor;
    stroke-width: 1.7;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.admin-profile-details {
    min-width: 0;
}

.admin-profile-name {
    overflow: hidden;
    color: #f1f5f9;
    font-size: 12px;
    font-weight: 750;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.admin-profile-role {
    margin-top: 3px;
    color: #aab7c8;
    font-size: 10px;
}

@media (max-width: 1050px) {
    .admin-sidebar {
        width: 76px;
        padding: 22px 9px;
    }

    .admin-brand {
        justify-content: center;
        padding: 5px 0 25px;
    }

    .brand-name,
    .sidebar-label,
    .sidebar-text,
    .admin-profile-details {
        display: none;
    }

    .sidebar-link {
        justify-content: center;
        padding: 12px 5px;
    }

    .sidebar-icon {
        width: 22px;
        flex-basis: 22px;
    }

    .sidebar-bottom {
        padding-left: 0;
        padding-right: 0;
    }

    .admin-profile {
        justify-content: center;
    }
}
</style>