<?php
/**
 * views/viewer/detail.php
 * 作品詳細ページのテンプレート
 */
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            
            <h1 class="h2 border-bottom pb-3 mb-3"><?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
            
            <div class="mb-4 text-muted">
                <?php if (isset($category_name) && !empty($work['category_id'])): ?>
                    カテゴリ: <a href="index.php?page=category&id=<?= urlencode($work['category_id']) ?>"><?= htmlspecialchars($category_name, ENT_QUOTES, 'UTF-8'); ?></a>
                <?php endif; ?>
                <br>
                著者: <a href="index.php?page=author&name=<?= urlencode($work['author']) ?>"><?= htmlspecialchars($work['author'], ENT_QUOTES, 'UTF-8'); ?></a>
            </div>

            <?php if (!empty($work['comment'])): ?>
                <div class="mb-4">
                    <strong>コメント:</strong>
                    <ul class="list-unstyled mt-2">
                        <?php 
                        $comments = explode("\n", trim($work['comment']));
                        foreach ($comments as $comment_line):
                        ?>
                            <li>・<?= htmlspecialchars($comment_line, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <p class="text-muted border-top pt-3">
                <small>
                    Copyright: <?= htmlspecialchars($work['copyright'], ENT_QUOTES, 'UTF-8'); ?>
                    <?php if (!empty($work['title_id'])): ?>
                        | タイトルID: <?= htmlspecialchars($work['title_id'], ENT_QUOTES, 'UTF-8'); ?>
                    <?php endif; ?>
                </small>
            </p>

            <?php if (!empty($assets)): ?>
            <div class="d-grid gap-2 my-4">
                <a href="download.php?work_id=<?= urlencode($work['work_id']) ?>" class="btn btn-primary btn-lg">
                    この素材をまとめてダウンロード
                </a>
            </div>
            <?php endif; ?>
            <h3 class="mt-5">画像アセット一覧</h3>
            <hr>
            <?php if (empty($assets)): ?>
                <p>利用可能なアセットはありません。</p>
            <?php else: ?>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
                    <?php foreach ($assets as $asset): ?>
                        <div class="col">
                            <div class="card h-100">
                                <a href="#" onclick="openModal('<?= htmlspecialchars($asset['url'], ENT_QUOTES, 'UTF-8'); ?>'); return false;" title="画像を拡大表示">
                                    <img src="<?= htmlspecialchars($asset['url'], ENT_QUOTES, 'UTF-8'); ?>" class="card-img-top" alt="アセット画像" style="object-fit: contain; height: 150px; background-color: #f8f9fa;">
                                </a>
                                <div class="card-body p-2">
                                    <p class="card-text small mb-1 text-truncate">
                                        <a href="<?= htmlspecialchars($asset['url'], ENT_QUOTES, 'UTF-8'); ?>" download>
                                            <?= htmlspecialchars($asset['filename'], ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    </p>
                                    <p class="card-text text-muted small mb-0"><?= htmlspecialchars($asset['size_str'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="card-text text-muted small mb-0"><?= htmlspecialchars($asset['date_str'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="imageModal" class="image-modal">
  <span class="image-modal-close" onclick="closeModal()">&times;</span>
  <img class="image-modal-content" id="modalImage">
</div>

<style>
.image-modal { display: none; position: fixed; z-index: 1050; padding-top: 50px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.9); -webkit-animation-name: fadeIn; animation-name: fadeIn; -webkit-animation-duration: 0.4s; animation-duration: 0.4s }
.image-modal-content { margin: auto; display: block; max-width: 90%; max-height: 90vh; object-fit: contain; }
.image-modal-close { position: absolute; top: 15px; right: 35px; color: #fff; font-size: 40px; font-weight: bold; transition: 0.3s; cursor: pointer; }
.image-modal-close:hover, .image-modal-close:focus { color: #bbb; text-decoration: none; }
@-webkit-keyframes fadeIn { from {opacity: 0} to {opacity: 1} }
@keyframes fadeIn { from {opacity: 0} to {opacity: 1} }
</style>

<script>
var modal = document.getElementById('imageModal');
var modalImg = document.getElementById("modalImage");
function openModal(imageUrl) {
  modal.style.display = "flex";
  modalImg.src = imageUrl;
}
function closeModal() {
  modal.style.display = "none";
}
window.onclick = function(event) {
  if (event.target == modal) {
    closeModal();
  }
}
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});
</script>