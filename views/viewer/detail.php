<?php
/**
 * views/viewer/detail.php
 * 作品詳細ページのテンプレート
 */
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            
            <div class="d-flex align-items-center mb-2">
                <h1 class="h2 mb-0 me-3"><strong><?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8'); ?></strong></h1>
                <button class="btn btn-sm btn-outline-secondary btn-copy" data-copy-text="<?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?>">COPY</button>
            </div>

            <div class="d-flex align-items-center mb-2">
                <span class="me-3">著者: <a href="index.php?page=author&name=<?= urlencode($work['author']) ?>"><?= htmlspecialchars($work['author'], ENT_QUOTES, 'UTF-8'); ?></a></span>
                <button class="btn btn-sm btn-outline-secondary btn-copy" data-copy-text="<?= htmlspecialchars($work['author'], ENT_QUOTES, 'UTF-8') ?>">COPY</button>
            </div>
            
            <div class="mb-4">
                <?php if (isset($category_name) && !empty($work['category_id'])): ?>
                    <p class="mb-0 text-muted"><small>カテゴリ: <a href="index.php?page=category&id=<?= urlencode($work['category_id']) ?>"><?= htmlspecialchars($category_name, ENT_QUOTES, 'UTF-8'); ?></a></small></p>
                <?php endif; ?>
            </div>

            <div class="d-flex align-items-center mb-4 pt-3 border-top">
                 <?php
                $copyright_text = $work['copyright'];
                if (mb_strpos($copyright_text, '©') !== 0) {
                    $copyright_text = '© ' . $copyright_text;
                }
                ?>
                <span class="me-3 text-muted"><small><?= htmlspecialchars($copyright_text, ENT_QUOTES, 'UTF-8'); ?></small></span>
                <button class="btn btn-sm btn-outline-secondary btn-copy" data-copy-text="<?= htmlspecialchars($copyright_text, ENT_QUOTES, 'UTF-8') ?>">COPY</button>
            </div>

            <?php if (!empty($work['comment'])): ?>
                <div class="mb-4">
                    <div class="p-3 bg-light rounded">
                        <?= nl2br(htmlspecialchars(trim($work['comment']), ENT_QUOTES, 'UTF-8')); ?>
                    </div>
                </div>
            <?php endif; ?>

            <p class="text-muted small">
                タイトルID: <?= !empty($work['title_id']) ? htmlspecialchars($work['title_id'], ENT_QUOTES, 'UTF-8') : '未登録'; ?>
                <?php if (!empty($last_updated)): ?>
                    <br>最終更新日時: <?= htmlspecialchars($last_updated, ENT_QUOTES, 'UTF-8'); ?>
                <?php endif; ?>
            </p>

            <?php if (!empty($assets)): ?>
            <div class="d-grid gap-2 my-4">
                <a href="download.php?work_id=<?= urlencode($work['work_id']) ?>" class="btn btn-primary btn-lg">
                    全アセットをダウンロード
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
                                <a href="#" class="js-modal-trigger" data-url="<?= htmlspecialchars($asset['url'], ENT_QUOTES, 'UTF-8'); ?>" data-filename="<?= htmlspecialchars($asset['filename'], ENT_QUOTES, 'UTF-8'); ?>" title="画像を拡大表示">
                                    <img src="<?= htmlspecialchars($asset['url'], ENT_QUOTES, 'UTF-8'); ?>" class="card-img-top" alt="アセット画像" style="object-fit: contain; height: 150px;">
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
  <div class="image-modal-content-container">
    <div id="modal-caption" class="image-modal-caption"></div>
    <img class="image-modal-content" id="modalImage">
  </div>
  <span class="image-modal-close">&times;</span>
</div>

<style>
.image-modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.85); -webkit-animation-name: fadeIn; animation-name: fadeIn; -webkit-animation-duration: 0.4s; animation-duration: 0.4s; }
.image-modal.is-open { display: flex; align-items: center; justify-content: center; }
.image-modal-content-container { position: relative; display: flex; flex-direction: column; width: auto; max-width: 50%; max-height: 80vh; }
.image-modal-content { display: block; max-width: 100%; max-height: 100%; object-fit: contain; }
.image-modal-caption { background-color: rgba(10, 10, 10, 0.9); color: #fff; padding: 10px 45px 10px 15px; text-align: left; font-size: 0.9rem; border-bottom: 1px solid #555; word-break: break-all; }
.image-modal-close { position: absolute; top: 0; right: 0; padding: 5px 15px; color: #fff; font-size: 30px; line-height: 1; font-weight: bold; transition: 0.3s; cursor: pointer; z-index: 1060; background-color: rgba(10, 10, 10, 0.9); }
.image-modal-close:hover, .image-modal-close:focus { color: #bbb; text-decoration: none; }
@-webkit-keyframes fadeIn { from {opacity: 0} to {opacity: 1} }
@keyframes fadeIn { from {opacity: 0} to {opacity: 1} }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('imageModal');
    if (modal) {
        var modalImg = document.getElementById("modalImage");
        var captionText = document.getElementById("modal-caption");
        var closeBtn = document.querySelector(".image-modal-close");

        function openModal(imageUrl, filename) {
            if (!modalImg || !captionText) return;
            modal.classList.add('is-open');
            modalImg.src = imageUrl;
            captionText.innerHTML = filename;
        }
        function closeModal() {
            modal.classList.remove('is-open');
        }

        var triggers = document.querySelectorAll('.js-modal-trigger');
        triggers.forEach(function(trigger) {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                var imageUrl = this.getAttribute('data-url');
                var filename = this.getAttribute('data-filename');
                openModal(imageUrl, filename);
            });
        });
        if(closeBtn) closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function(event) {
            if (event.target === modal) { closeModal(); }
        });
        document.addEventListener('keydown', function(event) {
            if ((event.key === 'Escape' || event.key === 'Esc') && modal.classList.contains('is-open')) {
                closeModal();
            }
        });
    }
    
    // ▼▼▼ COPYボタン用のJavaScript ▼▼▼
    var copyButtons = document.querySelectorAll('.btn-copy');
    copyButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var textToCopy = this.getAttribute('data-copy-text');
            var originalText = this.textContent;
            navigator.clipboard.writeText(textToCopy).then(() => {
                this.textContent = 'Copied!';
                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-success');
                setTimeout(() => {
                    this.textContent = originalText;
                    this.classList.remove('btn-success');
                    this.classList.add('btn-outline-secondary');
                }, 2000);
            }, (err) => {
                this.textContent = 'Error';
                setTimeout(() => { this.textContent = originalText; }, 2000);
            });
        });
    });
});
</script>