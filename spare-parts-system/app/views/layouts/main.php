<!DOCTYPE html>
<html lang="<?= app('language')->getCurrentLanguage() ?>" dir="<?= app('language')->getCurrentLanguage() === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) . ' - ' : '' ?><?= __('app.name') ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/css/app.css" rel="stylesheet">
    
    <?php if (app('language')->getCurrentLanguage() === 'ar'): ?>
        <link href="/css/rtl.css" rel="stylesheet">
    <?php endif; ?>
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= csrf_token() ?>">
</head>
<body>
    <!-- Navigation -->
    <?php if (Auth::check()): ?>
        <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="<?= Auth::check() ? 'main-content' : 'auth-content' ?>">
        <!-- Flash Messages -->
        <?php include __DIR__ . '/../partials/flash.php'; ?>
        
        <!-- Page Content -->
        <?= $content ?>
    </main>
    
    <!-- Footer -->
    <?php if (Auth::check()): ?>
        <?php include __DIR__ . '/../partials/footer.php'; ?>
    <?php endif; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/js/app.js"></script>
    
    <!-- Additional Scripts -->
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
