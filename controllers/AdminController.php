<?php
class AdminController {
    private $dataManager;

    public function __construct() {
        $this->dataManager = new DataManager();
    }

    public function dashboard() {
        $data['title'] = '管理ダッシュボード';

        // 1. パラメータを受け取る
        $page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
        $items_per_page = 20; // 1ページの表示件数
        $sort_key = isset($_GET['sort']) ? $_GET['sort'] : 'open'; // デフォルトは公開日
        $sort_order = isset($_GET['order']) ? $_GET['order'] : 'desc'; // デフォルトは降順

        // 2.【修正】有効な作品の総数を取得する
        $total_works = $this->dataManager->getValidWorkCount();
        $total_pages = ceil($total_works / $items_per_page);
        
        // 3. 表示する作品データを取得する
        // 全作品を取得してから、カテゴリでフィルタリングし、ソートし、ページで切り出す
        $all_works = $this->dataManager->getWorks($sort_key, $sort_order, 9999, 0);

        $categories = $this->dataManager->getCategories();
        $valid_category_ids = array();
        foreach ($categories as $category) {
            $valid_category_ids[] = $category['id'];
        }

        // カテゴリを持つ作品だけをフィルタリング
        $valid_works = array();
        foreach ($all_works as $work) {
            if (isset($work['category_id']) && in_array($work['category_id'], $valid_category_ids)) {
                $valid_works[] = $work;
            }
        }
        
        // ページに表示する分だけを切り出す
        $offset = ($page_num - 1) * $items_per_page;
        $works_for_page = array_slice($valid_works, $offset, $items_per_page);

        // 4. カテゴリごとの作品数を集計する
        $category_work_counts = array();
        foreach ($categories as $category) {
            $category_work_counts[$category['id']] = 0;
        }
        foreach ($valid_works as $work) {
            $category_work_counts[$work['category_id']]++;
        }
        
        // 5. ビューに渡すデータをセットする
        $data['categories'] = $categories;
        $data['works'] = $works_for_page; // ページ分割された作品
        $data['category_work_counts'] = $category_work_counts;
        $data['total_pages'] = $total_pages;
        $data['current_page'] = $page_num;
        $data['current_sort_key'] = $sort_key;
        $data['current_sort_order'] = $sort_order;
        
        $this->loadView('dashboard', $data);
    }

    public function addWork() {
        $data['title'] = '作品の新規追加';
        $data['work'] = array(
            'work_id' => '', 'title' => '', 'title_ruby' => '',
            'author' => '', 'author_ruby' => '', 'category_id' => '',
            'comment' => ''
        );
        $data['categories'] = $this->dataManager->getCategories();
        $this->loadView('edit_work_form', $data);
    }

    public function createWork($postData) {
        $work_id = isset($postData['work_id']) ? trim($postData['work_id']) : '';
        if (empty($work_id)) {
            die('Error: 作品IDは必須です。');
        }
        $success = $this->dataManager->addWork($postData);
        if ($success) {
            header('Location: admin.php?action=dashboard');
            exit;
        } else {
            die('Error: データの追加に失敗しました。作品IDが既に存在している可能性があります。');
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