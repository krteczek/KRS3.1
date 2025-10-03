<div class="login-container">
    <div class="login-header">
        <h1><?= $loginTitle ?></h1>
        <p><?= $siteName ?></p>
    </div>

    <?= $error ?>

    <form method="POST" action="<?= $baseUrl ?>login">
        <div class="form-group">
            <label for="username"><?= $usernameLabel ?>:</label>
            <input type="text" id="username" name="username" class="form-input" required>
        </div>

        <div class="form-group">
            <label for="password"><?= $passwordLabel ?>:</label>
            <input type="password" id="password" name="password" class="form-input" required>
        </div>

        <?= $csrfField ?>

        <button type="submit" class="btn btn-primary btn-large"><?= $submitText ?></button>
    </form>

    <a href="<?= $baseUrl ?>" class="back-link">← <?= $backLinkText ?></a>
</div>