<?php

if(!function_exists('common_return')){
    function common_return($code = 0, $msg = '', $data = null, $options = []){
        $res = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];
        if(isset($options['json_die'])){
            json_die($res, $options['json_die']);
        }else{
            return $res;
        }
    }
}


if(!function_exists('full_date')){
    function full_date($time = null) {
        if (is_null($time)) {
            return date('Y-m-d H:i:s');
        }
        return date('Y-m-d H:i:s', $time);
    }
}

if(!function_exists('json_die')){
    function json_die($data, $set_header = true){
        if($set_header){
            header("Content-Type:text, application/json");
        }
        die(json_encode($data));
    }
}

if(!function_exists('get_file_type')){
    function get_file_type($file_name, $is_local_file = true){
        $file_type = null;

        if($is_local_file){
            $img_info = getimagesize($file_name);
            $img_type = $img_info['mime'];
        }else{
            preg_match('/\.[a-zA-Z]+$/', $file_name, $match_result);
            if($match_result){
                $file_type = str_replace('.','',$match_result[0]);
            }
        }
        return $file_type;
    }
}

if(!function_exists('export_csv')){
    function export_csv($data, $file_name) {
        $result = '';
        foreach($data as &$line){
            $line = is_string($line) ? explode(',', $line) : $line;
            foreach($line as &$line_item){
                $line_item = str_replace(',', ' ', trim($line_item));
                if(is_numeric($line_item) && strlen("{$line_item}") > 10){
                    $line_item = "{$line_item}\t"; //加"\t"防止转换成科学计数法
                }
            }
            $line = implode(',', $line);
            $result .= "{$line}\n";
        }

        //防止导出文件名乱码
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $encoded_filename = urlencode($file_name);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);

        header('Content-Type: application/octet-stream');
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $file_name . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
        }

        die(mb_convert_encoding($result, 'GBK', 'UTF-8'));
    }
}


if(!function_exists('get_real_url')){
    function get_real_url($url, $max_location_level = 10){
        for($i = 1;$i<=$max_location_level; $i++){
            $real_url = get_location($url, $i);
            if($real_url == $url){
                break;
            }else{
                $url = $real_url;
            }
        }
        return $real_url;
    }
}


if(!function_exists('get_location')){
    function get_location($url, $count = 1){
        $headers = get_headers($url, 1);
        if(empty($headers['Location']) || $count > 10){
            return $url;
        }else{
            return is_array($headers['Location']) ? $headers['Location'][0] : $headers['Location'];
        }
    }
}
