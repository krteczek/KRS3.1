<!-- templates/partials/footer.php -->
<div class="footer">
    <div class="footer-content">
        <div class="footer-copyright">
            KRS&copy; Verze 3.0 <?= date('Y') ?>
        </div>

        <div class="language-switcher">
            <?php
            $currentLang = $_SESSION['language'] ?? 'cs';
            $languages = [
                'cs' => 'Česky',
                'en' => 'English',
                'de' => 'Deutsch'
            ];
            ?>

            <?php foreach ($languages as $code => $name): ?>
                <a href="?lang=<?= $code ?>"
                   class="<?= $currentLang === $code ? 'active' : '' ?>">
                    <?= $name ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>