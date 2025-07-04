<?php
$is_new = empty($work['work_id']);
$form_action = $is_new ? 'create_work' : 'save_work';
$submit_text = $is_new ? '作品を追加' : '変更を保存';
?>

<div class="container my-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="mb-4"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>

            <form action="admin.php?action=<?= $form_action ?>" method="post" enctype="multipart/form-data">
                <?php if (!$is_new): ?>
                    <input type="hidden" name="work_id" value="<?= htmlspecialchars($work['work_id'], ENT_QUOTES, 'UTF-8') ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="title" class="form-label">タイトル</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="title_ruby" class="form-label">タイトル（ルビ）</label>
                    <input type="text" class="form-control" id="title_ruby" name="title_ruby" value="<?= htmlspecialchars($work['title_ruby'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="mb-3">
                    <label for="directory_name" class="form-label">ディレクトリ名</label>
                    <input type="text" class="form-control" id="directory_name" name="directory_name" value="<?= htmlspecialchars($work['directory_name'], ENT_QUOTES, 'UTF-8') ?>" <?= $is_new ? 'required' : 'readonly' ?>>
                    <?php if ($is_new): ?>
                        <div class="form-text">アセットを格納するフォルダの名前です。半角英数字とハイフン、アンダースコアのみ使用してください。</div>
                    <?php else: ?>
                        <div class="form-text">ディレクトリ名は一度作成すると変更できません。</div>
                    <?php endif; ?>
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
                <h5 class="mb-3">画像アセットの管理</h5>

                <?php if (!$is_new && !empty($assets)): ?>
                <div class="mb-4">
                    <label class="form-label">既存のアセット</label>
                    <div class="row g-2">
                        <?php foreach ($assets as $asset): ?>
                        <div class="col-md-4">
                            <div class="card">
                                <img src="<?= htmlspecialchars($asset['url'], ENT_QUOTES, 'UTF-8') ?>" class="card-img-top" style="height: 100px; object-fit: contain;">
                                <div class="card-body p-2">
                                    <p class="card-text small text-truncate" title="<?= htmlspecialchars($asset['filename'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($asset['filename'], ENT_QUOTES, 'UTF-8') ?></p>
                                    <form action="admin.php?action=delete_asset" method="post" onsubmit="return confirm('このアセットを完全に削除しますか？');">
                                        <input type="hidden" name="work_id" value="<?= htmlspecialchars($work['work_id'], ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="asset_path" value="<?= htmlspecialchars($asset['server_path'], ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">削除</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="asset_upload" class="form-label">アセットの追加アップロード</label>
                    <input class="form-control" type="file" id="asset_upload" name="assets[]" multiple>
                    <div class="form-text">複数ファイルの選択が可能です。</div>
                    <div class="form-text mt-1">
                        <small class="text-muted">
                            ファイル名規則: 「作品名(英数表記)_vis0x」「作品名(英数表記)_logo0x」
                        </small>
                    </div>
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