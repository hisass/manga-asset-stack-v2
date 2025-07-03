<?php
// views/admin/dashboard.php (タブの状態を動的に変更)

function get_opposite_order($order) {
    return ($order === 'asc') ? 'desc' : 'asc';
}
function get_sort_indicator($key, $current_key, $current_order) {
    if ($key === $current_key) {
        return ($current_order === 'asc') ? ' ▲' : ' ▼';
    }
    return '';
}
?>
<div class="container-fluid mt-4">
    <h1 class="h2 mb-4">管理ダッシュボード</h1>

    <ul class="nav nav-tabs" id="adminTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= ($active_tab === 'works') ? 'active' : '' ?>" id="works-tab" data-bs-toggle="tab" data-bs-target="#works-tab-pane" type="button" role="tab" aria-controls="works-tab-pane" aria-selected="<?= ($active_tab === 'works') ? 'true' : 'false' ?>">作品管理</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= ($active_tab === 'categories') ? 'active' : '' ?>" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories-tab-pane" type="button" role="tab" aria-controls="categories-tab-pane" aria-selected="<?= ($active_tab === 'categories') ? 'true' : 'false' ?>">カテゴリ管理</button>
        </li>
    </ul>

    <div class="tab-content" id="adminTabContent">
        
        <div class="tab-pane fade <?= ($active_tab === 'works') ? 'show active' : '' ?>" id="works-tab-pane" role="tabpanel" aria-labelledby="works-tab" tabindex="0">
            <div class="card card-tab-content-top">
                <div class="card-body">
                    <form action="admin.php" method="GET" class="row g-3 align-items-center">
                        <input type="hidden" name="action" value="dashboard">
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($current_sort_key, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="order" value="<?= htmlspecialchars($current_sort_order, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="col-md-4">
                            <input type="search" class="form-control" name="search" placeholder="キーワード検索..." value="<?= htmlspecialchars($current_search_keyword, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="filter_category">
                                <option value="">すべてのカテゴリ</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= htmlspecialchars($category['id'], ENT_QUOTES, 'UTF-8'); ?>" <?= ($current_filter_category === $category['id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?> (<?= isset($category_work_counts[$category['id']]) ? $category_work_counts[$category['id']] : 0; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">絞り込み</button>
                        </div>
                        <div class="col-md-3 text-end">
                            <a href="admin.php?action=add_work" class="btn btn-success">作品追加</a>
                        </div>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col"><a href="?action=dashboard&sort=title&order=<?= get_opposite_order($current_sort_order); ?>" class="text-white text-decoration-none">作品タイトル<?= get_sort_indicator('title', $current_sort_key, $current_sort_order); ?></a></th>
                            <th scope="col"><a href="?action=dashboard&sort=author&order=<?= get_opposite_order($current_sort_order); ?>" class="text-white text-decoration-none">作者<?= get_sort_indicator('author', $current_sort_key, $current_sort_order); ?></a></th>
                            <th scope="col">カテゴリ</th>
                            <th scope="col"><a href="?action=dashboard&sort=open&order=<?= get_opposite_order($current_sort_order); ?>" class="text-white text-decoration-none">公開日<?= get_sort_indicator('open', $current_sort_key, $current_sort_order); ?></a></th>
                            <th scope="col">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($works)): ?>
                            <tr><td colspan="5" class="text-center">表示する作品がありません。</td></tr>
                        <?php else: ?>
                            <?php foreach ($works as $work): ?>
                                <tr>
                                    <td class="align-middle">
                                        <?php
                                        $source_badge = '';
                                        if (isset($work['source'])) {
                                            if ($work['source'] === 'added') $source_badge = ' <span class="badge bg-success">v2</span>';
                                            elseif ($work['source'] === 'updated') $source_badge = ' <span class="badge bg-primary">更新</span>';
                                        }
                                        ?>
                                        <a href="admin.php?action=edit_work&id=<?= htmlspecialchars($work['work_id'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8'); ?></a><?= $source_badge; ?>
                                    </td>
                                    <td class="align-middle"><?= htmlspecialchars($work['author'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="align-middle"><?= isset($work['category_id'], $categories[$work['category_id']]) ? htmlspecialchars($categories[$work['category_id']]['name'], ENT_QUOTES, 'UTF-8') : '未分類'; ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($work['open'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="align-middle">
                                        <a href="admin.php?action=edit_work&id=<?= htmlspecialchars($work['work_id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-primary">編集</a>
                                        <a href="admin.php?action=delete_work&id=<?= htmlspecialchars($work['work_id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('本当にこの作品を削除しますか？');">削除</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (isset($total_pages) && $total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($current_page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?action=dashboard&page_num=<?= $current_page - 1 ?>&sort=<?= htmlspecialchars($current_sort_key) ?>&order=<?= htmlspecialchars($current_sort_order) ?>&filter_category=<?= htmlspecialchars($current_filter_category) ?>&search=<?= htmlspecialchars($current_search_keyword) ?>">前へ</a></li>
                    <?php endif; ?>
                    <?php
                    $start_page = max(1, $current_page - 3);
                    $end_page = min($total_pages, $current_page + 3);
                    if ($start_page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?action=dashboard&page_num=1&sort=' . htmlspecialchars($current_sort_key) . '&order=' . htmlspecialchars($current_sort_order) . '&filter_category=' . htmlspecialchars($current_filter_category) . '&search=' . htmlspecialchars($current_search_keyword) . '">1</a></li>';
                        if ($start_page > 2) { echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; }
                    }
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        echo '<li class="page-item ' . ($i == $current_page ? 'active' : '') . '">';
                        echo '<a class="page-link" href="?action=dashboard&page_num=' . $i . '&sort=' . htmlspecialchars($current_sort_key) . '&order=' . htmlspecialchars($current_sort_order) . '&filter_category=' . htmlspecialchars($current_filter_category) . '&search=' . htmlspecialchars($current_search_keyword) . '">' . $i . '</a>';
                        echo '</li>';
                    }
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) { echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; }
                        echo '<li class="page-item"><a class="page-link" href="?action=dashboard&page_num=' . $total_pages . '&sort=' . htmlspecialchars($current_sort_key) . '&order=' . htmlspecialchars($current_sort_order) . '&filter_category=' . htmlspecialchars($current_filter_category) . '&search=' . htmlspecialchars($current_search_keyword) . '">' . $total_pages . '</a></li>';
                    }
                    ?>
                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="?action=dashboard&page_num=<?= $current_page + 1 ?>&sort=<?= htmlspecialchars($current_sort_key) ?>&order=<?= htmlspecialchars($current_sort_order) ?>&filter_category=<?= htmlspecialchars($current_filter_category) ?>&search=<?= htmlspecialchars($current_search_keyword) ?>">次へ</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade <?= ($active_tab === 'categories') ? 'show active' : '' ?>" id="categories-tab-pane" role="tabpanel" aria-labelledby="categories-tab" tabindex="0">
            <div class="card card-tab-content-top text-end">
                <div class="card-body">
                     <a href="admin.php?action=edit_category" class="btn btn-success">カテゴリ新規追加</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>カテゴリ名</th>
                            <th>トップ表示数</th>
                            <th>エイリアス</th>
                            <th>作品数</th>
                            <th>操作</th>
                            <th class="text-center">並び順</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr><td colspan="6" class="text-center">登録されているカテゴリはありません。</td></tr>
                        <?php else: ?>
                            <?php 
                            $cat_keys = array_keys($categories);
                            $last_key_index = count($cat_keys) - 1;
                            $current_index = 0;
                            ?>
                            <?php foreach ($categories as $cat_id => $cat): ?>
                            <tr>
                                <td class="align-middle">
                                     <?php
                                        $source_badge = '';
                                        if (isset($cat['source'])) {
                                            if ($cat['source'] === 'added') $source_badge = ' <span class="badge bg-success">v2</span>';
                                            elseif ($cat['source'] === 'updated') $source_badge = ' <span class="badge bg-primary">更新</span>';
                                        }
                                        ?>
                                    <a href="admin.php?action=edit_category&id=<?= htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?></a><?= $source_badge; ?>
                                </td>
                                <td class="align-middle"><?= htmlspecialchars($cat['title_count'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="align-middle"><?= htmlspecialchars($cat['alias'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="align-middle"><?= isset($category_work_counts[$cat['id']]) ? $category_work_counts[$cat['id']] : 0; ?></td>
                                <td class="align-middle">
                                    <a href="admin.php?action=edit_category&id=<?= htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-primary">編集</a>
                                </td>
                                <td class="align-middle text-center">
                                    <?php if ($current_index > 0): ?>
                                        <a href="admin.php?action=move_category&id=<?= $cat_id ?>&direction=up" title="上へ">▲</a>
                                    <?php endif; ?>
                                    <?php if ($current_index < $last_key_index): ?>
                                        <a href="admin.php?action=move_category&id=<?= $cat_id ?>&direction=down" title="下へ">▼</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php $current_index++; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.card-tab-content-top {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
}
</style>