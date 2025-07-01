<?php
// models/ViewerModel.php

class ViewerModel {
    private $dataManager;

    public function __construct(DataManager $dataManager) {
        $this->dataManager = $dataManager;
    }

    public function getCategories() {
        return $this->dataManager->getCategories();
    }

    public function getWorksByCategoryId($category_id) {
        return $this->dataManager->getWorks($category_id);
    }

    public function getWorkById($work_id) {
        return $this->dataManager->getWorkById($work_id);
    }
    
    // ▼▼▼ このメソッドを丸ごと追加 ▼▼▼
    public function getCategoryById($category_id) {
        return $this->dataManager->getCategoryById($category_id);
    }
    // ▲▲▲ ここまでを追加 ▲▲▲

    /**
     * 指定された作品のアセット画像を探し、Webでアクセス可能なURLの配列を返す
     * v1とv2の両方のアセットパスを検索対象とする
     *
     * @param array $work 作品データ配列
     * @return array 画像URLの配列
     */
    public function getAssetsForWork($work) {
        $asset_urls = array();
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');

        if (empty($work['directory_name'])) {
            return $asset_urls; // ディレクトリ名がなければ空の配列を返す
        }
        $dir_name = $work['directory_name'];

        // 検索対象のサーバー内パス
        $paths_to_scan = array(
            'v1' => ASSET_PATH_V1,
            'v2' => ASSET_PATH_V2
        );

        // Webアクセス用のURLベースパス
        // v1のアセットパスは一つ上の階層にあるため、BASE_URLからパスを調整する
        $v1_web_base_path = dirname(BASE_URL) . '/dmpc-materials/contents';
        $v2_web_base_path = BASE_URL . '/contents';

        $web_base_paths = array(
            'v1' => $v1_web_base_path,
            'v2' => $v2_web_base_path
        );

        // v1, v2 両方のパスをスキャン
        foreach ($paths_to_scan as $version => $base_path) {
            $full_dir_path = $base_path . '/' . $dir_name;

            if (is_dir($full_dir_path)) {
                $files = scandir($full_dir_path);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }
                    
                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                    if (in_array($extension, $allowed_extensions)) {
                        // ファイルへのWebアクセス可能なURLを構築して配列に追加
                        $web_path = $web_base_paths[$version] . '/' . $dir_name . '/' . rawurlencode($file);
                        $asset_urls[] = $web_path;
                    }
                }
            }
        }
        
        // 重複を削除し、結果を返す
        return array_unique($asset_urls);
    }
}