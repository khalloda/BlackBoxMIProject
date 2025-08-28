<div class="container-fluid py-4">
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-truck me-2"></i>
                        <?= __('suppliers.title') ?>
                    </h1>
                    <p class="text-muted mb-0"><?= __('suppliers.manage_suppliers') ?></p>
                </div>
                <div>
                    <a href="/suppliers/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        <?= __('suppliers.add_supplier') ?>
                    </a>
                    <a href="/suppliers/export<?= $search ? '?search=' . urlencode($search) : '' ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-download me-2"></i>
                        <?= __('common.export') ?>
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

    <!-- Search and Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/suppliers" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label"><?= __('common.search') ?></label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="<?= __('suppliers.search_placeholder') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search me-2"></i>
                            <?= __('common.search') ?>
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <a href="/suppliers" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            <?= __('common.clear') ?>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Suppliers Table -->
    <div class="card">
        <div class="card-body">
            <?php if (!empty($suppliers)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= __('suppliers.code') ?></th>
                                <th><?= __('suppliers.company_name') ?></th>
                                <th><?= __('suppliers.contact_person') ?></th>
                                <th><?= __('suppliers.email') ?></th>
                                <th><?= __('suppliers.phone') ?></th>
                                <th><?= __('suppliers.city') ?></th>
                                <th><?= __('suppliers.payment_terms') ?></th>
                                <th><?= __('common.status') ?></th>
                                <th><?= __('common.actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suppliers as $supplier): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($supplier['code']) ?></strong>
                                    </td>
                                    <td>
                                        <a href="/suppliers/<?= $supplier['id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($supplier['company_name']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($supplier['contact_person']) ?></td>
                                    <td>
                                        <?php if ($supplier['email']): ?>
                                            <a href="mailto:<?= htmlspecialchars($supplier['email']) ?>">
                                                <?= htmlspecialchars($supplier['email']) ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($supplier['phone']): ?>
                                            <a href="tel:<?= htmlspecialchars($supplier['phone']) ?>">
                                                <?= htmlspecialchars($supplier['phone']) ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($supplier['city']) ?></td>
                                    <td>
                                        <?= $supplier['payment_terms'] ?> <?= __('common.days') ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $supplier['is_active'] ? 'success' : 'secondary' ?>">
                                            <?= $supplier['is_active'] ? __('common.active') : __('common.inactive') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="/suppliers/<?= $supplier['id'] ?>" class="btn btn-sm btn-outline-primary" title="<?= __('common.view') ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/suppliers/<?= $supplier['id'] ?>/edit" class="btn btn-sm btn-outline-secondary" title="<?= __('common.edit') ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-<?= $supplier['is_active'] ? 'warning' : 'success' ?>" 
                                                    onclick="toggleStatus(<?= $supplier['id'] ?>, <?= $supplier['is_active'] ? 'false' : 'true' ?>)"
                                                    title="<?= $supplier['is_active'] ? __('common.deactivate') : __('common.activate') ?>">
                                                <i class="fas fa-<?= $supplier['is_active'] ? 'pause' : 'play' ?>"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <nav aria-label="Suppliers pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['has_previous']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['previous_page'] ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                                        <?= __('common.previous') ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['next_page'] ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                                        <?= __('common.next') ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted"><?= __('suppliers.no_suppliers') ?></h5>
                    <p class="text-muted"><?= __('suppliers.no_suppliers_desc') ?></p>
                    <a href="/suppliers/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        <?= __('suppliers.add_first_supplier') ?>
                    </a>
                </div>
            <?php endif; ?>
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
