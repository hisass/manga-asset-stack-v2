<?php
$is_new = empty($work['work_id']);
$form_action = $is_new ? 'create_work' : 'save_work';
$submit_text = $is_new ? '作品を追加' : '変更を保存';
?>

<div class="container my-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="mb-4"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>

            <form action="admin.php?action=<?= $form_action ?>" method="post">
                <?php if (!$is_new): ?>
                    <input type="hidden" name="work_id" value="<?= htmlspecialchars($work['work_id'], ENT_QUOTES, 'UTF-8') ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="title" class="form-label">タイトル</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="author" class="form-label">著者</label>
                    <input type="text" class="form-control" id="author" name="author" value="<?= htmlspecialchars($work['author'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label">カテゴリ</label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value="">カテゴリを選択してください</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['id'], ENT_QUOTES, 'UTF-8') ?>" <?= (isset($work['category_id']) && $work['category_id'] === $category['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="comment" class="form-label">コメント</label>
                    <textarea class="form-control" id="comment" name="comment" rows="5"><?= htmlspecialchars($work['comment'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="open" class="form-label">公開日</label>
                    <input type="date" class="form-control" id="open" name="open" value="<?= htmlspecialchars($work['open'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="form-text">指定しない場合、保存した当日が自動で設定されます。</div>
                </div>
                <hr class="my-4">

                <div class="d-flex justify-content-between">
                    <a href="admin.php?action=dashboard" class="btn btn-secondary">キャンセル</a>
                    <button type="submit" class="btn btn-primary"><?= $submit_text ?></button>
                </div>
            </form>
        </div>
    </div>
</div>