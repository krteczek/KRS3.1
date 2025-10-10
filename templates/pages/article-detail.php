<?php
// templates/pages/article-detail.php
?>
<div class="article-detail">
    <article>
        <header class="mb-4">
            <h1 class="display-4"><?= htmlspecialchars($article['title']) ?></h1>

            <div class="article-meta text-muted mb-3">
                <span class="me-3">
                    <i class="fas fa-calendar-alt me-1"></i>
                    <?= $article['formatted_date'] ?? date('j. n. Y', strtotime($article['published_at'])) ?>
                </span>
                <span class="me-3">
                    <i class="fas fa-user me-1"></i>
                    <?= htmlspecialchars($article['author_name']) ?>
                </span>

                <?php if (!empty($article['categories'])): ?>
                    <span class="me-3">
                        <i class="fas fa-tags me-1"></i>
                        <?php foreach ($article['categories'] as $index => $category): ?>
                            <a href="<?= $baseUrl ?>/category/<?= $category['slug'] ?>" class="text-muted">
                                <?= htmlspecialchars($category['name']) ?><?= $index < count($article['categories']) - 1 ? ',' : '' ?>
                            </a>
                        <?php endforeach; ?>
                    </span>
                <?php endif; ?>
            </div>
        </header>

        <div class="article-content">
            <?= $article['content'] ?>
        </div>

        <footer class="mt-5 pt-4 border-top">
            <a href="<?= $baseUrl ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i><?= $backLinkText ?>
            </a>
        </footer>
    </article>
</div>