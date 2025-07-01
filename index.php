<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once BASE_DIR_PATH . '/models/DataManager.php';
require_once BASE_DIR_PATH . '/controllers/WorkController.php';

// DataManagerとWorkControllerをインスタンス化
$dataManager = new DataManager();
$controller = new WorkController($dataManager);

// URLの?page=...の値によって呼び出すメソッドを切り替える
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

switch ($page) {
    case 'detail':
        $work_id = isset($_GET['id']) ? $_GET['id'] : null;
        $controller->detail($work_id);
        break;

    // ▼▼▼ この2つのcaseを追加 ▼▼▼
    case 'category':
        $category_id = isset($_GET['id']) ? $_GET['id'] : null;
        $controller->categoryPage($category_id);
        break;

    case 'new':
        $controller->newArrivals();
        break;
    // ▲▲▲ ここまでを追加 ▲▲▲

    case 'home':
    default:
        $controller->home();
        break;
}