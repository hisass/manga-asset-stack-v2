<?php
class AdminController {
    private $dataManager;

    public function __construct() {
        $this->dataManager = new DataManager();
    }

    // dashboardメソッドを、このデバッグ用コードで丸ごと置き換えてください
    public function dashboard() {
        echo "<h1>デバッグモード</h1>";

        // --- ステップ1：ソートされた全作品を取得 ---
        $sort_key = isset($_GET['sort']) ? $_GET['sort'] : 'open';
        $sort_order = isset($_GET['order']) ? $_GET['order'] : 'desc';
        $all_works = $this->dataManager->getWorks($sort_key, $sort_order, 9999, 0);
        echo "<h2>ステップ1：全作品データの取得</h2>";
        echo "<p>取得した全作品数 (カテゴリ問わず): " . count($all_works) . "件</p>";
        echo "<hr>";

        // --- ステップ2：有効なカテゴリIDのリストを作成 ---
        $categories = $this->dataManager->getCategories();
        $valid_category_ids = array();
        foreach ($categories as $category) {
            $valid_category_ids[] = $category['id'];
        }
        echo "<h2>ステップ2：有効なカテゴリの確認</h2>";
        echo "<p>有効なカテゴリIDのリスト：</p>";
        echo "<pre>";
        print_r($valid_category_ids);
        echo "</pre>";
        echo "<hr>";

        // --- ステップ3：カテゴリを持つ作品だけをフィルタリング ---
        $valid_works = array();
        foreach ($all_works as $work) {
            if (isset($work['category_id']) && in_array($work['category_id'], $valid_category_ids)) {
                $valid_works[] = $work;
            }
        }
        echo "<h2>ステップ3：有効な作品のフィルタリング</h2>";
        echo "<p>フィルタリング後の有効な作品数: " . count($valid_works) . "件</p>";
        echo "<hr>";

        // --- ステップ4：ページネーションの計算 ---
        $total_works = count($valid_works);
        $items_per_page = 20;
        $total_pages = ceil($total_works / $items_per_page);
        echo "<h2>ステップ4：ページネーションの最終計算</h2>";
        echo "<p>計算に使用する総作品数: " . $total_works . "件</p>";
        echo "<p>1ページあたりの表示件数: " . $items_per_page . "件</p>";
        echo "<p>計算結果の総ページ数: " . $total_pages . "</p>";
        echo "<hr>";
        
        // --- デバッグ終了 ---
        echo "<h2>デバッグ終了</h2>";
        exit;
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