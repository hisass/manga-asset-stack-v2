<?php
class AdminController {
    private $dataManager;

    public function __construct() {
        $this->dataManager = new DataManager();
    }

// dashboardメソッドを、このテスト用コードで丸ごと置き換えてください
public function dashboard() {
    // --- ▼▼▼ すべてのロジックを無視し、正しい値を直接定義する ▼▼▼ ---

    $data['title'] = '管理ダッシュボード【ビュー表示テスト】';

    // カテゴリデータを仮作成
    $data['categories'] = array(
        array('id' => 'cat_001', 'name' => '少年まんが', 'directory_name' => 'shonen', 'alias' => 'shonen', 'title_count' => '12'),
        array('id' => 'cat_002', 'name' => '少女まんが', 'directory_name' => 'shojo', 'alias' => 'shojo', 'title_count' => '11'),
    );
    // カテゴリごとの作品数を仮作成
    $data['category_work_counts'] = array(
        'cat_001' => 12,
        'cat_002' => 11,
    );

    // 作品データを25件ほど仮作成（41件だと大変なので）
    $data['works'] = array();
    for ($i = 1; $i <= 25; $i++) {
        $data['works'][] = array(
            'work_id' => 'test_' . $i,
            'title' => 'テスト作品 ' . $i,
            'category_id' => 'cat_001',
            'open' => '2025-06-26'
        );
    }
    
    // ソート情報を仮作成
    $data['current_sort_key'] = 'open';
    $data['current_sort_order'] = 'desc';

    // ★★★ 最も重要なテスト箇所 ★★★
    // 総ページ数を「3」に固定する (25件の作品 / 1ページ20件 = 2ページだが、あえて「3」にする)
    $data['total_pages'] = 3;
    $data['current_page'] = 1;
    
    // --- ▲▲▲ ここまでがテスト用の固定データ ▲▲▲ ---

    // この固定データをビューに渡して表示させてみる
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