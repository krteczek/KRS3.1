<div class="row">
    <div class="col-12">
        <h1 class="display-4 mb-4"><?= $welcomeMessage ?></h1>
    </div>
</div>

<?php if (!empty($articles)): ?>
    <div class="row">
        <?php foreach ($articles as $article): ?>
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="<?= $baseUrl ?>/article/<?= $article['slug'] ?>"
                               class="text-decoration-none text-dark">
                                <?= htmlspecialchars($article['title']) ?>
                            </a>
                        </h5>

                        <p class="card-text text-muted small">
                            <i class="fas fa-calendar-alt me-1"></i>
                            <?= date('j. n. Y', strtotime($article['published_at'])) ?>
                            <i class="fas fa-user ms-3 me-1"></i>
                            <?= htmlspecialchars($article['author_name']) ?>
                        </p>

                        <p class="card-text">
                            <?= htmlspecialchars($article['excerpt'] ?? '') ?>
                        </p>

                        <!-- Zobrazení kategorií -->
                        <?php if (!empty($article['categories'])): ?>
                            <div class="mb-3">
                                <?php foreach ($article['categories'] as $category): ?>
                                    <span class="badge bg-primary me-1">
                                        <i class="fas fa-tag me-1"></i>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <a href="<?= $baseUrl ?>/article/<?= $article['slug'] ?>"
                           class="btn btn-outline-primary btn-sm">
                            <?= $readMoreText ?> <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>
                <?= $noArticlesMessage ?>
            </div>
        </div>
    </div>
<?php endif; ?>