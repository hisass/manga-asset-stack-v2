<!DOCTYPE html>
<html lang="ja" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title, ENT_QUOTES, 'UTF-8') : 'Manga Asset Stack' ?> - Manga Asset Stack v2</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            padding-top: 124px; 
            /* ▼▼▼ フッターの高さ分の余白を再度設定 ▼▼▼ */
            padding-bottom: 80px; 
        }
        .navbar.bg-dark {
            min-height: 84px;
        }
        .navbar-brand img {
            height: 28px;
            width: auto;
        }
    </style>
</head>
<body>

<header class="fixed-top">
    <nav class="navbar navbar-expand navbar-dark bg-dark align-items-center">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="<?php echo BASE_URL; ?>/assets/images/Manga-Asset-Stack-Logo.svg" alt="Manga Asset Stack Logo">
            </a>
            <div class="d-flex ms-auto align-items-center">
                <form class="d-flex me-3" method="GET" action="index.php">
                    <input type="hidden" name="page" value="search">
                    <input class="form-control me-2" type="search" name="keyword" placeholder="キーワード検索" aria-label="Search">
                    <button class="btn btn-outline-success" type="submit">検索</button>
                </form>
                <button class="btn btn-outline-light" id="theme-toggle-btn" type="button" title="テーマを切り替え">
                    <svg id="theme-icon-sun" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sun-fill" viewBox="0 0 16 16"><path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707z"/></svg>
                    <svg id="theme-icon-moon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-stars-fill d-none" viewBox="0 0 16 16"><path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/><path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162h1.214a.217.217 0 0 1 .158.37l-1.03.752.387 1.162a.217.217 0 0 1-.316.242l-1.03-.752-1.03.752a.217.217 0 0 1-.316-.242l.387-1.162-1.03-.752a.217.217 0 0 1 .158-.37h1.214l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a.752.752 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a.752.752 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774.258c.346-.115.617-.386.732-.732L13.863.1z"/></svg>
                </button>
            </div>
        </div>
    </nav>
    <nav class="navbar navbar-expand-lg bg-body-secondary py-1 category-nav">
        <div class="container-fluid">
            <div class="collapse navbar-collapse justify-content-center">
                <ul class="navbar-nav">
                    <?php if (!empty($all_categories)): ?>
                        <?php foreach ($all_categories as $category): ?>
                            <li class="nav-item">
                                <?php
                                    $link_url = isset($category['url']) 
                                                ? $category['url'] 
                                                : 'index.php?page=category&id=' . urlencode($category['id']);
                                ?>
                                <a class="nav-link small" href="<?= $link_url ?>">
                                    <?php
                                        if ($category['id'] === 'new') {
                                            echo $category['name']; 
                                        } else {
                                            echo htmlspecialchars(!empty($category['alias']) ? $category['alias'] : $category['name'], ENT_QUOTES, 'UTF-8');
                                        }
                                    ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
<main class="container mt-4">