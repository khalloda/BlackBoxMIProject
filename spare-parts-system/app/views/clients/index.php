<div class="container-fluid py-4">
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-users me-2"></i>
                        <?= __('clients.title') ?>
                    </h1>
                    <p class="text-muted mb-0">Manage your clients and customer database</p>
                </div>
                <div>
                    <a href="/clients/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        <?= __('clients.add_client') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="/clients" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Search by code, name, email...">
                        </div>
                        <div class="col-md-3">
                            <label for="type" class="form-label">Client Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">All Types</option>
                                <option value="company" <?= $type === 'company' ? 'selected' : '' ?>>Company</option>
                                <option value="individual" <?= $type === 'individual' ? 'selected' : '' ?>>Individual</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fas fa-search me-1"></i>
                                <?= __('search') ?>
                            </button>
                            <a href="/clients" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times me-1"></i>
                                <?= __('reset') ?>
                            </a>
                            <a href="/clients/export?<?= http_build_query(['search' => $search, 'type' => $type]) ?>" 
                               class="btn btn-outline-success">
                                <i class="fas fa-download me-1"></i>
                                Export CSV
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Clients Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>
                        Clients List
                        <?php if ($search || $type): ?>
                            <small class="text-muted">
                                (Filtered<?= $search ? ' by: ' . htmlspecialchars($search) : '' ?>)
                            </small>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($clients)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Client Name</th>
                                        <th>Type</th>
                                        <th>Contact</th>
                                        <th>City</th>
                                        <th>Credit Limit</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clients as $client): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($client['code']) ?></strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($client['display_name']) ?></strong>
                                                    <?php if ($client['type'] === 'company' && $client['contact_person']): ?>
                                                        <br><small class="text-muted">Contact: <?= htmlspecialchars($client['contact_person']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $client['type'] === 'company' ? 'primary' : 'info' ?>">
                                                    <?= ucfirst($client['type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($client['email']): ?>
                                                    <div><i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($client['email']) ?></div>
                                                <?php endif; ?>
                                                <?php if ($client['phone']): ?>
                                                    <div><i class="fas fa-phone me-1"></i> <?= htmlspecialchars($client['phone']) ?></div>
                                                <?php endif; ?>
                                                <?php if ($client['mobile']): ?>
                                                    <div><i class="fas fa-mobile-alt me-1"></i> <?= htmlspecialchars($client['mobile']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($client['city'] ?: '-') ?></td>
                                            <td>
                                                <?= \App\Core\Language::formatCurrency($client['credit_limit']) ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $client['is_active'] ? 'success' : 'secondary' ?>">
                                                    <?= $client['is_active'] ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="/clients/<?= $client['id'] ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="/clients/<?= $client['id'] ?>/edit" 
                                                       class="btn btn-sm btn-outline-secondary" 
                                                       title="Edit Client">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-<?= $client['is_active'] ? 'warning' : 'success' ?>" 
                                                            onclick="toggleClientStatus(<?= $client['id'] ?>, <?= $client['is_active'] ? 'false' : 'true' ?>)"
                                                            title="<?= $client['is_active'] ? 'Deactivate' : 'Activate' ?> Client">
                                                        <i class="fas fa-<?= $client['is_active'] ? 'pause' : 'play' ?>"></i>
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
                            <nav aria-label="Clients pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($pagination['has_previous']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($type) ?>">
                                                <?= __('pagination.previous') ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                        <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($type) ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($pagination['has_next']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($type) ?>">
                                                <?= __('pagination.next') ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                            
                            <div class="text-center text-muted">
                                <?= __('pagination.showing') ?> <?= ($pagination['current_page'] - 1) * $pagination['per_page'] + 1 ?> 
                                <?= __('pagination.to') ?> <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> 
                                <?= __('pagination.of') ?> <?= $pagination['total'] ?> <?= __('pagination.results') ?>
                            </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No clients found</h5>
                            <p class="text-muted">
                                <?php if ($search || $type): ?>
                                    No clients match your search criteria. <a href="/clients">Clear filters</a> to see all clients.
                                <?php else: ?>
                                    Start by adding your first client to the system.
                                <?php endif; ?>
                            </p>
                            <?php if (!$search && !$type): ?>
                                <a href="/clients/create" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>
                                    <?= __('clients.add_client') ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
</div>

<script>
function toggleClientStatus(clientId, newStatus) {
    if (!confirm('Are you sure you want to ' + (newStatus ? 'activate' : 'deactivate') + ' this client?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch('/clients/' + clientId + '/toggle-status', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            SPMS.utils.showToast(data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            SPMS.utils.showToast(data.message || 'Failed to update client status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        SPMS.utils.showToast('An error occurred', 'error');
    });
}
</script>
