<div class="container-fluid py-4">
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-edit me-2"></i>
                        <?= __('clients.edit_client') ?>
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/dashboard"><?= __('dashboard.title') ?></a></li>
                            <li class="breadcrumb-item"><a href="/clients"><?= __('clients.title') ?></a></li>
                            <li class="breadcrumb-item active"><?= __('clients.edit_client') ?></li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="/clients/<?= $client['id'] ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        <?= __('common.back') ?>
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

    <!-- Client Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-info-circle me-2"></i>
                <?= __('clients.client_information') ?>
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="/clients/<?= $client['id'] ?>">
                <?= csrf_token() ?>
                
                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3"><?= __('clients.basic_info') ?></h6>
                        
                        <div class="mb-3">
                            <label for="code" class="form-label"><?= __('clients.code') ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['code']) ? 'is-invalid' : '' ?>" 
                                   id="code" name="code" value="<?= htmlspecialchars($client['code']) ?>" required>
                            <?php if (isset($errors['code'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['code']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label"><?= __('clients.type') ?> <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type" required onchange="toggleClientType()">
                                <option value="company" <?= $client['type'] === 'company' ? 'selected' : '' ?>><?= __('clients.company') ?></option>
                                <option value="individual" <?= $client['type'] === 'individual' ? 'selected' : '' ?>><?= __('clients.individual') ?></option>
                            </select>
                        </div>

                        <!-- Company Fields -->
                        <div id="companyFields" style="display: <?= $client['type'] === 'company' ? 'block' : 'none' ?>">
                            <div class="mb-3">
                                <label for="company_name" class="form-label"><?= __('clients.company_name') ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= isset($errors['company_name']) ? 'is-invalid' : '' ?>" 
                                       id="company_name" name="company_name" value="<?= htmlspecialchars($client['company_name']) ?>">
                                <?php if (isset($errors['company_name'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['company_name']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="contact_person" class="form-label"><?= __('clients.contact_person') ?></label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person" 
                                       value="<?= htmlspecialchars($client['contact_person']) ?>">
                            </div>
                        </div>

                        <!-- Individual Fields -->
                        <div id="individualFields" style="display: <?= $client['type'] === 'individual' ? 'block' : 'none' ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label"><?= __('clients.first_name') ?> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                                               id="first_name" name="first_name" value="<?= htmlspecialchars($client['first_name']) ?>">
                                        <?php if (isset($errors['first_name'])): ?>
                                            <div class="invalid-feedback"><?= htmlspecialchars($errors['first_name']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label"><?= __('clients.last_name') ?> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                                               id="last_name" name="last_name" value="<?= htmlspecialchars($client['last_name']) ?>">
                                        <?php if (isset($errors['last_name'])): ?>
                                            <div class="invalid-feedback"><?= htmlspecialchars($errors['last_name']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label"><?= __('clients.email') ?></label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                   id="email" name="email" value="<?= htmlspecialchars($client['email']) ?>">
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label"><?= __('clients.phone') ?></label>
                                    <input type="text" class="form-control" id="phone" name="phone" 
                                           value="<?= htmlspecialchars($client['phone']) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mobile" class="form-label"><?= __('clients.mobile') ?></label>
                                    <input type="text" class="form-control" id="mobile" name="mobile" 
                                           value="<?= htmlspecialchars($client['mobile']) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="fax" class="form-label"><?= __('clients.fax') ?></label>
                            <input type="text" class="form-control" id="fax" name="fax" 
                                   value="<?= htmlspecialchars($client['fax']) ?>">
                        </div>
                    </div>

                    <!-- Address & Business Information -->
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3"><?= __('clients.address_info') ?></h6>
                        
                        <div class="mb-3">
                            <label for="address_en" class="form-label"><?= __('clients.address_en') ?></label>
                            <textarea class="form-control" id="address_en" name="address_en" rows="3"><?= htmlspecialchars($client['address_en']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="address_ar" class="form-label"><?= __('clients.address_ar') ?></label>
                            <textarea class="form-control" id="address_ar" name="address_ar" rows="3" dir="rtl"><?= htmlspecialchars($client['address_ar']) ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="city" class="form-label"><?= __('clients.city') ?></label>
                                    <input type="text" class="form-control" id="city" name="city" 
                                           value="<?= htmlspecialchars($client['city']) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="country" class="form-label"><?= __('clients.country') ?></label>
                                    <input type="text" class="form-control" id="country" name="country" 
                                           value="<?= htmlspecialchars($client['country']) ?>">
                                </div>
                            </div>
                        </div>

                        <h6 class="text-muted mb-3 mt-4"><?= __('clients.business_info') ?></h6>

                        <div class="mb-3">
                            <label for="tax_number" class="form-label"><?= __('clients.tax_number') ?></label>
                            <input type="text" class="form-control" id="tax_number" name="tax_number" 
                                   value="<?= htmlspecialchars($client['tax_number']) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="credit_limit" class="form-label"><?= __('clients.credit_limit') ?></label>
                            <div class="input-group">
                                <input type="number" class="form-control <?= isset($errors['credit_limit']) ? 'is-invalid' : '' ?>" 
                                       id="credit_limit" name="credit_limit" min="0" step="0.01"
                                       value="<?= htmlspecialchars($client['credit_limit']) ?>">
                                <span class="input-group-text">AED</span>
                                <?php if (isset($errors['credit_limit'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['credit_limit']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="payment_terms" class="form-label"><?= __('clients.payment_terms') ?></label>
                            <div class="input-group">
                                <input type="number" class="form-control <?= isset($errors['payment_terms']) ? 'is-invalid' : '' ?>" 
                                       id="payment_terms" name="payment_terms" min="0" 
                                       value="<?= htmlspecialchars($client['payment_terms']) ?>">
                                <span class="input-group-text"><?= __('common.days') ?></span>
                                <?php if (isset($errors['payment_terms'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['payment_terms']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="discount_percentage" class="form-label"><?= __('clients.discount_percentage') ?></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="discount_percentage" name="discount_percentage" 
                                       min="0" max="100" step="0.01" value="<?= htmlspecialchars($client['discount_percentage']) ?>">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       <?= $client['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">
                                    <?= __('clients.is_active') ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/clients/<?= $client['id'] ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                <?= __('common.cancel') ?>
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                <?= __('clients.update_client') ?>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleClientType() {
    const type = document.getElementById('type').value;
    const companyFields = document.getElementById('companyFields');
    const individualFields = document.getElementById('individualFields');
    
    if (type === 'company') {
        companyFields.style.display = 'block';
        individualFields.style.display = 'none';
        document.getElementById('company_name').required = true;
        document.getElementById('first_name').required = false;
        document.getElementById('last_name').required = false;
    } else {
        companyFields.style.display = 'none';
        individualFields.style.display = 'block';
        document.getElementById('company_name').required = false;
        document.getElementById('first_name').required = true;
        document.getElementById('last_name').required = true;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleClientType();
});
</script>
