<?php

// header('Content-type: application/json');

include('../vendor/autoload.php');

use Wmy\Image;

$config = [
    'local' => [
        'is_local' => true,
        'name' => 'test.png',
    ],
    'remote' => [
        'is_local' => false,
        'name' => 'https://www.baidu.com/img/bd_logo1.png',
    ],
];
$config_key = 'local';
$base64url = Image::base64Url($config[$config_key]['name'], $config[$config_key]['is_local']);
// echo "<img src='{$base64url}'>";

$img_info = Image::base64Decode($base64url);
file_put_contents("decode.{$img_info['type']}", $img_info['content']);
echo "<img src='decode.{$img_info['type']}'>";

