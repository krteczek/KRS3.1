<!-- templates/partials/header.php -->
<header class="header">
    <div class="container">
        <h1><a href="<?= $baseUrl ?? '/' ?>"><?= htmlspecialchars($siteName ?? 'KRS3') ?></a></h1>
        <nav class="main-nav">
            <a href="<?= $baseUrl ?? '/' ?>"><?= \App\Core\Config::text('navigation.home') ?></a>
            <a href="<?= ($baseUrl ?? '') ?>clanky"><?= \App\Core\Config::text('navigation.articles') ?></a>

            <?php if (isset($user) && is_array($user) && !empty($user['isLoggedIn'])): ?>
                <span class="user-welcome">
                    <?= \App\Core\Config::text('navigation.welcome', ['username' => $user['username']]) ?>
                </span>
                <a href="<?= $baseUrl ?>admin"><?= \App\Core\Config::text('navigation.admin') ?></a>
                <a href="<?= $baseUrl ?>logout"><?= \App\Core\Config::text('navigation.logout') ?></a>
            <?php else: ?>
                <a href="<?= $baseUrl ?>login"><?= \App\Core\Config::text('navigation.login') ?></a>
            <?php endif; ?>
        </nav>
    </div>
</header>