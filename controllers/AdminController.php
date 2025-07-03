<?php
class AdminController {
    private $dataManager;

    public function __construct() {
        $this->dataManager = new DataManager();
    }

    public function dashboard() {
        $works_per_page = 20;
        $all_works_for_count = $this->dataManager->getWorks(null, null, null);
        $total_works = count($all_works_for_count);
        $total_pages = $total_works > 0 ? ceil($total_works / $works_per_page) : 1;
        $current_page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
        if ($current_page < 1) { $current_page = 1; }
        $current_filter_category = isset($_GET['filter_category']) ? $_GET['filter_category'] : null;
        $current_search_keyword = isset($_GET['search']) && trim($_GET['search']) !== '' ? trim($_GET['search']) : null;
        $current_sort_key = isset($_GET['sort']) ? $_GET['sort'] : 'open';
        $current_sort_order = isset($_GET['order']) ? $_GET['order'] : 'desc';
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'works';
        $all_filtered_works = $this->dataManager->getWorks($current_filter_category, $current_search_keyword, $current_sort_key . '_' . $current_sort_order);
        $offset = ($current_page - 1) * $works_per_page;
        $works_for_display = array_slice($all_filtered_works, $offset, $works_per_page);
        $categories = $this->dataManager->getCategories();
        $category_work_counts = array();
        foreach ($categories as $category) {
            $category_work_counts[$category['id']] = 0;
        }
        foreach ($all_works_for_count as $work) {
            if (isset($work['category_id']) && isset($category_work_counts[$work['category_id']])) {
                $category_work_counts[$work['category_id']]++;
            }
        }
        $this->loadView('dashboard', array(
            'title' => '管理ダッシュボード', 'works' => $works_for_display, 'categories' => $categories,
            'category_work_counts' => $category_work_counts, 'total_pages' => (int)$total_pages,
            'current_page' => (int)$current_page, 'current_sort_key' => $current_sort_key,
            'current_sort_order' => $current_sort_order, 'current_filter_category' => $current_filter_category,
            'current_search_keyword' => $current_search_keyword, 'active_tab' => $active_tab
        ));
    }

    // ▼▼▼ このメソッドを修正 ▼▼▼
    public function addWork() {
        $data['title'] = '作品の新規追加';
        $data['work'] = array(
            'work_id' => '', 'title' => '', 'title_ruby' => '', 'author' => '', 'author_ruby' => '',
            'category_id' => '', 'comment' => '', 'title_id' => '', 'directory_name' => '',
            'copyright' => '', 'open' => date('Y-m-d') // 今日の日付をデフォルト値に設定
        );
        $data['categories'] = $this->dataManager->getCategories();
        $this->loadView('edit_work_form', $data);
    }
    // ▲▲▲ ここまでを修正 ▲▲▲

    public function createWork($postData) {
        $success = $this->dataManager->addWork($postData);
        if ($success) {
            header('Location: admin.php?action=dashboard');
            exit;
        } else {
            die('Error: データの追加に失敗しました。');
        }
    }

    public function editWork($work_id) {
        if (!$work_id) { header('Location: admin.php'); exit; }
        $work = $this->dataManager->getWorkById($work_id);
        if (!$work) { die('指定された作品が見つかりません。'); }
        $data['title'] = '作品の編集: ' . htmlspecialchars($work['title']);
        $data['work'] = $work;
        $data['categories'] = $this->dataManager->getCategories();
        $this->loadView('edit_work_form', $data);
    }

    public function saveWork($postData) {
        $work_id = isset($postData['work_id']) ? $postData['work_id'] : null;
        if (!$work_id) { die('Error: work_idが見つかりません。'); }
        $success = $this->dataManager->updateWork($work_id, $postData);
        if ($success) {
            header('Location: admin.php?action=dashboard');
            exit;
        } else {
            die('Error: データの保存に失敗しました。');
        }
    }

    public function deleteWork($work_id) {
        if (!$work_id) { die('Error: work_idが見つかりません。'); }
        $success = $this->dataManager->deleteWork($work_id);
        if ($success) {
            header('Location: admin.php?action=dashboard');
            exit;
        } else {
            die('Error: データの削除に失敗しました。');
        }
    }

    public function editCategory($category_id = null) {
        $is_new = ($category_id === null);
        if ($is_new) {
            $data['title'] = 'カテゴリの新規追加';
            $data['category'] = array( 'id' => '', 'name' => '', 'alias' => '', 'directory_name' => '', 'title_count' => 0 );
        } else {
            $category = $this->dataManager->getCategoryById($category_id);
            if (!$category) { die('指定されたカテゴリが見つかりません。'); }
            $data['title'] = 'カテゴリの編集: ' . htmlspecialchars($category['name']);
            $data['category'] = $category;
        }
        $this->loadView('edit_category_form', $data);
    }

    public function createCategory($postData) {
        $success = $this->dataManager->addCategory($postData);
        if ($success) {
            header('Location: admin.php?action=dashboard&tab=categories');
            exit;
        } else {
            die('Error: カテゴリの追加に失敗しました。');
        }
    }

    public function saveCategory($postData) {
        $category_id = isset($postData['id']) ? $postData['id'] : null;
        if (!$category_id) { die('Error: カテゴリIDが見つかりません。'); }
        $success = $this->dataManager->updateCategory($category_id, $postData);
        if ($success) {
            header('Location: admin.php?action=dashboard&tab=categories');
            exit;
        } else {
            die('Error: カテゴリの更新に失敗しました。');
        }
    }
    
    public function moveCategory($category_id, $direction) {
        if (!$category_id || !$direction) {
            header('Location: admin.php?action=dashboard&tab=categories');
            exit;
        }
        $order_file_path = BASE_DIR_PATH . '/data/categories_order.json';
        if (!file_exists($order_file_path)) {
            die('Error: categories_order.json が見つかりません。');
        }
        $ordered_ids = json_decode(file_get_contents($order_file_path), true);
        $index = array_search($category_id, $ordered_ids);
        if ($index !== false) {
            if ($direction === 'up' && $index > 0) {
                $temp = $ordered_ids[$index - 1];
                $ordered_ids[$index - 1] = $ordered_ids[$index];
                $ordered_ids[$index] = $temp;
            } elseif ($direction === 'down' && $index < count($ordered_ids) - 1) {
                $temp = $ordered_ids[$index + 1];
                $ordered_ids[$index + 1] = $ordered_ids[$index];
                $ordered_ids[$index] = $temp;
            }
        }
        file_put_contents($order_file_path, json_encode($ordered_ids));
        header('Location: admin.php?action=dashboard&tab=categories');
        exit;
    }

    private function loadView($viewName, $data = array()) {
        extract($data, EXTR_SKIP);
        $baseDir = BASE_DIR_PATH . '/views/admin/';
        header('Content-Type: text/html; charset=utf-8');
        require_once $baseDir . "layouts/header.php";
        require_once $baseDir . "{$viewName}.php";
        require_once $baseDir . "layouts/footer.php";
    }
}