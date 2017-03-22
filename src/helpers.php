<?php

if(!function_exists('common_return')){
    function common_return($code = 0, $msg = '', $data = null){
        return [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];
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

if(!function_exists('curl_get_contents')){
    function curl_get_contents($url, $options = []){
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

        if(!empty($options['post_data'])){
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POST, 1);

            curl_setopt($curl, CURLOPT_POSTFIELDS, empty($options['is_file']) ? http_build_query($options['post_data']) : $options['post_data']);
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