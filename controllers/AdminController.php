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
        $items_per_page = 20;
        $sort_key = isset($_GET['sort']) ? $_GET['sort'] : 'open';
        $sort_order = isset($_GET['order']) ? $_GET['order'] : 'desc';
        $filter_category = isset($_GET['filter_category']) ? $_GET['filter_category'] : null;
        $search_keyword = isset($_GET['search']) && trim($_GET['search']) !== '' ? trim($_GET['search']) : null;

        // 2. フィルタリングとソートをして作品リストを取得
        $all_filtered_works = $this->dataManager->getWorks($filter_category, $search_keyword, $sort_key, $sort_order);

        // 3. ページネーションを計算
        $total_works = count($all_filtered_works);
        $total_pages = ceil($total_works / $items_per_page);
        
        // 4. 現在のページに表示する分だけを切り出す
        $offset = ($page_num - 1) * $items_per_page;
        $works_for_page = array_slice($all_filtered_works, $offset, $items_per_page);

        // 5. カテゴリごとの作品数を集計する (これは全作品から)
        $all_works = $this->dataManager->getWorks(); // 集計用に全件取得
        $categories = $this->dataManager->getCategories();
        $category_work_counts = array();
        foreach ($categories as $category) {
            $category_work_counts[$category['id']] = 0;
        }
        foreach ($all_works as $work) {
            if (isset($work['category_id']) && isset($category_work_counts[$work['category_id']])) {
                $category_work_counts[$work['category_id']]++;
            }
        }
        
        // 6. ビューに渡すデータをセット
        $data['categories'] = $categories;
        $data['works'] = $works_for_page;
        $data['category_work_counts'] = $category_work_counts;
        $data['total_pages'] = $total_pages;
        $data['current_page'] = $page_num;
        $data['current_sort_key'] = $sort_key;
        $data['current_sort_order'] = $sort_order;
        $data['current_filter_category'] = $filter_category;
        $data['current_search_keyword'] = $search_keyword;
        
        $this->loadView('dashboard', $data);
    }

    // ... (他のメソッドは変更なし) ...
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