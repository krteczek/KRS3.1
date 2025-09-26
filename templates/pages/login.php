<div class="login-container">
    <div class="login-header">
        <h1>Přihlášení</h1>
        <p>Redakční systém KRS</p>
    </div>

    <?= $error ?>

    <form method="POST" action="<?= $baseUrl ?>/login">
        <div class="form-group">
            <label for="username">Uživatelské jméno:</label>
            <input type="text" id="username" name="username" class="form-input" required>
        </div>

        <div class="form-group">
            <label for="password">Heslo:</label>
            <input type="password" id="password" name="password" class="form-input" required>
        </div>

        <?= $csrfField ?>

        <button type="submit" class="btn btn-primary btn-large">Přihlásit se</button>
    </form>

    <a href="<?= $baseUrl ?>/" class="back-link">← Zpět na úvodní stránku</a>
</div>