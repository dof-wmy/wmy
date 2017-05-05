<?php
require '../vendor/autoload.php';

use Wmy\Upload;
use Wmy\Baidu\OCR;

//header("Access-Control-Allow-Origin: *");

ini_set("display_errors", 1);
ini_set('error_reporting', E_ERROR);

$image_url = '';
$img_base64 = '';
if(!empty($_POST['image_url'])){
    $image_url = trim($_POST['image_url']);
}

$upload_res = Upload::upload('image', '.', '', [
    'allow_exts' => [
        'png',
        'gif',
        'jpg',
        'jpeg',
        'bmp',
    ],
]);
if(empty($upload_res['code'])){
    $file_path = $upload_res['data']['file'];
    $unlink_file = true;
    if(!empty($file_path)){
        if($file_content = file_get_contents($file_path)){
            $img_base64 = \Intervention\Image\ImageManagerStatic::make($file_content)->encode('data-url')->encoded;
            if(!empty($unlink_file)) unlink($file_path);
        }else{
            common_return(406, '读取文件失败', [
                $file_path
            ], ['json_die' => true]);
        }
    }
}

json_die(OCR::getByDemo($img_base64, $image_url));
