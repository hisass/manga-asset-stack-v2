<?php
// 新規追加か編集かで、フォームの送り先とボタンのテキストを切り替える
$is_new = empty($work['work_id']);
$form_action = $is_new ? 'create_work' : 'save_work';
$submit_text = $is_new ? '作品を追加' : '変更を保存';
?>

<h1 class="mb-4"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>

<form action="admin.php?action=<?= $form_action ?>" method="post">
    <?php if (!$is_new): ?>
        <input type="hidden" name="work_id" value="<?= htmlspecialchars($work['work_id'], ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label for="work_id" class="form-label">作品ID (フォルダ名)</label>
        <input type="text" class="form-control" id="work_id" name="work_id" value="<?= htmlspecialchars($work['work_id'], ENT_QUOTES, 'UTF-8') ?>" <?= $is_new ? '' : 'readonly' ?>>
        <?php if ($is_new): ?>
            <div class="form-text">新規追加の場合、半角英数字とハイフンのみ使用してください。これがフォルダ名になります。</div>
        <?php else: ?>
            <div class="form-text">作品IDは編集できません。</div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="title" class="form-label">タイトル</label>
        <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="mb-3">
        <label for="title_ruby" class="form-label">タイトル（ルビ）</label>
        <input type="text" class="form-control" id="title_ruby" name="title_ruby" value="<?= htmlspecialchars($work['title_ruby'], ENT_QUOTES, 'UTF-8') ?>">
    </div>
    
    <div class="mb-3">
        <label for="author" class="form-label">著者</label>
        <input type="text" class="form-control" id="author" name="author" value="<?= htmlspecialchars($work['author'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="mb-3">
        <label for="author_ruby" class="form-label">著者（ルビ）</label>
        <input type="text" class="form-control" id="author_ruby" name="author_ruby" value="<?= htmlspecialchars($work['author_ruby'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="mb-3">
        <label for="category_id" class="form-label">カテゴリ</label>
        <select class="form-select" id="category_id" name="category_id">
            <option value="">カテゴリを選択してください</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars($category['id'], ENT_QUOTES, 'UTF-8') ?>" <?= ($work['category_id'] === $category['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="comment" class="form-label">コメント</label>
        <textarea class="form-control" id="comment" name="comment" rows="5"><?= htmlspecialchars($work['comment'], ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <hr>

    <button type="submit" class="btn btn-primary"><?= $submit_text ?></button>
    <a href="admin.php" class="btn btn-secondary">キャンセル</a>
</form>