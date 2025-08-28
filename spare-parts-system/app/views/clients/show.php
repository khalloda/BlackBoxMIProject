<div class="container-fluid py-4">
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-user me-2"></i>
                        <?= $client['company_name'] ?: trim($client['first_name'] . ' ' . $client['last_name']) ?>
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/dashboard"><?= __('dashboard.title') ?></a></li>
                            <li class="breadcrumb-item"><a href="/clients"><?= __('clients.title') ?></a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($client['code']) ?></li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="/clients" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        <?= __('common.back') ?>
                    </a>
                    <a href="/clients/<?= $client['id'] ?>/edit" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>
                        <?= __('common.edit') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($flash_messages)): ?>
        <?php foreach ($flash_messages as $type => $message): ?>
            <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="row">
        <!-- Client Information -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <?= __('clients.client_information') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3"><?= __('clients.basic_info') ?></h6>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('clients.code') ?></label>
                                <div class="fw-bold"><?= htmlspecialchars($client['code']) ?></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('clients.type') ?></label>
                                <div>
                                    <span class="badge bg-<?= $client['type'] === 'company' ? 'primary' : 'info' ?>">
                                        <?= $client['type'] === 'company' ? __('clients.company') : __('clients.individual') ?>
                                    </span>
                                </div>
                            </div>

                            <?php if ($client['type'] === 'company'): ?>
                                <div class="mb-3">
                                    <label class="form-label text-muted"><?= __('clients.company_name') ?></label>
                                    <div class="fw-bold"><?= htmlspecialchars($client['company_name']) ?></div>
                                </div>

                                <?php if ($client['contact_person']): ?>
                                <div class="mb-3">
                                    <label class="form-label text-muted"><?= __('clients.contact_person') ?></label>
                                    <div><?= htmlspecialchars($client['contact_person']) ?></div>
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="mb-3">
                                    <label class="form-label text-muted"><?= __('clients.full_name') ?></label>
                                    <div class="fw-bold"><?= htmlspecialchars(trim($client['first_name'] . ' ' . $client['last_name'])) ?></div>
                                </div>
                            <?php endif; ?>

                            <?php if ($client['email']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('clients.email') ?></label>
                                <div>
                                    <a href="mailto:<?= htmlspecialchars($client['email']) ?>">
                                        <?= htmlspecialchars($client['email']) ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="row">
                                <?php if ($client['phone']): ?>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted"><?= __('clients.phone') ?></label>
                                        <div>
                                            <a href="tel:<?= htmlspecialchars($client['phone']) ?>">
                                                <?= htmlspecialchars($client['phone']) ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($client['mobile']): ?>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted"><?= __('clients.mobile') ?></label>
                                        <div>
                                            <a href="tel:<?= htmlspecialchars($client['mobile']) ?>">
                                                <?= htmlspecialchars($client['mobile']) ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($client['fax']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('clients.fax') ?></label>
                                <div><?= htmlspecialchars($client['fax']) ?></div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted mb-3"><?= __('clients.address_info') ?></h6>
                            
                            <?php if ($client['address_en']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('clients.address_en') ?></label>
                                <div><?= nl2br(htmlspecialchars($client['address_en'])) ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if ($client['address_ar']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('clients.address_ar') ?></label>
                                <div dir="rtl"><?= nl2br(htmlspecialchars($client['address_ar'])) ?></div>
                            </div>
                            <?php endif; ?>

                            <div class="row">
                                <?php if ($client['city']): ?>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted"><?= __('clients.city') ?></label>
                                        <div><?= htmlspecialchars($client['city']) ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($client['country']): ?>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted"><?= __('clients.country') ?></label>
                                        <div><?= htmlspecialchars($client['country']) ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <h6 class="text-muted mb-3 mt-4"><?= __('clients.business_info') ?></h6>

                            <?php if ($client['tax_number']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('clients.tax_number') ?></label>
                                <div><?= htmlspecialchars($client['tax_number']) ?></div>
                            </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('clients.credit_limit') ?></label>
                                <div><?= number_format($client['credit_limit'], 2) ?> AED</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('clients.payment_terms') ?></label>
                                <div><?= $client['payment_terms'] ?> <?= __('common.days') ?></div>
                            </div>

                            <?php if ($client['discount_percentage'] > 0): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('clients.discount_percentage') ?></label>
                                <div><?= $client['discount_percentage'] ?>%</div>
                            </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('common.status') ?></label>
                                <div>
                                    <span class="badge bg-<?= $client['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $client['is_active'] ? __('common.active') : __('common.inactive') ?>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('common.created_at') ?></label>
                                <div><?= date('Y-m-d H:i', strtotime($client['created_at'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Quotes -->
            <?php if (!empty($quotes)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        <?= __('clients.recent_quotes') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th><?= __('quotes.quote_number') ?></th>
                                    <th><?= __('quotes.quote_date') ?></th>
                                    <th><?= __('quotes.total_amount') ?></th>
                                    <th><?= __('common.status') ?></th>
                                    <th><?= __('common.actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($quotes as $quote): ?>
                                <tr>
                                    <td><?= htmlspecialchars($quote['quote_number']) ?></td>
                                    <td><?= date('Y-m-d', strtotime($quote['quote_date'])) ?></td>
                                    <td><?= number_format($quote['total_amount'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $quote['status'] === 'approved' ? 'success' : ($quote['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                            <?= ucfirst($quote['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/quotes/<?= $quote['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Orders -->
            <?php if (!empty($orders)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>
                        <?= __('clients.recent_orders') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th><?= __('sales_orders.order_number') ?></th>
                                    <th><?= __('sales_orders.order_date') ?></th>
                                    <th><?= __('sales_orders.total_amount') ?></th>
                                    <th><?= __('common.status') ?></th>
                                    <th><?= __('common.actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['order_number']) ?></td>
                                    <td><?= date('Y-m-d', strtotime($order['order_date'])) ?></td>
                                    <td><?= number_format($order['total_amount'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $order['status'] === 'delivered' ? 'success' : ($order['status'] === 'cancelled' ? 'danger' : 'warning') ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/sales-orders/<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Statistics & Actions -->
        <div class="col-lg-4">
            <!-- Statistics -->
            <?php if (isset($client['stats'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        <?= __('clients.statistics') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary mb-0"><?= $client['stats']['quotes_count'] ?></h4>
                                <small class="text-muted"><?= __('clients.total_quotes') ?></small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success mb-0"><?= $client['stats']['orders_count'] ?></h4>
                            <small class="text-muted"><?= __('clients.total_orders') ?></small>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-info mb-0"><?= number_format($client['stats']['total_sales'], 0) ?></h4>
                                <small class="text-muted"><?= __('clients.total_sales') ?></small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning mb-0"><?= number_format($client['stats']['outstanding_balance'], 0) ?></h4>
                            <small class="text-muted"><?= __('clients.outstanding') ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        <?= __('common.quick_actions') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/quotes/create?client_id=<?= $client['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-file-alt me-2"></i>
                            <?= __('clients.create_quote') ?>
                        </a>
                        
                        <a href="/sales-orders/create?client_id=<?= $client['id'] ?>" class="btn btn-success">
                            <i class="fas fa-shopping-cart me-2"></i>
                            <?= __('clients.create_order') ?>
                        </a>
                        
                        <a href="/clients/<?= $client['id'] ?>/edit" class="btn btn-outline-secondary">
                            <i class="fas fa-edit me-2"></i>
                            <?= __('common.edit') ?>
                        </a>
                        
                        <button type="button" class="btn btn-outline-<?= $client['is_active'] ? 'warning' : 'success' ?>" 
                                onclick="toggleStatus(<?= $client['id'] ?>, <?= $client['is_active'] ? 'false' : 'true' ?>)">
                            <i class="fas fa-<?= $client['is_active'] ? 'pause' : 'play' ?> me-2"></i>
                            <?= $client['is_active'] ? __('common.deactivate') : __('common.activate') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Toggle Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= __('common.confirm_action') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="statusMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('common.cancel') ?></button>
                <form id="statusForm" method="POST" style="display: inline;">
                    <?= csrf_token() ?>
                    <button type="submit" class="btn btn-primary"><?= __('common.confirm') ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleStatus(clientId, newStatus) {
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    const message = newStatus ? 
        '<?= __('clients.confirm_activate') ?>' : 
        '<?= __('clients.confirm_deactivate') ?>';
    
    document.getElementById('statusMessage').textContent = message;
    document.getElementById('statusForm').action = '/clients/' + clientId + '/toggle-status';
    
    modal.show();
}
</script>
