<?php
// config.php

// 1. このプロジェクトのルートディレクトリのパスを定数として定義する
// このファイルはルートにあるので、__DIR__ でOK
define('BASE_DIR_PATH', __DIR__);

// 2. アセットファイルのパスを定数として定義する
// v1のアセットパス (manga-asset-stack-v2フォルダの一つ上の階層にある)
define('ASSET_PATH_V1', BASE_DIR_PATH . '/../dmpc-materials/contents');

// v2のアセットパス (manga-asset-stack-v2フォルダの直下にある)
define('ASSET_PATH_V2', BASE_DIR_PATH . '/contents');