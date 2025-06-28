<?php
// views/admin/dashboard.php (最終版)

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

    <div class="card mb-4">
        <div class="card-body">
            <form action="admin.php" method="GET" class="row g-3 align-items-center">
                <input type="hidden" name="action" value="dashboard">
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($current_sort_key, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="order" value="<?php echo htmlspecialchars($current_sort_order, ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="col-md-4">
                    <label for="search" class="visually-hidden">検索</label>
                    <input type="search" class="form-control" id="search" name="search" placeholder="タイトル, 作者名, コメントで検索..." value="<?php echo htmlspecialchars($current_search_keyword, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-md-3">
                    <label for="filter_category" class="visually-hidden">カテゴリ</label>
                    <select class="form-select" id="filter_category" name="filter_category">
                        <option value="">すべてのカテゴリ</option>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['id'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($current_filter_category === $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo isset($category_work_counts[$category['id']]) ? $category_work_counts[$category['id']] : 0; ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">絞り込み</button>
                </div>
                 <div class="col-md-3 text-end">
                    <a href="admin.php?action=edit_category" class="btn btn-secondary">カテゴリ追加</a>
                    <a href="admin.php?action=add_work" class="btn btn-success">作品追加</a>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th scope="col">
                        <a href="?action=dashboard&sort=title&order=<?php echo get_opposite_order($current_sort_order); ?>" class="text-white text-decoration-none">
                            作品タイトル<?php echo get_sort_indicator('title', $current_sort_key, $current_sort_order); ?>
                        </a>
                    </th>
                    <th scope="col">
                         <a href="?action=dashboard&sort=author&order=<?php echo get_opposite_order($current_sort_order); ?>" class="text-white text-decoration-none">
                            作者<?php echo get_sort_indicator('author', $current_sort_key, $current_sort_order); ?>
                        </a>
                    </th>
                    <th scope="col">カテゴリ</th>
                    <th scope="col">
                        <a href="?action=dashboard&sort=open&order=<?php echo get_opposite_order($current_sort_order); ?>" class="text-white text-decoration-none">
                            公開日<?php echo get_sort_indicator('open', $current_sort_key, $current_sort_order); ?>
                        </a>
                    </th>
                    <th scope="col">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($works)): ?>
                    <tr>
                        <td colspan="5" class="text-center">表示する作品がありません。</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($works as $work): ?>
                        <tr>
                            <td class="align-middle">
                                <?php
                                // ★★★ここからが変更箇所★★★
                                $source_badge = '';
                                if (isset($work['source'])) {
                                    if ($work['source'] === 'added') {
                                        // 「新規」を「v2」に変更し、位置調整スタイルを追加
                                        $source_badge = ' <span class="badge" style="background-color: #198754; vertical-align: middle; margin-left: 4px;">v2</span>'; 
                                    } elseif ($work['source'] === 'updated') {
                                        // 位置調整スタイルを追加
                                        $source_badge = ' <span class="badge" style="background-color: #0d6efd; vertical-align: middle; margin-left: 4px;">更新</span>';
                                    }
                                }
                                ?>
                                <a href="admin.php?action=edit_work&id=<?php echo htmlspecialchars($work['work_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                                <?php echo $source_badge; ?>
                                <?php // ★★★ここまでが変更箇所★★★ ?>
                            </td>
                            <td class="align-middle"><?php echo htmlspecialchars($work['author'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="align-middle">
                                <?php
                                $category_name = '未分類';
                                if (isset($work['category_id']) && isset($categories[$work['category_id']])) {
                                    $category_name = $categories[$work['category_id']]['name'];
                                }
                                echo htmlspecialchars($category_name, ENT_QUOTES, 'UTF-8');
                                ?>
                            </td>
                            <td class="align-middle"><?php echo htmlspecialchars($work['open'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="align-middle">
                                <a href="admin.php?action=edit_work&id=<?php echo htmlspecialchars($work['work_id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-primary">編集</a>
                                <a href="admin.php?action=delete_work&id=<?php echo htmlspecialchars($work['work_id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('本当にこの作品を削除しますか？');">削除</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (isset($total_pages) && $total_pages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?action=dashboard&page_num=<?php echo $i; ?>&sort=<?php echo htmlspecialchars($current_sort_key, ENT_QUOTES, 'UTF-8'); ?>&order=<?php echo htmlspecialchars($current_sort_order, ENT_QUOTES, 'UTF-8'); ?>&filter_category=<?php echo htmlspecialchars($current_filter_category, ENT_QUOTES, 'UTF-8'); ?>&search=<?php echo htmlspecialchars($current_search_keyword, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>

</div>