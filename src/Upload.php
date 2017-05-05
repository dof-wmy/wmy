<?php
/**
 * Created by PhpStorm.
 * User: wmy
 * Date: 2017/5/4
 * Time: 11:27
 */

namespace Wmy;


class Upload
{
    static function upload($file_key, $save_path = '.', $save_name = null, $options = []){
        if(!empty($_FILES[$file_key])){
            if($_FILES[$file_key]['error'] == 0){
                $file_name = $_FILES[$file_key]['name'];//原文件名
                $file_name_tmp = $_FILES[$file_key]['tmp_name'];//服务器上临时文件名
                $file_size = $_FILES[$file_key]['size'];//文件大小
                $file_ext = strtolower(trim(pathinfo($file_name, PATHINFO_EXTENSION)));//获得文件扩展名
                //文件大小验证
//                TODO::文件大小验证
                //文件类型验证
                if(!empty($options['allow_exts']) && !in_array($file_ext, $options['allow_exts'])){
                    return common_return(406, '文件格式不符合要求', $options['allow_exts']);
                }
                if(empty($save_name)){
                    $save_name = md5(date("YmdHis") . mt_rand(100, 999)) . ".{$file_ext}";
                }elseif(file_exists("{$save_path}/{$save_name}")){
                    return common_return(406, "文件 {$save_name} 已存在", [$save_path, $save_name]);
                }
                move_uploaded_file($file_name_tmp, "{$save_path}/{$save_name}");//移动文件
                if(file_exists("{$save_path}/{$save_name}")){
                    return common_return(0, '上传文件成功', [
                        'file' => "{$save_path}/{$save_name}"
                    ]);
                }else{
                    return common_return(406, '上传文件失败');
                }
            }else{
                return common_return(406, '上传文件出错', $_FILES[$file_key]);
            }
        }else{
            return common_return(406, '上传文件不存在');
        }
    }
}