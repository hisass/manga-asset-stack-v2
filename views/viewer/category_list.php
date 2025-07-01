<?php
/**
 * views/viewer/category_list.php
 * カテゴリ別作品一覧ページのテンプレート
 */
?>

<div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4">
    <h1 class="mb-0"><?= htmlspecialchars($page_specific_category['name'], ENT_QUOTES, 'UTF-8') ?></h1>
    
    <form action="index.php" method="GET" class="d-flex align-items-center">
        <input type="hidden" name="page" value="category">
        <input type="hidden" name="id" value="<?= htmlspecialchars($category_id, ENT_QUOTES, 'UTF-8') ?>">
        
        <label for="sort-select" class="form-label mb-0 me-2">並び順:</label>
        <select class="form-select form-select-sm" id="sort-select" name="sort" onchange="this.form.submit()">
            <?php
            $sort_options = array(
                'open_desc' => 'デフォルト (公開日順)',
                'title_asc' => 'タイトル (昇順)',
                'title_desc' => 'タイトル (降順)',
                'author_asc' => '著者名 (昇順)',
                'author_desc' => '著者名 (降順)'
            );
            foreach ($sort_options as $value => $label):
                $selected = (isset($current_sort) && $current_sort === $value) ? ' selected' : '';
            ?>
                <option value="<?= $value ?>"<?= $selected ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>
<?php if (empty($works)): ?>
    <div class="alert alert-info" role="alert">
        このカテゴリに登録されている作品はありません。
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
                        <p class="mb-1 text-muted"><small><?= htmlspecialchars($work['author'], ENT_QUOTES, 'UTF-8') ?></small></p>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="badge bg-secondary rounded-pill"><?= htmlspecialchars($work['asset_count'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>