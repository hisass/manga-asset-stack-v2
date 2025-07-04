<?php
// ▼▼▼ このエラーログ設定をファイルの先頭に追加 ▼▼▼
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');
error_reporting(E_ALL);
// ▲▲▲ ここまで ▲▲▲

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/config.php';
require_once BASE_DIR_PATH . '/models/DataManager.php';
require_once BASE_DIR_PATH . '/controllers/AdminController.php';

$controller = new AdminController();
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

switch ($action) {
    case 'add_work': $controller->addWork(); break;
    case 'create_work': $controller->createWork($_POST); break;
    case 'edit_work': $controller->editWork(isset($_GET['id']) ? $_GET['id'] : null); break;
    case 'save_work': $controller->saveWork($_POST); break;
    case 'delete_work': $controller->deleteWork(isset($_GET['id']) ? $_GET['id'] : null); break;
    case 'edit_category': $controller->editCategory(isset($_GET['id']) ? $_GET['id'] : null); break;
    case 'create_category': $controller->createCategory($_POST); break;
    case 'save_category': $controller->saveCategory($_POST); break;
    case 'move_category': $controller->moveCategory(isset($_GET['id']) ? $_GET['id'] : null, isset($_GET['direction']) ? $_GET['direction'] : null); break;
    case 'delete_asset': $controller->deleteAsset(); break;
    case 'dashboard': default: $controller->dashboard(); break;
}