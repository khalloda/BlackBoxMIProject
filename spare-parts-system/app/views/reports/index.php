<div class="container-fluid py-4">
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        <?= __('reports.title') ?>
                    </h1>
                    <p class="text-muted mb-0"><?= __('reports.description') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Categories -->
    <div class="row">
        <!-- Sales Reports -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-chart-line fa-3x text-success"></i>
                    </div>
                    <h5 class="card-title"><?= __('reports.sales_reports') ?></h5>
                    <p class="card-text text-muted"><?= __('reports.sales_description') ?></p>
                    <a href="/reports/sales" class="btn btn-success">
                        <i class="fas fa-eye me-2"></i>
                        <?= __('reports.view_reports') ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Inventory Reports -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-boxes fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title"><?= __('reports.inventory_reports') ?></h5>
                    <p class="card-text text-muted"><?= __('reports.inventory_description') ?></p>
                    <a href="/reports/inventory" class="btn btn-primary">
                        <i class="fas fa-eye me-2"></i>
                        <?= __('reports.view_reports') ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Financial Reports -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-dollar-sign fa-3x text-warning"></i>
                    </div>
                    <h5 class="card-title"><?= __('reports.financial_reports') ?></h5>
                    <p class="card-text text-muted"><?= __('reports.financial_description') ?></p>
                    <a href="/reports/financial" class="btn btn-warning">
                        <i class="fas fa-eye me-2"></i>
                        <?= __('reports.view_reports') ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Client Reports -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-info"></i>
                    </div>
                    <h5 class="card-title"><?= __('reports.client_reports') ?></h5>
                    <p class="card-text text-muted"><?= __('reports.client_description') ?></p>
                    <a href="/reports/clients" class="btn btn-info">
                        <i class="fas fa-eye me-2"></i>
                        <?= __('reports.view_reports') ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Product Reports -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-box fa-3x text-secondary"></i>
                    </div>
                    <h5 class="card-title"><?= __('reports.product_reports') ?></h5>
                    <p class="card-text text-muted"><?= __('reports.product_description') ?></p>
                    <a href="/reports/products" class="btn btn-secondary">
                        <i class="fas fa-eye me-2"></i>
                        <?= __('reports.view_reports') ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Custom Reports -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-cog fa-3x text-dark"></i>
                    </div>
                    <h5 class="card-title"><?= __('reports.custom_reports') ?></h5>
                    <p class="card-text text-muted"><?= __('reports.custom_description') ?></p>
                    <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#customReportModal">
                        <i class="fas fa-plus me-2"></i>
                        <?= __('reports.create_custom') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-5">
        <div class="col-12">
            <h4 class="mb-3"><?= __('reports.quick_stats') ?></h4>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title"><?= __('reports.total_sales') ?></h6>
                            <h3 class="mb-0">AED 0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title"><?= __('reports.total_products') ?></h6>
                            <h3 class="mb-0">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-box fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title"><?= __('reports.total_clients') ?></h6>
                            <h3 class="mb-0">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title"><?= __('reports.pending_orders') ?></h6>
                            <h3 class="mb-0">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Report Modal -->
<div class="modal fade" id="customReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= __('reports.create_custom_report') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/reports/generate">
                <?= csrf_token() ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="report_type" class="form-label"><?= __('reports.report_type') ?></label>
                                <select class="form-select" id="report_type" name="report_type" required>
                                    <option value=""><?= __('common.select') ?></option>
                                    <option value="sales"><?= __('reports.sales_reports') ?></option>
                                    <option value="inventory"><?= __('reports.inventory_reports') ?></option>
                                    <option value="financial"><?= __('reports.financial_reports') ?></option>
                                    <option value="clients"><?= __('reports.client_reports') ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="format" class="form-label"><?= __('reports.format') ?></label>
                                <select class="form-select" id="format" name="format" required>
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                    <option value="csv">CSV</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_from" class="form-label"><?= __('reports.date_from') ?></label>
                                <input type="date" class="form-control" id="date_from" name="date_from">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_to" class="form-label"><?= __('reports.date_to') ?></label>
                                <input type="date" class="form-control" id="date_to" name="date_to">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('common.cancel') ?></button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download me-2"></i>
                        <?= __('reports.generate') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
