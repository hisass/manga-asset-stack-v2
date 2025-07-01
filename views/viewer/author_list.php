<?php
/**
 * views/viewer/author_list.php
 * 著者別作品一覧ページのテンプレート
 */
?>

<h1 class="border-bottom pb-2 mb-4">
    <?= htmlspecialchars($author_name, ENT_QUOTES, 'UTF-8') ?>
</h1>
<?php if (empty($works)): ?>
    <div class="alert alert-info" role="alert">
        この著者の作品は見つかりませんでした。
    </div>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($works as $work): ?>
            <a href="index.php?page=detail&id=<?= urlencode($work['work_id']) ?>" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <?php if (!empty($work['thumbnail_url'])): ?>
                            <img src="<?= htmlspecialchars($work['thumbnail_url'], ENT_QUOTES, 'UTF-8') ?>" class="work-list-thumbnail" alt="<?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php else: ?>
                            <div class="work-list-thumbnail bg-light d-flex align-items-center justify-content-center">
                                <small class="text-muted">NoImg</small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-1"><?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?></h5>
                        <p class="mb-1 text-muted"><small>カテゴリ: <?= isset($work['category_id'], $all_categories[$work['category_id']]) ? htmlspecialchars($all_categories[$work['category_id']]['name'], ENT_QUOTES, 'UTF-8') : '未分類' ?></small></p>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="badge bg-secondary rounded-pill"><?= htmlspecialchars($work['asset_count'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>