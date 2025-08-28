<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="error-page">
                <div class="error-code mb-4">
                    <h1 class="display-1 text-danger">500</h1>
                </div>
                
                <div class="error-message mb-4">
                    <h2 class="h4 mb-3">Internal Server Error</h2>
                    <p class="text-muted">
                        Something went wrong on our end. We're working to fix this issue. Please try again later.
                    </p>
                </div>
                
                <div class="error-actions">
                    <a href="/dashboard" class="btn btn-primary me-2">
                        <i class="fas fa-home me-2"></i>
                        Go to Dashboard
                    </a>
                    <button onclick="window.location.reload()" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-2"></i>
                        Try Again
                    </button>
                </div>
                
                <div class="mt-4">
                    <p class="text-muted small">
                        If this problem persists, please contact the system administrator.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
