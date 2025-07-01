<?php
/**
 * views/viewer/home.php
 * トップページのテンプレート（カード表示・サムネイル付き）
 */
?>

<?php
// ナビゲーションバーから「NEW」を除いた、トップページに表示すべきカテゴリのリストを作成
$categories_for_home = array_filter($all_categories, function($cat) {
    return isset($cat['id']) && $cat['id'] !== 'new';
});
?>

<?php foreach ($categories_for_home as $category): ?>
    <?php // Check if this category has any works to display
    if (!empty($works_by_category[$category['id']])): ?>
        <section class="mb-5">
            <a href="index.php?page=category&id=<?= urlencode($category['id']) ?>" class="text-decoration-none category-title-link">
                <h2><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></h2>
            </a>
            <hr>
            <div class="row">
                <?php // Now loop through the works for this specific category
                foreach ($works_by_category[$category['id']] as $work): ?>
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <a href="index.php?page=detail&id=<?= urlencode($work['work_id']) ?>" class="card-link text-decoration-none text-dark">
                            <div class="card h-100">
                                <?php if (!empty($work['thumbnail_url'])): ?>
                                    <img src="<?= htmlspecialchars($work['thumbnail_url'], ENT_QUOTES, 'UTF-8') ?>" class="card-img-top work-thumbnail-home" alt="<?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?>のサムネイル">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center work-thumbnail-home">
                                        <small class="text-muted">No Image</small>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h6 class="card-title fw-bold"><?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?></h6>
                                    <p class="card-text small text-muted"><?= htmlspecialchars($work['author'], ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
<?php endforeach; ?>