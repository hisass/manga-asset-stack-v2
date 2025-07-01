<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once BASE_DIR_PATH . '/models/DataManager.php';
require_once BASE_DIR_PATH . '/controllers/WorkController.php';

$dataManager = new DataManager();
$controller = new WorkController($dataManager);

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

switch ($page) {
    case 'detail':
        $work_id = isset($_GET['id']) ? $_GET['id'] : null;
        $controller->detail($work_id);
        break;

    case 'category':
        $category_id = isset($_GET['id']) ? $_GET['id'] : null;
        $controller->categoryPage($category_id);
        break;
    
    // ▼▼▼ このcaseを追加 ▼▼▼
    case 'author':
        $author_name = isset($_GET['name']) ? $_GET['name'] : null;
        $controller->authorPage($author_name);
        break;
    // ▲▲▲ ここまでを追加 ▲▲▲

    case 'new':
        $controller->newArrivals();
        break;

    case 'home':
    default:
        $controller->home();
        break;
}