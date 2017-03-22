<?php
namespace Wmy;

class Image{

    static function base64Url($img_file_path, $is_local_file = true){
        $default_type = 'png';

        $img_type = get_file_type($img_file_path, $is_local_file);
        $img_type = $img_type ? : $default_type;
        $curl_options = [];
        if(!$is_local_file){
            $parse_url = parse_url($img_file_path);
            $curl_options['referer'] = "{$parse_url['scheme']}//{$parse_url['host']}";
            $curl_result = curl_get_contents($img_file_path, $curl_options);
            $img_content = $curl_result['data'];
        }else{
            $img_content = file_get_contents($img_file_path);
        }
        $img_base64 = base64_encode($img_content);
        $img_base64_url = '';
        if($img_base64){
            $img_base64 = chunk_split($img_base64);
            $img_base64_url = "data:image/{$img_type};base64,{$img_base64}";
        }
        return $img_base64_url;
    }

    static function base64Decode($base64_url){
        preg_match('/data:image\/([a-zA-Z]+);base64,([\s\S]+)/', $base64_url, $preg_arr);
        return [
            'type' => $preg_arr[1],
            'content' => base64_decode(preg_replace('/\s/', '', $preg_arr[2])),
        ];
    }

}