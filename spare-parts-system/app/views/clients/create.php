<div class="container-fluid py-4">
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        <?= __('clients.add_client') ?>
                    </h1>
                    <p class="text-muted mb-0">Add a new client to your database</p>
                </div>
                <div>
                    <a href="/clients" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        <?= __('back') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Client Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <?= __('clients.client_details') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/clients" id="clientForm" novalidate>
                        <?= \App\Core\CSRF::field() ?>
                        
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Basic Information</h6>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="code" class="form-label"><?= __('clients.client_code') ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= isset($errors['code']) ? 'is-invalid' : '' ?>" 
                                       id="code" name="code" value="<?= htmlspecialchars($client['code'] ?? $client_code) ?>" required>
                                <?php if (isset($errors['code'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['code']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="type" class="form-label"><?= __('clients.client_type') ?> <span class="text-danger">*</span></label>
                                <select class="form-select <?= isset($errors['type']) ? 'is-invalid' : '' ?>" 
                                        id="type" name="type" required onchange="toggleClientType()">
                                    <option value="">Select Type</option>
                                    <option value="company" <?= ($client['type'] ?? '') === 'company' ? 'selected' : '' ?>>
                                        <?= __('clients.company') ?>
                                    </option>
                                    <option value="individual" <?= ($client['type'] ?? '') === 'individual' ? 'selected' : '' ?>>
                                        <?= __('clients.individual') ?>
                                    </option>
                                </select>
                                <?php if (isset($errors['type'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['type']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                           <?= ($client['is_active'] ?? true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active">
                                        <?= __('active') ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Company Information -->
                        <div id="companyFields" class="row mb-4" style="display: none;">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Company Information</h6>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="company_name" class="form-label"><?= __('clients.company_name') ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= isset($errors['company_name']) ? 'is-invalid' : '' ?>" 
                                       id="company_name" name="company_name" value="<?= htmlspecialchars($client['company_name'] ?? '') ?>">
                                <?php if (isset($errors['company_name'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['company_name']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="contact_person" class="form-label"><?= __('clients.contact_person') ?></label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person" 
                                       value="<?= htmlspecialchars($client['contact_person'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <!-- Individual Information -->
                        <div id="individualFields" class="row mb-4" style="display: none;">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Personal Information</h6>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="first_name" class="form-label"><?= __('clients.first_name') ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                                       id="first_name" name="first_name" value="<?= htmlspecialchars($client['first_name'] ?? '') ?>">
                                <?php if (isset($errors['first_name'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['first_name']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="last_name" class="form-label"><?= __('clients.last_name') ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                                       id="last_name" name="last_name" value="<?= htmlspecialchars($client['last_name'] ?? '') ?>">
                                <?php if (isset($errors['last_name'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['last_name']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Contact Information</h6>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="email" class="form-label"><?= __('clients.email') ?></label>
                                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                       id="email" name="email" value="<?= htmlspecialchars($client['email'] ?? '') ?>">
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="phone" class="form-label"><?= __('clients.phone') ?></label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($client['phone'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="mobile" class="form-label"><?= __('clients.mobile') ?></label>
                                <input type="tel" class="form-control" id="mobile" name="mobile" 
                                       value="<?= htmlspecialchars($client['mobile'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-4 mt-3">
                                <label for="fax" class="form-label"><?= __('clients.fax') ?></label>
                                <input type="tel" class="form-control" id="fax" name="fax" 
                                       value="<?= htmlspecialchars($client['fax'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <!-- Address Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Address Information</h6>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="address_en" class="form-label"><?= __('clients.address') ?> (English)</label>
                                <textarea class="form-control" id="address_en" name="address_en" rows="3"><?= htmlspecialchars($client['address_en'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="address_ar" class="form-label"><?= __('clients.address') ?> (Arabic)</label>
                                <textarea class="form-control" id="address_ar" name="address_ar" rows="3" dir="rtl"><?= htmlspecialchars($client['address_ar'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="col-md-6 mt-3">
                                <label for="city" class="form-label"><?= __('clients.city') ?></label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?= htmlspecialchars($client['city'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-6 mt-3">
                                <label for="country" class="form-label"><?= __('clients.country') ?></label>
                                <input type="text" class="form-control" id="country" name="country" 
                                       value="<?= htmlspecialchars($client['country'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <!-- Business Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Business Information</h6>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="tax_number" class="form-label"><?= __('clients.tax_number') ?></label>
                                <input type="text" class="form-control" id="tax_number" name="tax_number" 
                                       value="<?= htmlspecialchars($client['tax_number'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="credit_limit" class="form-label"><?= __('clients.credit_limit') ?></label>
                                <input type="number" class="form-control <?= isset($errors['credit_limit']) ? 'is-invalid' : '' ?>" 
                                       id="credit_limit" name="credit_limit" step="0.01" min="0"
                                       value="<?= htmlspecialchars($client['credit_limit'] ?? '0') ?>">
                                <?php if (isset($errors['credit_limit'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['credit_limit']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="payment_terms" class="form-label"><?= __('clients.payment_terms') ?></label>
                                <input type="number" class="form-control <?= isset($errors['payment_terms']) ? 'is-invalid' : '' ?>" 
                                       id="payment_terms" name="payment_terms" min="0"
                                       value="<?= htmlspecialchars($client['payment_terms'] ?? '30') ?>">
                                <?php if (isset($errors['payment_terms'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['payment_terms']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="discount_percentage" class="form-label"><?= __('clients.discount_percentage') ?></label>
                                <input type="number" class="form-control <?= isset($errors['discount_percentage']) ? 'is-invalid' : '' ?>" 
                                       id="discount_percentage" name="discount_percentage" step="0.01" min="0" max="100"
                                       value="<?= htmlspecialchars($client['discount_percentage'] ?? '0') ?>">
                                <?php if (isset($errors['discount_percentage'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['discount_percentage']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="/clients" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>
                                        <?= __('cancel') ?>
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="fas fa-save me-2"></i>
                                        <?= __('save') ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form
    toggleClientType();
    
    // Form submission
    const form = document.getElementById('clientForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        } else {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        }
        form.classList.add('was-validated');
    });
});

function toggleClientType() {
    const type = document.getElementById('type').value;
    const companyFields = document.getElementById('companyFields');
    const individualFields = document.getElementById('individualFields');
    const companyName = document.getElementById('company_name');
    const firstName = document.getElementById('first_name');
    const lastName = document.getElementById('last_name');
    
    if (type === 'company') {
        companyFields.style.display = 'flex';
        individualFields.style.display = 'none';
        companyName.required = true;
        firstName.required = false;
        lastName.required = false;
    } else if (type === 'individual') {
        companyFields.style.display = 'none';
        individualFields.style.display = 'flex';
        companyName.required = false;
        firstName.required = true;
        lastName.required = true;
    } else {
        companyFields.style.display = 'none';
        individualFields.style.display = 'none';
        companyName.required = false;
        firstName.required = false;
        lastName.required = false;
    }
}
</script>
