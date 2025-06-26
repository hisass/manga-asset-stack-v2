<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once BASE_DIR_PATH . '/models/DataManager.php';
require_once BASE_DIR_PATH . '/controllers/AdminController.php';

$controller = new AdminController();

$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

switch ($action) {
    case 'add_work':
        $controller->addWork();
        break;

    case 'create_work':
        $controller->createWork($_POST);
        break;

    case 'edit_work':
        $work_id = isset($_GET['id']) ? $_GET['id'] : null;
        $controller->editWork($work_id);
        break;

    case 'save_work':
        $controller->saveWork($_POST);
        break;

    case 'delete_work':
        $work_id = isset($_GET['id']) ? $_GET['id'] : null;
        $controller->deleteWork($work_id);
        break;

    case 'edit_category': // ▼▼▼ この3行を追加 ▼▼▼
        $category_id = isset($_GET['id']) ? $_GET['id'] : null;
        $controller->editCategory($category_id);
        break;
        
    case 'dashboard':
    default:
        $controller->dashboard();
        break;
}