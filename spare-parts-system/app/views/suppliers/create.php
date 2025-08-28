<div class="container-fluid py-4">
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus me-2"></i>
                        <?= __('suppliers.add_supplier') ?>
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/dashboard"><?= __('dashboard.title') ?></a></li>
                            <li class="breadcrumb-item"><a href="/suppliers"><?= __('suppliers.title') ?></a></li>
                            <li class="breadcrumb-item active"><?= __('suppliers.add_supplier') ?></li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="/suppliers" class="btn btn-outline-secondary">
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

    <!-- Supplier Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-info-circle me-2"></i>
                <?= __('suppliers.supplier_information') ?>
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="/suppliers">
                <?= csrf_token() ?>
                
                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3"><?= __('suppliers.basic_info') ?></h6>
                        
                        <div class="mb-3">
                            <label for="code" class="form-label"><?= __('suppliers.code') ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['code']) ? 'is-invalid' : '' ?>" 
                                   id="code" name="code" value="<?= htmlspecialchars($supplier['code'] ?? $supplier_code) ?>" required>
                            <?php if (isset($errors['code'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['code']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="company_name" class="form-label"><?= __('suppliers.company_name') ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['company_name']) ? 'is-invalid' : '' ?>" 
                                   id="company_name" name="company_name" value="<?= htmlspecialchars($supplier['company_name'] ?? '') ?>" required>
                            <?php if (isset($errors['company_name'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['company_name']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="contact_person" class="form-label"><?= __('suppliers.contact_person') ?></label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person" 
                                   value="<?= htmlspecialchars($supplier['contact_person'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label"><?= __('suppliers.email') ?></label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                   id="email" name="email" value="<?= htmlspecialchars($supplier['email'] ?? '') ?>">
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label"><?= __('suppliers.phone') ?></label>
                                    <input type="text" class="form-control" id="phone" name="phone" 
                                           value="<?= htmlspecialchars($supplier['phone'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mobile" class="form-label"><?= __('suppliers.mobile') ?></label>
                                    <input type="text" class="form-control" id="mobile" name="mobile" 
                                           value="<?= htmlspecialchars($supplier['mobile'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="fax" class="form-label"><?= __('suppliers.fax') ?></label>
                            <input type="text" class="form-control" id="fax" name="fax" 
                                   value="<?= htmlspecialchars($supplier['fax'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Address & Business Information -->
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3"><?= __('suppliers.address_info') ?></h6>
                        
                        <div class="mb-3">
                            <label for="address_en" class="form-label"><?= __('suppliers.address_en') ?></label>
                            <textarea class="form-control" id="address_en" name="address_en" rows="3"><?= htmlspecialchars($supplier['address_en'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="address_ar" class="form-label"><?= __('suppliers.address_ar') ?></label>
                            <textarea class="form-control" id="address_ar" name="address_ar" rows="3" dir="rtl"><?= htmlspecialchars($supplier['address_ar'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="city" class="form-label"><?= __('suppliers.city') ?></label>
                                    <input type="text" class="form-control" id="city" name="city" 
                                           value="<?= htmlspecialchars($supplier['city'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="country" class="form-label"><?= __('suppliers.country') ?></label>
                                    <input type="text" class="form-control" id="country" name="country" 
                                           value="<?= htmlspecialchars($supplier['country'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <h6 class="text-muted mb-3 mt-4"><?= __('suppliers.business_info') ?></h6>

                        <div class="mb-3">
                            <label for="tax_number" class="form-label"><?= __('suppliers.tax_number') ?></label>
                            <input type="text" class="form-control" id="tax_number" name="tax_number" 
                                   value="<?= htmlspecialchars($supplier['tax_number'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="payment_terms" class="form-label"><?= __('suppliers.payment_terms') ?></label>
                            <div class="input-group">
                                <input type="number" class="form-control <?= isset($errors['payment_terms']) ? 'is-invalid' : '' ?>" 
                                       id="payment_terms" name="payment_terms" min="0" 
                                       value="<?= htmlspecialchars($supplier['payment_terms'] ?? '30') ?>">
                                <span class="input-group-text"><?= __('common.days') ?></span>
                                <?php if (isset($errors['payment_terms'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['payment_terms']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text"><?= __('suppliers.payment_terms_help') ?></div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       <?= ($supplier['is_active'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">
                                    <?= __('suppliers.is_active') ?>
                                </label>
                            </div>
                            <div class="form-text"><?= __('suppliers.is_active_help') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/suppliers" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                <?= __('common.cancel') ?>
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                <?= __('suppliers.create_supplier') ?>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-generate supplier code if empty
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('code');
    const companyNameInput = document.getElementById('company_name');
    
    if (!codeInput.value) {
        companyNameInput.addEventListener('input', function() {
            if (!codeInput.value && this.value) {
                // Generate code from company name (first 3 letters + random number)
                const prefix = this.value.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '');
                const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                codeInput.value = 'SUP' + randomNum;
            }
        });
    }
});
</script>
