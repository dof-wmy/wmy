<?php
/**
 * Created by PhpStorm.
 * User: wmy
 * Date: 2017/5/5
 * Time: 8:52
 */

namespace Wmy;

use Wmy\Exception\WmyException;

class Curl
{
    static protected $method = 'GET';
    static protected $connect_timeout = 30;
    static protected $timeout= 120;
    static protected $header = [];
    static protected $referer;
    static protected $cookie;
    static protected $cookie_jar;
    static protected $cookie_file;
    static protected $proxy;

    static function execute($url, $options = []){
        if(!function_exists('curl_init')){
            throw new WmyException('curl error');
        }
        $curl = curl_init();
        foreach([
            'method' => CURLOPT_CUSTOMREQUEST,
            'connect_timeout' => CURLOPT_CONNECTTIMEOUT,
            'timeout' => CURLOPT_TIMEOUT,
            'header' => CURLOPT_HTTPHEADER,
            'referer' => CURLOPT_REFERER,
            'cookie' => CURLOPT_COOKIE,
            'cookie_jar' => CURLOPT_COOKIEJAR,
            'cookie_file' => CURLOPT_COOKIEFILE,
        ] as $field=>$curl_option){
            if(!empty($options[$field])){
                Self::$$field = $field == 'method' ? strtoupper($options[$field]) : $options[$field];
            }
            if(!empty(Self::$$field)){
                curl_setopt($curl, $curl_option,  Self::$$field);
            }
        }
        if(Self::$method != 'GET'){
            curl_setopt($curl, CURLOPT_POST, 1);
        }
        if(!empty($options['form_params'])){
            if(Self::$method == 'GET'){
                $uri = '';
                foreach($options['form_params'] as $param=>$value){
                    $uri .= "&{$param}={$value}";
                }
                $url .= $uri;
            }else{
                curl_setopt($curl, CURLOPT_POSTFIELDS, !empty($options['http_build_query']) ? http_build_query($options['form_params']) : $options['form_params']);
            }
        }

        //代理配置
        if(!empty($options['proxy'])){
            Self::$proxy = $options['proxy'];
            curl_setopt($curl, CURLOPT_PROXY, Self::$proxy);
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        if(substr($url, 0, 8) == 'https://'){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            //curl_setopt ( $curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1 );
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);

        $curl_result = curl_exec($curl);
        //转码
        if(!empty($options['encode_to'])){
            mb_convert_encoding($curl_result, empty($options['encode_from']) ? 'UTF-8' : $options['encode_from'], $options['encode_to']);
        }
        $curl_http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        // 获得响应结果里的：头大小
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        // 根据头大小去获取头信息内容
        $header = substr($curl_result, 0, $header_size);
        $header = explode("\n", $header);
        if(!empty($header)){
            unset($header[0]);
            $header_arr = [];
            foreach($header as $k=>$header_value){
                if(trim($header_value)){
                    $header_value = explode(':', $header_value);
                    $header_arr[$header_value[0]] = trim($header_value[1]);
                }
            }
            $header = $header_arr;
        }
        $body = substr($curl_result, $header_size);
        curl_close($curl);

        return [
            'curl_result' => $curl_result,
            'http_code' => $curl_http_code,
            'header' => $header,
            'body' => $body,
        ];
    }
}