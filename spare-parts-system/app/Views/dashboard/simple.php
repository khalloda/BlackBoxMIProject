<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <h4>Welcome to Spare Parts Management System!</h4>
                        <p>You have successfully logged in. The system is working correctly.</p>
                        <hr>
                        <p class="mb-0">
                            <strong>User:</strong> <?= htmlspecialchars($user['full_name'] ?? 'Unknown') ?><br>
                            <strong>Role:</strong> <?= htmlspecialchars($user['role'] ?? 'Unknown') ?><br>
                            <strong>Login Time:</strong> <?= date('Y-m-d H:i:s') ?>
                        </p>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5>Clients</h5>
                                    <p>Manage your clients</p>
                                    <a href="/clients" class="btn btn-light btn-sm">View Clients</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5>Products</h5>
                                    <p>Manage your products</p>
                                    <a href="/products" class="btn btn-light btn-sm">View Products</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5>Quotes</h5>
                                    <p>Create and manage quotes</p>
                                    <a href="/quotes" class="btn btn-light btn-sm">View Quotes</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5>Reports</h5>
                                    <p>View system reports</p>
                                    <a href="/reports" class="btn btn-light btn-sm">View Reports</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
