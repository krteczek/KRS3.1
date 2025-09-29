<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? $siteName ?? 'KRS3') ?></title>
    <link rel="stylesheet" href="<?= $baseUrl ?? '' ?>css/style-new.css">
</head>

<body class="<?= ($isLoginPage ?? false) ? 'login-page' : '' ?>">
    <?php if (!($isLoginPage ?? false)): ?>
        <?php include __DIR__ . '/../partials/header.php'; ?>
    <?php endif; ?>

    <main class="container">
        <?= $content ?? '' ?>
    </main>

    <?php if (!($isLoginPage ?? false)): ?>
        <?php include __DIR__ . '/../partials/footer.php'; ?>
    <?php endif; ?>
</body>
</html>