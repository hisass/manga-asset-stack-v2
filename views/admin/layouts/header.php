<!DOCTYPE html>
<html lang="ja" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title, ENT_QUOTES, 'UTF-8') : 'ダッシュボード'; ?> - Manga Asset Stack v2 Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.php">

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="admin.php" id="site-logo">
            Manga Asset Stack
            <small class="text-muted" style="font-size: 0.8rem;">[Admin]</small>
        </a>
        
        <button id="theme-toggle-btn" class="btn btn-outline-light ms-auto">
            <i class="bi bi-moon-stars-fill"></i>
        </button>
    </div>
</nav>

<main class="container my-4">