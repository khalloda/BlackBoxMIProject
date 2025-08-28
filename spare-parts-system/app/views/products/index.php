<div class="container-fluid py-4">
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-box me-2"></i>
                        <?= __('products.title') ?>
                    </h1>
                    <p class="text-muted mb-0"><?= __('products.manage_products') ?></p>
                </div>
                <div>
                    <a href="/products/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        <?= __('products.add_product') ?>
                    </a>
                    <a href="/products/export<?= !empty($search) ? '?search=' . urlencode($search) : '' ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-download me-2"></i>
                        <?= __('common.export') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/products" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label"><?= __('common.search') ?></label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search ?? '') ?>" 
                           placeholder="<?= __('products.search_placeholder') ?>">
                </div>
                <div class="col-md-3">
                    <label for="classification" class="form-label"><?= __('products.classification') ?></label>
                    <select class="form-select" id="classification" name="classification">
                        <option value=""><?= __('common.all') ?></option>
                        <?php if (!empty($classifications)): ?>
                            <?php foreach ($classifications as $class): ?>
                                <option value="<?= $class['id'] ?>" <?= ($classification ?? '') == $class['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($class['name_en']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="brand" class="form-label"><?= __('products.brand') ?></label>
                    <select class="form-select" id="brand" name="brand">
                        <option value=""><?= __('common.all') ?></option>
                        <?php if (!empty($brands)): ?>
                            <?php foreach ($brands as $brandItem): ?>
                                <option value="<?= $brandItem['id'] ?>" <?= ($brand ?? '') == $brandItem['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($brandItem['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search me-2"></i>
                            <?= __('common.search') ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-body">
            <?php if (!empty($products)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= __('products.code') ?></th>
                                <th><?= __('products.name') ?></th>
                                <th><?= __('products.classification') ?></th>
                                <th><?= __('products.brand') ?></th>
                                <th><?= __('products.stock') ?></th>
                                <th><?= __('products.selling_price') ?></th>
                                <th><?= __('common.status') ?></th>
                                <th><?= __('common.actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($product['code']) ?></strong>
                                    </td>
                                    <td>
                                        <a href="/products/<?= $product['id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($product['name_en']) ?>
                                        </a>
                                        <?php if (!empty($product['name_ar'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($product['name_ar']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($product['classification_name'])): ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($product['classification_name']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($product['brand_name'] ?? '') ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($product['total_stock'] ?? 0) > ($product['min_stock_level'] ?? 0) ? 'success' : 'warning' ?>">
                                            <?= $product['total_stock'] ?? 0 ?>
                                        </span>
                                        <?php if (($product['total_stock'] ?? 0) <= ($product['min_stock_level'] ?? 0)): ?>
                                            <i class="fas fa-exclamation-triangle text-warning ms-1" title="<?= __('products.low_stock') ?>"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($product['selling_price'] > 0): ?>
                                            <?= number_format($product['selling_price'], 2) ?> AED
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $product['is_active'] ? 'success' : 'secondary' ?>">
                                            <?= $product['is_active'] ? __('common.active') : __('common.inactive') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="/products/<?= $product['id'] ?>" class="btn btn-sm btn-outline-primary" title="<?= __('common.view') ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/products/<?= $product['id'] ?>/edit" class="btn btn-sm btn-outline-secondary" title="<?= __('common.edit') ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-<?= $product['is_active'] ? 'warning' : 'success' ?>" 
                                                    onclick="toggleStatus(<?= $product['id'] ?>, <?= $product['is_active'] ? 'false' : 'true' ?>)"
                                                    title="<?= $product['is_active'] ? __('common.deactivate') : __('common.activate') ?>">
                                                <i class="fas fa-<?= $product['is_active'] ? 'pause' : 'play' ?>"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <nav aria-label="Products pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['has_previous']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['previous_page'] ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                        <?= __('common.previous') ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['next_page'] ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                        <?= __('common.next') ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-box fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted"><?= __('products.no_products') ?></h5>
                    <p class="text-muted"><?= __('products.no_products_desc') ?></p>
                    <a href="/products/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        <?= __('products.add_first_product') ?>
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
function toggleStatus(productId, newStatus) {
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    const message = newStatus ? 
        '<?= __('products.confirm_activate') ?>' : 
        '<?= __('products.confirm_deactivate') ?>';
    
    document.getElementById('statusMessage').textContent = message;
    document.getElementById('statusForm').action = '/products/' + productId + '/toggle-status';
    
    modal.show();
}
</script>
