<div class="container-fluid py-4">
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-truck me-2"></i>
                        <?= htmlspecialchars($supplier['company_name']) ?>
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/dashboard"><?= __('dashboard.title') ?></a></li>
                            <li class="breadcrumb-item"><a href="/suppliers"><?= __('suppliers.title') ?></a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($supplier['code']) ?></li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="/suppliers" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        <?= __('common.back') ?>
                    </a>
                    <a href="/suppliers/<?= $supplier['id'] ?>/edit" class="btn btn-primary">
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
        <!-- Supplier Information -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <?= __('suppliers.supplier_information') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3"><?= __('suppliers.basic_info') ?></h6>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('suppliers.code') ?></label>
                                <div class="fw-bold"><?= htmlspecialchars($supplier['code']) ?></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('suppliers.company_name') ?></label>
                                <div class="fw-bold"><?= htmlspecialchars($supplier['company_name']) ?></div>
                            </div>

                            <?php if ($supplier['contact_person']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('suppliers.contact_person') ?></label>
                                <div><?= htmlspecialchars($supplier['contact_person']) ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if ($supplier['email']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('suppliers.email') ?></label>
                                <div>
                                    <a href="mailto:<?= htmlspecialchars($supplier['email']) ?>">
                                        <?= htmlspecialchars($supplier['email']) ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="row">
                                <?php if ($supplier['phone']): ?>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted"><?= __('suppliers.phone') ?></label>
                                        <div>
                                            <a href="tel:<?= htmlspecialchars($supplier['phone']) ?>">
                                                <?= htmlspecialchars($supplier['phone']) ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($supplier['mobile']): ?>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted"><?= __('suppliers.mobile') ?></label>
                                        <div>
                                            <a href="tel:<?= htmlspecialchars($supplier['mobile']) ?>">
                                                <?= htmlspecialchars($supplier['mobile']) ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($supplier['fax']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('suppliers.fax') ?></label>
                                <div><?= htmlspecialchars($supplier['fax']) ?></div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted mb-3"><?= __('suppliers.address_info') ?></h6>
                            
                            <?php if ($supplier['address_en']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('suppliers.address_en') ?></label>
                                <div><?= nl2br(htmlspecialchars($supplier['address_en'])) ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if ($supplier['address_ar']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('suppliers.address_ar') ?></label>
                                <div dir="rtl"><?= nl2br(htmlspecialchars($supplier['address_ar'])) ?></div>
                            </div>
                            <?php endif; ?>

                            <div class="row">
                                <?php if ($supplier['city']): ?>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted"><?= __('suppliers.city') ?></label>
                                        <div><?= htmlspecialchars($supplier['city']) ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($supplier['country']): ?>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted"><?= __('suppliers.country') ?></label>
                                        <div><?= htmlspecialchars($supplier['country']) ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <h6 class="text-muted mb-3 mt-4"><?= __('suppliers.business_info') ?></h6>

                            <?php if ($supplier['tax_number']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('suppliers.tax_number') ?></label>
                                <div><?= htmlspecialchars($supplier['tax_number']) ?></div>
                            </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('suppliers.payment_terms') ?></label>
                                <div><?= $supplier['payment_terms'] ?> <?= __('common.days') ?></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('common.status') ?></label>
                                <div>
                                    <span class="badge bg-<?= $supplier['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $supplier['is_active'] ? __('common.active') : __('common.inactive') ?>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted"><?= __('common.created_at') ?></label>
                                <div><?= date('Y-m-d H:i', strtotime($supplier['created_at'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Purchase Orders -->
            <?php if (!empty($purchase_orders)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>
                        <?= __('suppliers.recent_purchase_orders') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th><?= __('purchase_orders.po_number') ?></th>
                                    <th><?= __('purchase_orders.po_date') ?></th>
                                    <th><?= __('purchase_orders.total_amount') ?></th>
                                    <th><?= __('common.status') ?></th>
                                    <th><?= __('common.actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($purchase_orders as $po): ?>
                                <tr>
                                    <td><?= htmlspecialchars($po['po_number']) ?></td>
                                    <td><?= date('Y-m-d', strtotime($po['po_date'])) ?></td>
                                    <td><?= number_format($po['total_amount'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $po['status'] === 'confirmed' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($po['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/purchase-orders/<?= $po['id'] ?>" class="btn btn-sm btn-outline-primary">
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
            <?php if (isset($supplier['stats'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        <?= __('suppliers.statistics') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary mb-0"><?= $supplier['stats']['purchase_orders_count'] ?></h4>
                                <small class="text-muted"><?= __('suppliers.total_orders') ?></small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success mb-0"><?= number_format($supplier['stats']['purchase_orders_total'], 0) ?></h4>
                            <small class="text-muted"><?= __('suppliers.total_value') ?></small>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-info mb-0"><?= $supplier['stats']['payments_count'] ?></h4>
                                <small class="text-muted"><?= __('suppliers.total_payments') ?></small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning mb-0"><?= number_format($supplier['stats']['outstanding_balance'], 0) ?></h4>
                            <small class="text-muted"><?= __('suppliers.outstanding') ?></small>
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
                        <a href="/purchase-orders/create?supplier_id=<?= $supplier['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            <?= __('suppliers.create_purchase_order') ?>
                        </a>
                        
                        <a href="/suppliers/<?= $supplier['id'] ?>/edit" class="btn btn-outline-secondary">
                            <i class="fas fa-edit me-2"></i>
                            <?= __('common.edit') ?>
                        </a>
                        
                        <button type="button" class="btn btn-outline-<?= $supplier['is_active'] ? 'warning' : 'success' ?>" 
                                onclick="toggleStatus(<?= $supplier['id'] ?>, <?= $supplier['is_active'] ? 'false' : 'true' ?>)">
                            <i class="fas fa-<?= $supplier['is_active'] ? 'pause' : 'play' ?> me-2"></i>
                            <?= $supplier['is_active'] ? __('common.deactivate') : __('common.activate') ?>
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
function toggleStatus(supplierId, newStatus) {
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    const message = newStatus ? 
        '<?= __('suppliers.confirm_activate') ?>' : 
        '<?= __('suppliers.confirm_deactivate') ?>';
    
    document.getElementById('statusMessage').textContent = message;
    document.getElementById('statusForm').action = '/suppliers/' + supplierId + '/toggle-status';
    
    modal.show();
}
</script>
