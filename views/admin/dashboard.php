<?php
/**
 * ヘルパー関数
 * ソート用のリンクを生成する
 */
function sort_link($label, $key, $current_key, $current_order) {
    $order = ($key === $current_key && $current_order === 'asc') ? 'desc' : 'asc';
    $icon = '';
    if ($key === $current_key) {
        $icon = ($current_order === 'asc') ? ' ▲' : ' ▼';
    }
    // 現在のページ番号を維持するために、URLにpage_numを追加
    $page_num_param = isset($_GET['page_num']) ? '&page_num=' . (int)$_GET['page_num'] : '';
    return '<a href="admin.php?sort=' . $key . '&order=' . $order . $page_num_param . '" class="text-white text-decoration-none">' . $label . $icon . '</a>';
}

/**
 * 【新規追加】ページネーションの表示ロジック
 */
function render_pagination($current_page, $total_pages, $current_sort_key, $current_sort_order) {
    if ($total_pages <= 1) {
        return;
    }

    $window = 2; // 現在のページの前後に表示するページ数
    $start = max(1, $current_page - $window);
    $end = min($total_pages, $current_page + $window);
    
    $base_url = "admin.php?sort={$current_sort_key}&order={$current_sort_order}";

    echo '<ul class="pagination justify-content-center">';
    
    // 「前へ」リンク
    $prev_class = ($current_page <= 1) ? 'disabled' : '';
    echo "<li class=\"page-item {$prev_class}\"><a class=\"page-link\" href=\"{$base_url}&page_num=" . ($current_page - 1) . "\">前へ</a></li>";

    // 最初のページへのリンクと区切り
    if ($start > 1) {
        echo "<li class=\"page-item\"><a class=\"page-link\" href=\"{$base_url}&page_num=1\">1</a></li>";
        if ($start > 2) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    // ページ番号のループ
    for ($i = $start; $i <= $end; $i++) {
        $active_class = ($i == $current_page) ? 'active' : '';
        echo "<li class=\"page-item {$active_class}\"><a class=\"page-link\" href=\"{$base_url}&page_num={$i}\">{$i}</a></li>";
    }

    // 最後のページへのリンクと区切り
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        echo "<li class=\"page-item\"><a class=\"page-link\" href=\"{$base_url}&page_num={$total_pages}\">{$total_pages}</a></li>";
    }
    
    // 「次へ」リンク
    $next_class = ($current_page >= $total_pages) ? 'disabled' : '';
    echo "<li class=\"page-item {$next_class}\"><a class=\"page-link\" href=\"{$base_url}&page_num=" . ($current_page + 1) . "\">次へ</a></li>";

    echo '</ul>';
}
?>
<h1 class="mb-4"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>

<section class="mb-5">
    <div class="d-flex justify-content-between align-items-center">
        <h2>カテゴリ一覧</h2>
        <a href="admin.php?action=edit_category" class="btn btn-primary btn-sm">カテゴリを新規追加</a>
    </div>
    <table class="table table-striped table-bordered table-sm">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>カテゴリ名</th>
                <th>登録作品数</th>
                <th>フォルダ名</th>
                <th>略称 (alias)</th>
                <th>トップ表示数 (title_count)</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= htmlspecialchars($category['id'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(isset($category_work_counts[$category['id']]) ? $category_work_counts[$category['id']] : 0, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= isset($category['directory_name']) ? htmlspecialchars($category['directory_name'], ENT_QUOTES, 'UTF-8') : '' ?></td>
                    <td><?= htmlspecialchars($category['alias'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($category['title_count'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <a href="admin.php?action=edit_category&id=<?= urlencode($category['id']) ?>" class="btn btn-secondary btn-sm">編集</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section>
    <div class="d-flex justify-content-between align-items-center">
        <h2>作品一覧</h2>
        <a href="admin.php?action=add_work" class="btn btn-primary btn-sm">作品を新規追加</a>
    </div>
    <table class="table table-striped table-bordered table-sm">
        <thead class="table-dark">
            <tr>
                <th></th>
                <th><?= sort_link('タイトル', 'title', $current_sort_key, $current_sort_order) ?></th>
                <th>カテゴリ</th>
                <th><?= sort_link('公開日', 'open', $current_sort_key, $current_sort_order) ?></th>
                <th>作品ID</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($works)): ?>
                <?php foreach ($works as $work): ?>
                    <tr>
                        <td>
                            <?php
                            $is_new = false;
                            if (!empty($work['open'])) {
                                $open_timestamp = strtotime($work['open']);
                                if (time() - $open_timestamp < (60 * 60 * 24 * 7)) { // 7日間
                                    $is_new = true;
                                }
                            }
                            if ($is_new) {
                                echo '<span class="badge bg-danger">NEW</span>';
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php
                            $category_name = '未分類';
                            foreach ($categories as $category) {
                                if ($category['id'] === $work['category_id']) {
                                    $category_name = $category['name'];
                                    break;
                                }
                            }
                            echo htmlspecialchars($category_name, ENT_QUOTES, 'UTF-8');
                            ?>
                        </td>
                        <td><?= isset($work['open']) ? htmlspecialchars($work['open'], ENT_QUOTES, 'UTF-8') : '' ?></td>
                        <td><?= htmlspecialchars($work['work_id'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a href="admin.php?action=edit_work&id=<?= urlencode($work['work_id']) ?>" class="btn btn-secondary btn-sm">編集</a>
                            <a href="admin.php?action=delete_work&id=<?= urlencode($work['work_id']) ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('本当に「<?= htmlspecialchars(addslashes($work['title']), ENT_QUOTES, 'UTF-8') ?>」を削除しますか？この操作は元に戻せません。');">
                               削除
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">表示する作品がありません。</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <nav aria-label="Page navigation">
        <?php render_pagination($current_page, $total_pages, $current_sort_key, $current_sort_order); ?>
    </nav>
</section>