<?php
// ▼▼▼【修正箇所】正しいパスに修正します ▼▼▼
require_once BASE_DIR_PATH . '/views/admin/layouts/header.php';
?>

<div class="container">
    <h1>管理ダッシュボード</h1>

    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <ul class="nav nav-tabs" id="adminTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="works-tab" data-toggle="tab" href="#works" role="tab" aria-controls="works" aria-selected="true">作品管理</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="categories-tab" data-toggle="tab" href="#categories" role="tab" aria-controls="categories" aria-selected="false">カテゴリ管理</a>
        </li>
    </ul>

    <div class="tab-content" id="adminTabContent">
        <div class="tab-pane fade show active" id="works" role="tabpanel" aria-labelledby="works-tab">
            <div class="d-flex justify-content-between align-items-center my-3">
                <h2>作品一覧</h2>
                <a href="admin.php?action=add_work" class="btn btn-primary">作品を追加</a>
            </div>
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>タイトル</th>
                        <th>著者</th>
                        <th>カテゴリ</th>
                        <th>公開日</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (is_array($works) && !empty($works)): ?>
                        <?php foreach ($works as $work_id => $work): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($work['author'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php
                                    if (isset($work['category_id']) && isset($categories[$work['category_id']])) {
                                        echo htmlspecialchars($categories[$work['category_id']]['name'], ENT_QUOTES, 'UTF-8');
                                    } else {
                                        echo '<span class="text-muted">カテゴリなし</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($work['open'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <a href="admin.php?action=edit_work&id=<?php echo $work_id; ?>" class="btn btn-sm btn-info">編集</a>
                                    <a href="admin.php?action=delete_work&id=<?php echo $work_id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('本当に削除しますか？');">削除</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">登録されている作品はありません。</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="tab-pane fade" id="categories" role="tabpanel" aria-labelledby="categories-tab">
            <div class="d-flex justify-content-between align-items-center my-3">
                <h2>カテゴリ一覧</h2>
                <a href="admin.php?action=edit_category" class="btn btn-primary">カテゴリを追加</a>
            </div>
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>カテゴリ名</th>
                        <th>エイリアス</th>
                        <th>表示順</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (is_array($categories) && !empty($categories)): ?>
                        <?php foreach ($categories as $cat_id => $category): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($category['alias'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <a href="admin.php?action=move_category&id=<?php echo $cat_id; ?>&direction=up" class="btn btn-sm btn-light">↑</a>
                                    <a href="admin.php?action=move_category&id=<?php echo $cat_id; ?>&direction=down" class="btn btn-sm btn-light">↓</a>
                                </td>
                                <td>
                                    <a href="admin.php?action=edit_category&id=<?php echo $cat_id; ?>" class="btn btn-sm btn-info">編集</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">登録されているカテゴリはありません。</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// ▼▼▼【修正箇所】正しいパスに修正します (footer.phpも同様と想定) ▼▼▼
require_once BASE_DIR_PATH . '/views/admin/layouts/footer.php';
?>