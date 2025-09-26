<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $siteName; ?></title>
<?php if (strpos($title, 'Přihlášení') === false): ?>
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/style.css">
<?php endif; ?>

<?php if (strpos($title, 'Přihlášení') !== false): ?>

	<style>
	        /* Oprava chyby v názvu třídy - mělo být .login-page */
        .login-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        /* Dočasné styly pro rychlou opravu */
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #ecf0f1;
            border-radius: 6px;
            font-size: 1rem;
        }

        .btn-primary {
            width: 100%;
            padding: 0.75rem;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
        }
		</style>

<?php endif; ?>

</head>
<body class="<?= strpos($title, 'Přihlášení') !== false ? 'login-page' : '' ?>">
    <?php if (strpos($title, 'Přihlášení') === false): ?>
        <?= include(__DIR__ . '/../partials/header.php'); ?>
    <?php endif; ?>

    <main class="container">
        <?= $content ?>
    </main>

    <?php if (strpos($title, 'Přihlášení') === false): ?>
        <?php include(__DIR__ . '/../partials/footer.php'); ?>
    <?php endif; ?>
</body>
</html>