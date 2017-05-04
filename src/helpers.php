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


if(!function_exists('curl_request')){
    function curl_request($url, $options = []){
        if(!function_exists('curl_init')){
            throw new Exception('curl error');
        }
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper(empty($options['method']) ? 'get' : $options['method']));

        //代理配置
        if(!empty($options['proxy'])){
            curl_setopt($curl, CURLOPT_PROXY, $options['proxy']);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        //超时配置
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, empty($options['connect_timeout']) ? 10 : $options['connect_timeout']);
        curl_setopt($curl, CURLOPT_TIMEOUT, empty($options['timeout']) ? 30 : $options['timeout']);
        //请求header配置
        if(!empty($options['headers'])){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $options['headers']);
        }

        if(!empty($options['referer'])){
            curl_setopt($curl, CURLOPT_REFERER, $options['referer']);
        }

        if(!empty($options['cookie'])){
            curl_setopt($curl, CURLOPT_COOKIE, $options['cookie']);
        }

        if(!empty($options['save_cookie_file'])){
            //存储cookies
            curl_setopt($curl, CURLOPT_COOKIEJAR, $options['save_cookie_file']);
        }

        if(!empty($options['use_cookie_file'])){
            //使用cookies文件
            curl_setopt($curl, CURLOPT_COOKIEJAR, $options['use_cookie_file']);
        }

        if(!empty($options['post_data'])){
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POST, 1);

            curl_setopt($curl, CURLOPT_POSTFIELDS, !empty($options['http_build_query']) ? http_build_query($options['post_data']) : $options['post_data']);
        }
        if(substr($url, 0, 8) == 'https://'){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            //curl_setopt ( $curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1 );
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);

        $curl_result = curl_exec($curl);
        $curl_http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return [
            'http_code' => $curl_http_code,
            'data' => $curl_result,
        ];
    }
}

if(!function_exists('test')){
    function test($data, $file_name){

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