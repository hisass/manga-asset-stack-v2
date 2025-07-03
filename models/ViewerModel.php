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
    public function getWorksByCategoryId($category_id, $sort_option = null) {
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
        $asset_details = array();
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');

        if (empty($work['directory_name'])) {
            return $asset_details;
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
                    $file_path = $full_dir_path . '/' . $file;
                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                    if (is_file($file_path) && in_array($extension, $allowed_extensions)) {
                        $web_path = $web_base_paths[$version] . '/' . $dir_name . '/' . rawurlencode($file);
                        
                        $size_str = '';
                        $image_size = getimagesize($file_path);
                        if ($image_size) {
                            $size_str = $image_size[0] . 'x' . $image_size[1] . 'px';
                        }
                        
                        $date_str = '';
                        $timestamp = filemtime($file_path);
                        if ($timestamp) {
                            $date_str = '作成日時: ' . date('Y-m-d H:i', $timestamp);
                        }

                        $asset_details[$web_path] = array(
                            'url' => $web_path,
                            'filename' => $file,
                            'size_str' => $size_str,
                            'date_str' => $date_str,
                            'server_path' => $file_path
                        );
                    }
                }
            }
        }
        
        return array_values($asset_details);
    }
}