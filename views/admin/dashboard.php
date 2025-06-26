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
                <th>フォルダ名</th> <th>略称 (alias)</th>
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
                <th>作品ID</th>
                <th>タイトル</th>
                <th>カテゴリID</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($works as $work): ?>
                <tr>
                    <td><?= htmlspecialchars($work['work_id'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($work['category_id'], ENT_QUOTES, 'UTF-8') ?></td>
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
</section>