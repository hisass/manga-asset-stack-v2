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
                    <td><?= htmlspecialchars($category_work_counts[$category['id']], ENT_QUOTES, 'UTF-8') ?></td>
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
                <?php
                function sort_link($label, $key, $current_key, $current_order) {
                    $order = ($key === $current_key && $current_order === 'asc') ? 'desc' : 'asc';
                    $icon = '';
                    if ($key === $current_key) {
                        $icon = ($current_order === 'asc') ? ' ▲' : ' ▼';
                    }
                    return '<a href="admin.php?sort=' . $key . '&order=' . $order . '" class="text-white text-decoration-none">' . $label . $icon . '</a>';
                }
                ?>
                <th></th> <th><?= sort_link('タイトル', 'title', $current_sort_key, $current_sort_order) ?></th>
                <th>カテゴリ</th>
                <th><?= sort_link('公開日', 'open', $current_sort_key, $current_sort_order) ?></th>
                <th>作品ID</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($works as $work): ?>
                <tr>
                    <td>
                        <?php
                        $is_new = false;
                        if (!empty($work['open'])) {
                            $open_timestamp = strtotime($work['open']);
                            if (time() - $open_timestamp < (60 * 60 * 24 * 7)) {
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
        </tbody>
    </table>

    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php if ($total_pages > 1): ?>
                <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="admin.php?page_num=<?= $current_page - 1 ?>&sort=<?= $current_sort_key ?>&order=<?= $current_sort_order ?>">前へ</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                        <a class="page-link" href="admin.php?page_num=<?= $i ?>&sort=<?= $current_sort_key ?>&order=<?= $current_sort_order ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="admin.php?page_num=<?= $current_page + 1 ?>&sort=<?= $current_sort_key ?>&order=<?= $current_sort_order ?>">次へ</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</section>