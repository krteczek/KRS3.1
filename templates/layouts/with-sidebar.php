<?php
// templates/layouts/with-sidebar.php
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= $baseUrl ?>css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= $baseUrl ?>">
                <?= htmlspecialchars($siteName) ?>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#mainNavbar" aria-controls="mainNavbar"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <?= $menuService->generateHorizontalMenu() ?>

                <!-- Uživatelský panel -->
                <div class="navbar-nav ms-auto">
                    <?php if ($user['isLoggedIn']): ?>
                        <span class="navbar-text me-3">
                            Přihlášen: <?= htmlspecialchars($user['username']) ?>
                        </span>
                        <a class="nav-link" href="<?= $baseUrl ?>admin">Admin</a>
                        <a class="nav-link" href="<?= $baseUrl ?>logout">Odhlásit</a>
                    <?php else: ?>
                        <a class="nav-link" href="<?= $baseUrl ?>login">Přihlásit</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4">
                <div class="sidebar-sticky">
                    <?= $menuService->generateSidebarMenu() ?>

                    <?php if (!empty($popularCategories)): ?>
                        <div class="sidebar-section mt-4">
                            <div class="sidebar-header">
                                <h6>Populární kategorie</h6>
                            </div>
                            <ul class="sidebar-nav">
                                <?php foreach ($popularCategories as $category): ?>
                                    <li class="sidebar-item">
                                        <a class="sidebar-link" href="<?= $baseUrl ?>category/<?= $category['slug'] ?>">
                                            <i class="fas fa-chart-line me-2"></i>
                                            <span class="sidebar-text"><?= htmlspecialchars($category['name']) ?></span>
                                            <span class="sidebar-badge"><?= $category['article_count'] ?? 0 ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Hlavní obsah -->
            <div class="col-lg-9 col-md-8">
                <?php if (!empty($breadcrumb)): ?>
                    <div class="mb-4">
                        <?= $breadcrumb ?>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-light text-center py-4 mt-5">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($siteName) ?>. Všechna práva vyhrazena.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>