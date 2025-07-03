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
        }
        .navbar.bg-dark {
            min-height: 84px;
        }
        .navbar-brand img {
            max-height: 28px;
        }
    </style>
</head>
<body class="d-flex flex-column vh-100">

<header class="fixed-top">
    <nav class="navbar navbar-expand navbar-dark bg-dark align-items-center">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/images/logo.svg" alt="サイトロゴ">
            </a>
            <div class="d-flex ms-auto">
                <form class="d-flex me-3" method="GET" action="index.php">
                    <input type="hidden" name="page" value="search">
                    <input class="form-control me-2" type="search" name="keyword" placeholder="キーワード検索" aria-label="Search">
                    <button class="btn btn-outline-success" type="submit">検索</button>
                </form>
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
<main class="container mt-4 flex-grow-1">