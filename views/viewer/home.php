<?php foreach ($all_categories as $category): ?>
    <?php if (!empty($works_by_category[$category['id']])): ?>
        <section class="mb-5">
            <h2><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <hr>
            <div class="row g-3">
                <?php foreach ($works_by_category[$category['id']] as $work): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="index.php?page=detail&id=<?= urlencode($work['work_id']) ?>" class="text-decoration-none text-dark">
                            <div class="card h-100">
                                <div class="card-body">
                                    <span class="fw-bold"><?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <br>
                                    <small><?= htmlspecialchars($work['author'], ENT_QUOTES, 'UTF-8') ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
<?php endforeach; ?>