<?php if (!empty($flash_messages)): ?>
    <div class="container-fluid mt-3">
        <?php foreach ($flash_messages as $type => $message): ?>
            <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show" role="alert">
                <?php if ($type === 'success'): ?>
                    <i class="fas fa-check-circle me-2"></i>
                <?php elseif ($type === 'error'): ?>
                    <i class="fas fa-exclamation-triangle me-2"></i>
                <?php elseif ($type === 'warning'): ?>
                    <i class="fas fa-exclamation-circle me-2"></i>
                <?php elseif ($type === 'info'): ?>
                    <i class="fas fa-info-circle me-2"></i>
                <?php endif; ?>
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
