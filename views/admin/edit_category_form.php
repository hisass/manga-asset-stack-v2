<?php
$is_new = empty($category['id']);
$form_action = $is_new ? 'create_category' : 'save_category';
$submit_text = $is_new ? 'カテゴリを追加' : '変更を保存';
?>

<h1 class="mb-4"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>

<form action="admin.php?action=<?= $form_action ?>" method="post">
    <?php if (!$is_new): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars($category['id'], ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label for="name" class="form-label fw-bold">カテゴリ名</label>
        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>" required>
    </div>

    <div class="mb-3">
        <label for="alias" class="form-label fw-bold">略称 (alias)</label>
        <input type="text" class="form-control" id="alias" name="alias" value="<?= htmlspecialchars($category['alias'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-text">ナビゲーションバーに表示される短い名前です。</div>
    </div>
    
    <div class="mb-3">
        <label for="title_count" class="form-label fw-bold">トップ表示数 (title_count)</label>
        <input type="number" class="form-control" id="title_count" name="title_count" value="<?= htmlspecialchars($category['title_count'], ENT_QUOTES, 'UTF-8') ?>" min="0" required>
        <div class="form-text">トップページに表示する作品の最大数です。0にすると、トップページやナビゲーションに表示されなくなります。</div>
    </div>
    
    <hr>

    <button type="submit" class="btn btn-primary"><?= $submit_text ?></button>
    <a href="admin.php" class="btn btn-secondary">キャンセル</a>
</form>