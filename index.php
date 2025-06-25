<?php
// ▼▼▼ この3行をファイルの先頭に追加しました ▼▼▼
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ▲▲▲ ここまで ▲▲▲

require_once __DIR__ . '/config.php';
require_once BASE_DIR_PATH . '/models/DataManager.php';
require_once BASE_DIR_PATH . '/controllers/WorkController.php';

// DataManagerは両方のコントローラで必要になる可能性がある
$dataManager = new DataManager();
$controller = new WorkController($dataManager);

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

switch ($page) {
    case 'detail':
        $work_id = isset($_GET['id']) ? $_GET['id'] : null;
        $controller->detail($work_id);
        break;
    
    // カテゴリページや検索ページも今後ここに追加
    
    case 'home':
    default:
        $controller->home();
        break;
}