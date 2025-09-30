<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? \App\Core\Config::text('admin.navigation.administration') ?> - KRS</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/admin.css">
</head>
<body>
    <?php include 'partials/admin-header.php'; ?>

    <div class="admin-container">
        <?php include 'partials/messages.php'; ?>
        <div class="admin-content">
            <?= $content ?>
        </div>
    </div>
</body>
</html>