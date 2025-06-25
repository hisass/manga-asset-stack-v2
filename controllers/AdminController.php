<?php
class AdminController {
    private $dataManager;

    public function __construct() {
        $this->dataManager = new DataManager();
    }

    public function dashboard() {
        $data['title'] = '管理ダッシュボード';
        $data['categories'] = $this->dataManager->getCategories();
        $data['works'] = $this->dataManager->getWorks();
        
        $this->loadView('dashboard', $data);
    }

    private function loadView($viewName, $data = array()) {
        extract($data, EXTR_SKIP);
        
        // ★★★ パスを修正しました ★★★
        $baseDir = BASE_DIR_PATH . '/views/admin/';
        require_once $baseDir . "layouts/header.php";
        require_once $baseDir . "{$viewName}.php";
        require_once $baseDir . "layouts/footer.php";
    }
    /**
     * 作品の変更を保存する
     */
    public function saveWork($postData) {
        $work_id = isset($postData['work_id']) ? $postData['work_id'] : null;

        // 更新対象のIDがなければエラー
        if (!$work_id) {
            die('Error: work_idが見つかりません。');
        }

        // モデルにデータの更新と保存を依頼
        $success = $this->dataManager->updateWork($work_id, $postData);

        if ($success) {
            // 保存が成功したら、ダッシュボードにリダイレクトして戻る
            header('Location: admin.php?action=dashboard');
            exit;
        } else {
            // 保存に失敗した場合
            die('Error: データの保存に失敗しました。');
        }
    }

    /**
     * 作品の編集フォームを表示する
     * @param string $work_id 編集対象の作品ID
     */
    public function editWork($work_id) {
        if (!$work_id) {
            // IDがなければダッシュボードに戻す
            header('Location: admin.php');
            exit;
        }

        $work = $this->dataManager->getWorkById($work_id);
        if (!$work) {
            // 作品が見つからなければエラー表示
            die('指定された作品が見つかりません。');
        }

        $data['title'] = '作品の編集: ' . htmlspecialchars($work['title']);
        $data['work'] = $work;
        $data['categories'] = $this->dataManager->getCategories(); // カテゴリ選択プルダウン用
        
        $this->loadView('edit_work_form', $data);
    }

    /**
     * 作品の新規追加フォームを表示する
     */
    public function addWork() {
        $data['title'] = '作品の新規追加';
        
        // 空の作品データ配列を用意（ビューでのエラーを防ぐため）
        $data['work'] = array(
            'work_id' => '', 'title' => '', 'title_ruby' => '',
            'author' => '', 'author_ruby' => '', 'category_id' => '',
            'comment' => ''
        );
        
        $data['categories'] = $this->dataManager->getCategories(); // カテゴリ選択プルダウン用
        
        // 編集フォームのビューを再利用する
        $this->loadView('edit_work_form', $data);
    }
    
    /**
     * 新しい作品を作成（保存）する
     */
    public function createWork($postData) {
        $work_id = isset($postData['work_id']) ? trim($postData['work_id']) : '';
        if (empty($work_id)) {
            die('Error: 作品IDは必須です。');
        }

        // モデルに新しい作品の追加と保存を依頼
        $success = $this->dataManager->addWork($postData);

        if ($success) {
            // 保存が成功したら、ダッシュボードにリダイレクトして戻る
            header('Location: admin.php?action=dashboard');
            exit;
        } else {
            // 保存に失敗した場合（主にIDの重複が原因）
            die('Error: データの追加に失敗しました。作品IDが既に存在している可能性があります。');
        }
    }

    
}