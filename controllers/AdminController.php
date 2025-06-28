<?php
class AdminController {
    private $dataManager;

    public function __construct() {
        $this->dataManager = new DataManager();
    }

    public function dashboard() {
        // 1. ページネーションの準備
        $works_per_page = 20;
        $all_works_for_count = $this->dataManager->getWorks(null, null, null, null);
        $total_works = count($all_works_for_count);
        $total_pages = $total_works > 0 ? ceil($total_works / $works_per_page) : 1;
        $current_page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
        if ($current_page < 1) {
            $current_page = 1;
        }

        // 2. フィルタとソートの準備
        $current_filter_category = isset($_GET['filter_category']) ? $_GET['filter_category'] : null;
        $current_search_keyword = isset($_GET['search']) && trim($_GET['search']) !== '' ? trim($_GET['search']) : null;
        $current_sort_key = isset($_GET['sort']) ? $_GET['sort'] : 'open';
        $current_sort_order = isset($_GET['order']) ? $_GET['order'] : 'desc';

        // 3. 表示する作品データを取得
        $all_filtered_works = $this->dataManager->getWorks($current_filter_category, $current_search_keyword, $current_sort_key, $current_sort_order);
        
        $offset = ($current_page - 1) * $works_per_page;
        $works_for_display = array_slice($all_filtered_works, $offset, $works_per_page);
        
        // 4. カテゴリ情報を取得
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

        // 5. ビューに変数を渡して表示
        $this->loadView('dashboard', array(
            'title' => '管理ダッシュボード',
            'works' => $works_for_display,
            'categories' => $categories,
            'category_work_counts' => $category_work_counts,
            'total_pages' => (int)$total_pages,
            'current_page' => (int)$current_page,
            'current_sort_key' => $current_sort_key,
            'current_sort_order' => $current_sort_order,
            'current_filter_category' => $current_filter_category,
            'current_search_keyword' => $current_search_keyword
        ));
    }

    public function addWork() {
        $data['title'] = '作品の新規追加';
        $data['work'] = array(
            'work_id' => '', 'title' => '', 'title_ruby' => '',
            'author' => '', 'author_ruby' => '', 'category_id' => '',
            'comment' => '', 'title_id' => '', 'directory_name' => '',
            'copyright' => '', 'open' => ''
        );
        $data['categories'] = $this->dataManager->getCategories();
        $this->loadView('edit_work_form', $data);
    }

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
        if (!$work_id) {
            header('Location: admin.php');
            exit;
        }
        $work = $this->dataManager->getWorkById($work_id);
        if (!$work) {
            die('指定された作品が見つかりません。');
        }
        $data['title'] = '作品の編集: ' . htmlspecialchars($work['title']);
        $data['work'] = $work;
        $data['categories'] = $this->dataManager->getCategories();
        $this->loadView('edit_work_form', $data);
    }

    public function saveWork($postData) {
        $work_id = isset($postData['work_id']) ? $postData['work_id'] : null;
        if (!$work_id) {
            die('Error: work_idが見つかりません。');
        }
        $success = $this->dataManager->updateWork($work_id, $postData);
        if ($success) {
            header('Location: admin.php?action=dashboard');
            exit;
        } else {
            die('Error: データの保存に失敗しました。');
        }
    }

    public function deleteWork($work_id) {
        if (!$work_id) {
            die('Error: work_idが見つかりません。');
        }
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
            $data['category'] = array(
                'id' => '', 'name' => '', 'alias' => '', 'directory_name' => '', 'title_count' => 0
            );
        } else {
            $category = $this->dataManager->getCategoryById($category_id);
            if (!$category) {
                die('指定されたカテゴリが見つかりません。');
            }
            $data['title'] = 'カテゴリの編集: ' . htmlspecialchars($category['name']);
            $data['category'] = $category;
        }
        $this->loadView('edit_category_form', $data);
    }

    public function createCategory($postData) {
        $success = $this->dataManager->addCategory($postData);
        if ($success) {
            header('Location: admin.php?action=dashboard');
            exit;
        } else {
            die('Error: カテゴリの追加に失敗しました。');
        }
    }

    public function saveCategory($postData) {
        $category_id = isset($postData['id']) ? $postData['id'] : null;
        if (!$category_id) {
            die('Error: カテゴリIDが見つかりません。');
        }

        $success = $this->dataManager->updateCategory($category_id, $postData);
        if ($success) {
            header('Location: admin.php?action=dashboard');
            exit;
        } else {
            die('Error: カテゴリの更新に失敗しました。');
        }
    }
    
    private function loadView($viewName, $data = array()) {
        extract($data, EXTR_SKIP);
        $baseDir = BASE_DIR_PATH . '/views/admin/';
        require_once $baseDir . "layouts/header.php";
        require_once $baseDir . "{$viewName}.php";
        require_once $baseDir . "layouts/footer.php";
    }
}