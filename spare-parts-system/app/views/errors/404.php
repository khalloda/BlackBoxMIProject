<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="error-page">
                <div class="error-code mb-4">
                    <h1 class="display-1 text-primary">404</h1>
                </div>
                
                <div class="error-message mb-4">
                    <h2 class="h4 mb-3">Page Not Found</h2>
                    <p class="text-muted">
                        The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
                    </p>
                    
                    <?php if (isset($requested_url)): ?>
                        <div class="alert alert-light mt-3">
                            <small class="text-muted">
                                <strong>Requested URL:</strong> <?= htmlspecialchars($requested_url) ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="error-actions">
                    <a href="/dashboard" class="btn btn-primary me-2">
                        <i class="fas fa-home me-2"></i>
                        Go to Dashboard
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Go Back
                    </a>
                </div>
                
                <div class="mt-4">
                    <p class="text-muted small">
                        If you believe this is an error, please contact the system administrator.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-page {
    padding: 2rem;
}

.error-code h1 {
    font-weight: 700;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

.error-message {
    max-width: 500px;
    margin: 0 auto;
}
</style>
