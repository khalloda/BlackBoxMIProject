<div class="container-fluid py-4">
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        <?= __('quotes.title') ?>
                    </h1>
                    <p class="text-muted mb-0"><?= __('quotes.manage_quotes') ?></p>
                </div>
                <div>
                    <a href="/quotes/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        <?= __('quotes.add_quote') ?>
                    </a>
                    <a href="/quotes/export<?= !empty($search) ? '?search=' . urlencode($search) : '' ?>" class="btn btn-outline-secondary">
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
            <form method="GET" action="/quotes" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label"><?= __('common.search') ?></label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search ?? '') ?>" 
                           placeholder="<?= __('quotes.search_placeholder') ?>">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label"><?= __('common.status') ?></label>
                    <select class="form-select" id="status" name="status">
                        <option value=""><?= __('common.all') ?></option>
                        <option value="draft" <?= ($status ?? '') === 'draft' ? 'selected' : '' ?>><?= __('quotes.status_draft') ?></option>
                        <option value="sent" <?= ($status ?? '') === 'sent' ? 'selected' : '' ?>><?= __('quotes.status_sent') ?></option>
                        <option value="approved" <?= ($status ?? '') === 'approved' ? 'selected' : '' ?>><?= __('quotes.status_approved') ?></option>
                        <option value="rejected" <?= ($status ?? '') === 'rejected' ? 'selected' : '' ?>><?= __('quotes.status_rejected') ?></option>
                        <option value="expired" <?= ($status ?? '') === 'expired' ? 'selected' : '' ?>><?= __('quotes.status_expired') ?></option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="client" class="form-label"><?= __('quotes.client') ?></label>
                    <select class="form-select" id="client" name="client">
                        <option value=""><?= __('common.all') ?></option>
                        <?php if (!empty($clients)): ?>
                            <?php foreach ($clients as $clientItem): ?>
                                <option value="<?= $clientItem['id'] ?>" <?= ($client ?? '') == $clientItem['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($clientItem['company_name'] ?: trim($clientItem['first_name'] . ' ' . $clientItem['last_name'])) ?>
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

    <!-- Quotes Table -->
    <div class="card">
        <div class="card-body">
            <?php if (!empty($quotes)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= __('quotes.quote_number') ?></th>
                                <th><?= __('quotes.client') ?></th>
                                <th><?= __('quotes.quote_date') ?></th>
                                <th><?= __('quotes.valid_until') ?></th>
                                <th><?= __('quotes.total_amount') ?></th>
                                <th><?= __('common.status') ?></th>
                                <th><?= __('quotes.created_by') ?></th>
                                <th><?= __('common.actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quotes as $quote): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($quote['quote_number']) ?></strong>
                                    </td>
                                    <td>
                                        <a href="/clients/<?= $quote['client_id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($quote['client_name'] ?? 'N/A') ?>
                                        </a>
                                    </td>
                                    <td><?= date('Y-m-d', strtotime($quote['quote_date'])) ?></td>
                                    <td>
                                        <?= date('Y-m-d', strtotime($quote['valid_until'])) ?>
                                        <?php if (strtotime($quote['valid_until']) < time() && $quote['status'] === 'sent'): ?>
                                            <i class="fas fa-exclamation-triangle text-warning ms-1" title="<?= __('quotes.expired') ?>"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= number_format($quote['total_amount'], 2) ?> AED</strong>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'sent' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'expired' => 'dark'
                                        ];
                                        $statusColor = $statusColors[$quote['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $statusColor ?>">
                                            <?= __('quotes.status_' . $quote['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= htmlspecialchars($quote['created_by_name'] ?? 'N/A') ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="/quotes/<?= $quote['id'] ?>" class="btn btn-sm btn-outline-primary" title="<?= __('common.view') ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (in_array($quote['status'], ['draft', 'sent'])): ?>
                                                <a href="/quotes/<?= $quote['id'] ?>/edit" class="btn btn-sm btn-outline-secondary" title="<?= __('common.edit') ?>">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="/quotes/<?= $quote['id'] ?>/pdf" class="btn btn-sm btn-outline-info" title="<?= __('quotes.download_pdf') ?>">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <?php if ($quote['status'] === 'draft'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="sendQuote(<?= $quote['id'] ?>)" title="<?= __('quotes.send') ?>">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            <?php elseif ($quote['status'] === 'sent'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="approveQuote(<?= $quote['id'] ?>)" title="<?= __('quotes.approve') ?>">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="rejectQuote(<?= $quote['id'] ?>)" title="<?= __('quotes.reject') ?>">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php elseif ($quote['status'] === 'approved'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="convertToOrder(<?= $quote['id'] ?>)" title="<?= __('quotes.convert_to_order') ?>">
                                                    <i class="fas fa-shopping-cart"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <nav aria-label="Quotes pagination" class="mt-4">
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
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted"><?= __('quotes.no_quotes') ?></h5>
                    <p class="text-muted"><?= __('quotes.no_quotes_desc') ?></p>
                    <a href="/quotes/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        <?= __('quotes.add_first_quote') ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Action Modals -->
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionModalTitle"><?= __('common.confirm_action') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="actionMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('common.cancel') ?></button>
                <form id="actionForm" method="POST" style="display: inline;">
                    <?= csrf_token() ?>
                    <button type="submit" class="btn btn-primary" id="actionButton"><?= __('common.confirm') ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function sendQuote(quoteId) {
    showActionModal(
        '<?= __('quotes.send_quote') ?>',
        '<?= __('quotes.confirm_send') ?>',
        '/quotes/' + quoteId + '/send',
        'btn-success'
    );
}

function approveQuote(quoteId) {
    showActionModal(
        '<?= __('quotes.approve_quote') ?>',
        '<?= __('quotes.confirm_approve') ?>',
        '/quotes/' + quoteId + '/approve',
        'btn-success'
    );
}

function rejectQuote(quoteId) {
    showActionModal(
        '<?= __('quotes.reject_quote') ?>',
        '<?= __('quotes.confirm_reject') ?>',
        '/quotes/' + quoteId + '/reject',
        'btn-danger'
    );
}

function convertToOrder(quoteId) {
    showActionModal(
        '<?= __('quotes.convert_to_order') ?>',
        '<?= __('quotes.confirm_convert') ?>',
        '/quotes/' + quoteId + '/convert',
        'btn-primary'
    );
}

function showActionModal(title, message, action, buttonClass) {
    document.getElementById('actionModalTitle').textContent = title;
    document.getElementById('actionMessage').textContent = message;
    document.getElementById('actionForm').action = action;
    
    const button = document.getElementById('actionButton');
    button.className = 'btn ' + buttonClass;
    
    const modal = new bootstrap.Modal(document.getElementById('actionModal'));
    modal.show();
}
</script>
