<?php
// templates/pages/404.php
?>
<div class="container mt-5">
    <div class="row">
        <div class="col-12 text-center">
            <h1 class="display-1 text-warning">404</h1>
            <h2 class="mb-4">Stránka nenalezena</h2>
            <p class="lead"><?= htmlspecialchars($message ?? 'Požadovaná stránka nebyla nalezena.') ?></p>
            <p>Zkontrolujte prosím URL adresu nebo pokračujte na úvodní stránku.</p>
            <a href="<?= $baseUrl ?>" class="btn btn-primary mt-3">
                <i class="fas fa-home me-2"></i><?= $backLinkText ?? 'Zpět na úvodní stránku' ?>
            </a>
        </div>
    </div>
</div><section class="error-page">
    <h1>Stránka nenalezena</h1>
    <p>Požadovaná stránka neexistuje.</p>
    <a href="<?= $baseUrl ?>/" class="btn btn-primary">← Zpět na úvodní stránku</a>
</section>