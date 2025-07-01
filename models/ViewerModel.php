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

    // ▼▼▼ このメソッドを修正 ▼▼▼
    public function getWorksByCategoryId($category_id, $sort_option = 'open_desc') {
        return $this->dataManager->getWorks($category_id, null, $sort_option);
    }
    // ▲▲▲ ここまでを修正 ▲▲▲

    public function getWorkById($work_id) {
        return $this->dataManager->getWorkById($work_id);
    }
    
    public function getCategoryById($category_id) {
        return $this->dataManager->getCategoryById($category_id);
    }

    public function getAssetsForWork($work) {
        $asset_urls = array();
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');

        if (empty($work['directory_name'])) {
            return $asset_urls;
        }
        $dir_name = $work['directory_name'];

        $paths_to_scan = array(
            'v1' => ASSET_PATH_V1,
            'v2' => ASSET_PATH_V2
        );

        $v1_web_base_path = dirname(BASE_URL) . '/dmpc-materials/contents';
        $v2_web_base_path = BASE_URL . '/contents';

        $web_base_paths = array(
            'v1' => $v1_web_base_path,
            'v2' => $v2_web_base_path
        );

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
                        $web_path = $web_base_paths[$version] . '/' . $dir_name . '/' . rawurlencode($file);
                        $asset_urls[] = $web_path;
                    }
                }
            }
        }
        
        return array_unique($asset_urls);
    }
}