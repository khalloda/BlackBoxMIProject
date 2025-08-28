<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand" href="/dashboard">
            <i class="fas fa-cogs me-2"></i>
            <?= __('app.name') ?>
        </a>

        <!-- Mobile toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link <?= $_SERVER['REQUEST_URI'] === '/dashboard' ? 'active' : '' ?>" href="/dashboard">
                        <i class="fas fa-tachometer-alt me-1"></i>
                        <?= __('dashboard.title') ?>
                    </a>
                </li>

                <!-- Masters Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= strpos($_SERVER['REQUEST_URI'], '/clients') === 0 || strpos($_SERVER['REQUEST_URI'], '/suppliers') === 0 || strpos($_SERVER['REQUEST_URI'], '/products') === 0 || strpos($_SERVER['REQUEST_URI'], '/warehouses') === 0 ? 'active' : '' ?>" 
                       href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-database me-1"></i>
                        <?= __('nav.masters') ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/clients">
                            <i class="fas fa-users me-2"></i><?= __('clients.title') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/suppliers">
                            <i class="fas fa-truck me-2"></i><?= __('suppliers.title') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/products">
                            <i class="fas fa-box me-2"></i><?= __('products.title') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/warehouses">
                            <i class="fas fa-warehouse me-2"></i><?= __('warehouses.title') ?>
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/dropdowns">
                            <i class="fas fa-list me-2"></i><?= __('dropdowns.title') ?>
                        </a></li>
                    </ul>
                </li>

                <!-- Sales Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= strpos($_SERVER['REQUEST_URI'], '/quotes') === 0 || strpos($_SERVER['REQUEST_URI'], '/sales-orders') === 0 || strpos($_SERVER['REQUEST_URI'], '/invoices') === 0 || strpos($_SERVER['REQUEST_URI'], '/payments') === 0 ? 'active' : '' ?>" 
                       href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-shopping-cart me-1"></i>
                        <?= __('nav.sales') ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/quotes">
                            <i class="fas fa-file-alt me-2"></i><?= __('quotes.title') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/sales-orders">
                            <i class="fas fa-shopping-cart me-2"></i><?= __('sales_orders.title') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/invoices">
                            <i class="fas fa-file-invoice me-2"></i><?= __('invoices.title') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/payments">
                            <i class="fas fa-credit-card me-2"></i><?= __('payments.title') ?>
                        </a></li>
                    </ul>
                </li>

                <!-- Inventory Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= strpos($_SERVER['REQUEST_URI'], '/stock') === 0 || strpos($_SERVER['REQUEST_URI'], '/purchase-orders') === 0 || strpos($_SERVER['REQUEST_URI'], '/grn') === 0 ? 'active' : '' ?>" 
                       href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-boxes me-1"></i>
                        <?= __('nav.inventory') ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/stock">
                            <i class="fas fa-cubes me-2"></i><?= __('stock.title') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/purchase-orders">
                            <i class="fas fa-shopping-bag me-2"></i><?= __('purchase_orders.title') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/grn">
                            <i class="fas fa-clipboard-check me-2"></i><?= __('grn.title') ?>
                        </a></li>
                    </ul>
                </li>

                <!-- Reports -->
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/reports') === 0 ? 'active' : '' ?>" href="/reports">
                        <i class="fas fa-chart-bar me-1"></i>
                        <?= __('reports.title') ?>
                    </a>
                </li>
            </ul>

            <!-- Right side menu -->
            <ul class="navbar-nav">
                <!-- Language Switcher -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-globe me-1"></i>
                        <?= app('language')->getCurrentLanguage() === 'ar' ? 'العربية' : 'English' ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/language/en">
                            <i class="fas fa-flag-usa me-2"></i>English
                        </a></li>
                        <li><a class="dropdown-item" href="/language/ar">
                            <i class="fas fa-flag me-2"></i>العربية
                        </a></li>
                    </ul>
                </li>

                <!-- User Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?= htmlspecialchars(Auth::user()['full_name'] ?? 'User') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/profile">
                            <i class="fas fa-user me-2"></i><?= __('nav.profile') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/change-password">
                            <i class="fas fa-key me-2"></i><?= __('nav.change_password') ?>
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/settings">
                            <i class="fas fa-cog me-2"></i><?= __('nav.settings') ?>
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout">
                            <i class="fas fa-sign-out-alt me-2"></i><?= __('nav.logout') ?>
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
