<?php
/**
 * Created by PhpStorm.
 * User: wmy
 * Date: 2017/5/4
 * Time: 10:49
 */

namespace Wmy\Baidu;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class OCR
{
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
        $post_data = [
            'type'       => 'commontext',
            'image' => trim($img_base64),
            'image_url'  => trim($image_url),
        ];
        $get_cookies = false;//设为true会报{"errno":101,"msg":"请求Demo类型错误","data":""}，具体原因以后再查
        try{
            try {
                $headers = [
                    'Accept' => '*/*',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Accept-Language' => 'zh-CN,zh;q=0.8,zh-TW;q=0.6,en-US;q=0.4,en;q=0.2',
                    'Connection' => 'keep-alive',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'DNT' => '1',
                    'Host' => 'ai.baidu.com',
                    'Origin' => 'http://ai.baidu.com',
                    'Referer' => 'http://ai.baidu.com',
                    'User-Agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 ('KHTML', like Gecko) Chrome/57.0.2987.133 Safari/537.36",
                ];
                if($get_cookies){
                    $jar = new \GuzzleHttp\Cookie\CookieJar;
                    $http_client = new \GuzzleHttp\Client();
                    $response = $http_client->request('GET', 'http://ai.baidu.com/tech/ocr/general', [
                        'cookies' => $jar,
                    ]);
                }else{
                    $headers['Cookie'] = '__cas__st__=NLI; __cas__id__=0';
                }
                $http_client = new \GuzzleHttp\Client(['cookies' => $get_cookies]);
                $response = $http_client->post('http://ai.baidu.com/aidemo', [
//             'debug' => true,
                    'http_errors' => true,
                    'connect_timeout' => 60,
                    'timeout' => 300,
                    'headers' => $headers,
                    // 'query' => $post_data,
                    'form_params' => $post_data,
                    // 'json' => $post_data,
                    // 'multipart' => [
                    //     [
                    //         'name'     => 'type',
                    //         'contents' => 'commontext'
                    //     ],
                    //     [
                    //         'name'     => 'image',
                    //         'contents' => $img_base64
                    //     ],
                    //     [
                    //         'name'     => 'image_url',
                    //         'contents' => ''
                    //     ],
                    // ]
                ]);
                $res_words = [];
                $baidu_res = $response->getBody()->getContents();
                if(!empty($baidu_res && $baidu_res = json_decode($baidu_res, true))){
                    if($baidu_res['errno'] == 0){
                        foreach($baidu_res['data']['words_result'] as $words){
                            $res_words[] = [
                                'text' => $words['words'],
                            ];
                        }
                        $result = [
                            'img_src' => empty($img_base64) ? $image_url : $img_base64,
                            'words' => $res_words,
                            'baidu_res' => $baidu_res,
                        ];
                        return common_return(0, '', $result);
                    }else{
                        return common_return(406, $baidu_res['msg'], $baidu_res);
                    }
                }
            } catch (ClientException $e) {
                return common_return(406, '请求出错', [
                    'error' => 'ClientException',
                    'request' => Psr7\str($e->getRequest()),
                    'response' => $e->hasResponse() ? Psr7\str($e->getResponse()) : null,
                ]);
            }
        }catch(RequestException $e){
            common_return(406, '请求出错', [
                'error' => 'RequestException',
                'request' => Psr7\str($e->getRequest()),
                'response' => $e->hasResponse() ? Psr7\str($e->getResponse()) : null,
            ]);
        }
    }
}