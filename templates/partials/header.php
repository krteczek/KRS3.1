<header class="header">
    <div class="container">
        <h1><?= $siteName; ?></h1>
        <nav class="main-nav">
            <a href="<?= $baseUrl ?>">Úvod</a>
            <a href="<?= $baseUrl ?>clanky">Články</a>
			<?php if (isset($user) && is_array($user) && !empty($user['isLoggedIn'])): ?>
                <a href="<?= $baseUrl ?>admin">Administrace</a>
                <a href="<?= $baseUrl ?>logout">Odhlásit</a>
            <?php else: ?>
                <a href="<?= $baseUrl ?>login">Přihlásit</a>
            <?php endif; ?>
        </nav>
    </div>
</header>