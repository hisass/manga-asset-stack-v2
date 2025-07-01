<?php
/**
 * views/viewer/home.php
 * トップページのテンプレート
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
                    <div class="col-12 col-md-6 mb-3">
                         <a href="index.php?page=detail&id=<?= urlencode($work['work_id']) ?>" class="text-decoration-none text-dark d-block">
                            <strong class="work-title"><?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <br>
                            <small class="text-muted work-author"><?= htmlspecialchars($work['author'], ENT_QUOTES, 'UTF-8') ?></small>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
<?php endforeach; ?>