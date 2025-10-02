<h1><?= $article['title']; ?></h1>
<div class="excerpt"><?= $article['excerpt']; ?></div>
<div class="content"><?= $article['content']; ?></div>
<div class="author_name"><?= $this->t('article.author'); ?>: <?= $article['author_name']; ?></div>
<div class="published"><?= $this->t('article.published'); ?>: <?= $article['published_at']; ?></div>