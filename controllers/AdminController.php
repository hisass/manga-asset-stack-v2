<?php
/**
 * 管理画面のすべてのロジックを制御するコントローラ
 */
class AdminController
{
    private $dataManager;

    public function __construct()
    {
        $this->dataManager = new DataManager();
    }

    /**
     * ダッシュボードを表示する
     */
    public function dashboard($message = null)
    {
        $works = $this->dataManager->getAllWorks();
        $categories = $this->dataManager->getAllCategories();
        $categories_order = $this->dataManager->getCategoryOrder();

        // カテゴリ順に並び替え
        $sorted_categories = array();
        foreach ($categories_order as $cat_id) {
            if (isset($categories[$cat_id])) {
                $sorted_categories[$cat_id] = $categories[$cat_id];
            }
        }
        // orderに含まれないカテゴリを追加
        foreach ($categories as $cat_id => $cat_data) {
            if (!isset($sorted_categories[$cat_id])) {
                $sorted_categories[$cat_id] = $cat_data;
            }
        }
        $categories = $sorted_categories;
        
        require_once BASE_DIR_PATH . '/views/admin/dashboard.php';
    }

    /**
     * 作品追加フォームを表示
     */
    public function addWork($message = null)
    {
        $categories = $this->dataManager->getAllCategories();
        require_once BASE_DIR_PATH . '/views/admin/edit_work_form.php';
    }

    /**
     * 新しい作品を作成する
     */
    public function createWork($post, $files)
    {
        $title = trim($post['title']);
        $directory_name = trim($post['directory_name']);
        if (empty($title) || empty($directory_name)) {
            $this->addWork("タイトルとフォルダ名は必須です。");
            return;
        }
        
        $asset_dir = ASSET_PATH_V2 . '/' . $directory_name;
        if (!file_exists($asset_dir)) {
            if (!mkdir($asset_dir, 0777, true)) {
                $this->addWork("フォルダの作成に失敗しました。パーミッションを確認してください。");
                return;
            }
        }
        
        $newWork = array(
            'work_id' => 'work_' . uniqid(mt_rand(), true),
            'title' => $title,
            'title_ruby' => trim($post['title_ruby']),
            'author' => trim($post['author']),
            'author_ruby' => trim($post['author_ruby']),
            'category_id' => $post['category_id'],
            'comment' => trim($post['comment']),
            'directory_name' => $directory_name,
            'copyright' => trim($post['copyright']),
            'open' => empty($post['open']) ? date('Y-m-d') : $post['open'],
            'created' => date('Y-m-d H:i:s'),
            'updated' => date('Y-m-d H:i:s'),
            'assets' => array()
        );

        if (isset($files['assets']) && !empty($files['assets']['name'][0])) {
            foreach ($files['assets']['name'] as $key => $name) {
                if ($files['assets']['error'][$key] === UPLOAD_ERR_OK) {
                    $tmp_name = $files['assets']['tmp_name'][$key];
                    $target_path = $asset_dir . '/' . basename($name);

                    if (move_uploaded_file($tmp_name, $target_path)) {
                        $newWork['assets'][] = array(
                            'filename' => basename($name),
                            'size' => filesize($target_path),
                            'created' => date('Y-m-d H:i:s', filectime($target_path)),
                        );
                    }
                }
            }
        }
        
        $newWorkId = $this->dataManager->addWork($newWork);

        if ($newWorkId) {
            header('Location: admin.php?action=edit_work&id=' . $newWorkId);
            exit;
        } else {
            $this->addWork("作品の追加に失敗しました。");
        }
    }

    /**
     * 作品の編集フォームを表示する
     */
    public function editWork($id, $message = null)
    {
        if (!$id) {
            die('作品IDが指定されていません。');
        }
        $work = $this->dataManager->getWorkById($id);
        if (!$work) {
            die('指定された作品が見つかりません。');
        }

        $categories = $this->dataManager->getAllCategories();
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        if($status === 'success' && $message === null){
            $message = "作品情報を更新しました。";
        }
        
        require_once BASE_DIR_PATH . '/views/admin/edit_work_form.php';
    }

