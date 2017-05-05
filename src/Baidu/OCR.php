<?php
/**
 * Created by PhpStorm.
 * User: wmy
 * Date: 2017/5/4
 * Time: 10:49
 */

namespace Wmy\Baidu;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Wmy\Curl;

class OCR
{
    static $get_cookies = false;//设为true会报{"errno":101,"msg":"请求Demo类型错误","data":""}，具体原因以后再查
    static $get_cookies_url = 'http://ai.baidu.com/tech/ocr/general';
    static $url = 'http://ai.baidu.com/aidemo';
    static $headers = [
        'Accept'          => '*/*',
        'Accept-Encoding' => 'gzip, deflate',
        'Accept-Language' => 'zh-CN,zh;q=0.8,zh-TW;q=0.6,en-US;q=0.4,en;q=0.2',
        'Connection'      => 'keep-alive',
        'Content-Type'    => 'application/x-www-form-urlencoded',
        'DNT'             => '1',
        'Host'            => 'ai.baidu.com',
        'Origin'          => 'http://ai.baidu.com',
        'Referer'         => 'http://ai.baidu.com',
        'Cookie'          => '__cas__st__=NLI; __cas__id__=0',
        'User-Agent'      => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 ('KHTML', like Gecko) Chrome/57.0.2987.133 Safari/537.36",
    ];
    static $form_params = [
        'type'      => 'commontext',
        'image'     => '',
        'image_url' => '',
    ];
    /**
     * 通过百度图片识别demo解析
     *
     * @param string $img_base64 图片base64编码
     * @param string $image_url 图片url
     *
     * @return array
     */
    static function getByDemo($img_base64 = '', $image_url = ''){
        if(empty($img_base64) && empty($image_url)){
            return common_return(406, 'img_base64和image_url不能同时为空');
        }
        Self::$form_params['image'] = trim($img_base64);
        Self::$form_params['image_url']  = trim($image_url);
        try{
            try {
                if(Self::$get_cookies){
                    unset(Self::$headers['Cookie']);
                    $jar = new \GuzzleHttp\Cookie\CookieJar;
                    $http_client = new \GuzzleHttp\Client();
                    $response = $http_client->request('GET', Self::$get_cookies_url, [
                        'cookies' => $jar,
                    ]);
                }
                $http_client = new \GuzzleHttp\Client(['cookies' => Self::$get_cookies]);
                $response = $http_client->post(Self::$url, [
                    'http_errors'       => true,
                    'connect_timeout'   => 60,
                    'timeout'           => 300,
                    'headers'           => Self::$headers,
                    'form_params'       => Self::$form_params,
                ]);
                $baidu_res_body = $response->getBody()->getContents();
                return Self::resHandle($baidu_res_body);
            } catch (ClientException $e) {
                return Self::curlGet();
//                return Self::exceptionHandle($e, 'ClientException');
            }
        }catch(RequestException $e){
            return Self::curlGet();
//            return Self::exceptionHandle($e, 'RequestException');
        }
    }

    static function curlGet(){
        $headers_tmp = '';
        foreach(Self::$headers as $header_key=>$header_value){
            $headers_tmp[] = "{$header_key}:{$header_value}";
        }
        $baidu_res = Curl::execute(Self::$url, [
            'method' => 'POST',
            'form_params' => Self::$form_params,
            'http_build_query' => true,
            'header' => $headers_tmp,
        ]);
        return Self::resHandle($baidu_res['body']);
    }

    static function resHandle($baidu_res_body){
        $res_words = [];
        if(!empty($baidu_res_body && $baidu_res_body = json_decode($baidu_res_body, true))){
            if($baidu_res_body['errno'] == 0){
                foreach($baidu_res_body['data']['words_result'] as $words){
                    $res_words[] = [
                        'text' => $words['words'],
                    ];
                }
                $result = [
                    'img_src' => empty(Self::$form_params['image']) ? Self::$form_params['image_url'] : Self::$form_params['image'],
                    'words' => $res_words,
                    'baidu_res' => $baidu_res_body,
                ];
                return common_return(0, '', $result);
            }
        }
        return common_return(406, $baidu_res_body['msg'], $baidu_res_body);
    }

    static function exceptionHandle($e, $exception_type){
        return common_return(406, '请求出错', [
            'error' => $exception_type,
            'request' => Psr7\str($e->getRequest()),
            'response' => $e->hasResponse() ? Psr7\str($e->getResponse()) : null,
        ]);
    }
}