    /**
     * 作品の変更を保存する
     */
    public function saveWork($post, $files)
    {
        $work_id = isset($post['work_id']) ? $post['work_id'] : null;
        if (!$work_id) { die('作品IDがありません。'); }
        $work = $this->dataManager->getWorkById($work_id);
        if (!$work) { die('指定された作品が見つかりません。'); }
        
        $work['title'] = trim($post['title']);
        $work['title_ruby'] = trim($post['title_ruby']);
        $work['author'] = trim($post['author']);
        $work['author_ruby'] = trim($post['author_ruby']);
        $work['category_id'] = $post['category_id'];
        $work['comment'] = trim($post['comment']);
        $work['copyright'] = trim($post['copyright']);
        $work['open'] = empty($post['open']) ? $work['open'] : $post['open'];
        $work['updated'] = date('Y-m-d H:i:s');
        
        if ($this->dataManager->saveWork($work)) {
            header('Location: admin.php?action=edit_work&id=' . $work_id . '&status=success');
            exit;
        } else {
            $this->editWork($work_id, "作品の保存に失敗しました。");
        }
    }

    /**
     * 作品を削除する
     */
    public function deleteWork($id)
    {
        if (!$id) { die('IDが指定されていません。'); }
        $work = $this->dataManager->getWorkById($id);
        if ($work && !empty($work['directory_name'])) {
            $dir_to_delete = ASSET_PATH_V2 . '/' . $work['directory_name'];
            if (is_dir($dir_to_delete)) {
                $files = glob($dir_to_delete . '/*');
                foreach($files as $file){ if(is_file($file)){ unlink($file); } }
                rmdir($dir_to_delete);
            }
        }
        if ($this->dataManager->deleteWork($id)) {
            $this->dashboard("作品を削除しました。");
        } else {
            $this->dashboard("作品の削除に失敗しました。");
        }
    }
    
    /**
     * アセットを削除する (Ajax)
     */
    public function deleteAsset()
    {
        header('Content-Type: application/json');
        $work_id = isset($_POST['work_id']) ? $_POST['work_id'] : null;
        $filename = isset($_POST['filename']) ? $_POST['filename'] : null;
        if (!$work_id || !$filename) {
            echo json_encode(array('success' => false, 'message' => 'IDまたはファイル名がありません。'));
            exit;
        }
        $work = $this->dataManager->getWorkById($work_id);
        if (!$work) {
            echo json_encode(array('success' => false, 'message' => '作品が見つかりません。'));
            exit;
        }
        $asset_key_to_delete = null;
        foreach ($work['assets'] as $key => $asset) {
            if ($asset['filename'] === $filename) {
                $asset_key_to_delete = $key;
                break;
            }
        }
        if (!empty($asset_key_to_delete)) {
            $asset_path = ASSET_PATH_V2 . '/' . $work['directory_name'] . '/' . $filename;
            if (file_exists($asset_path)) {
                unlink($asset_path);
            }
            unset($work['assets'][$asset_key_to_delete]);
            $work['assets'] = array_values($work['assets']);
            $work['updated'] = date('Y-m-d H:i:s');
            if ($this->dataManager->saveWork($work)) {
                echo json_encode(array('success' => true));
            } else {
                echo json_encode(array('success' => false, 'message' => 'データファイルの保存に失敗しました。'));
            }
            exit;
        }
        echo json_encode(array('success' => false, 'message' => '指定されたアセットが見つかりません。'));
        exit;
    }

    public function editCategory($id = null, $message = null)
    {
        $category = null;
        if ($id) {
            $category = $this->dataManager->getCategoryById($id);
        }
        require_once BASE_DIR_PATH . '/views/admin/edit_category_form.php';
    }

    public function createCategory($post)
    {
        $newCategory = array('id' => 'cat_' . uniqid(), 'name' => trim($post['name']), 'alias' => trim($post['alias']), 'title_count' => (int)$post['title_count']);
        if ($this->dataManager->addCategory($newCategory)) {
            $this->dashboard("カテゴリを追加しました。");
        } else {
            $this->editCategory(null, "カテゴリの追加に失敗しました。");
        }
    }
    
    public function saveCategory($post)
    {
        $id = $post['id'];
        $category = array('id' => $id, 'name' => trim($post['name']), 'alias' => trim($post['alias']), 'title_count' => (int)$post['title_count']);
        if ($this->dataManager->saveCategory($category)) {
            $this->dashboard("カテゴリを更新しました。");
        } else {
            $this->editCategory($id, "カテゴリの更新に失敗しました。");
        }
    }
    
    public function moveCategory($id, $direction)
    {
        if ($id && $direction) {
            $this->dataManager->moveCategoryOrder($id, $direction);
        }
        header('Location: admin.php');
        exit;
    }
}