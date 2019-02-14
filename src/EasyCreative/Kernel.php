<?php

namespace EasyCreative;

class Kernel {

    /**
     * @param string $package_name 传入合格合法包名
     * @return string
     */
    public static function StartCrawling($package_name = '297606951')
    {
        $url = 'https://itunes.apple.com/us/lookup?id='.$package_name;
        //$html_doc = self::makeCurl($url);
        $html_doc = json_decode(file_get_contents($url), true);

        $html_json_data = json_decode($html_doc, true);

        $result = [];
        if ($html_json_data['resultCount'] < 1) {
            return 'This '.$package_name.'name was not found';
        }

        $creative_array = $html_json_data['results'][0];
        $result['name'] = $html_json_data['results'][0]['trackCensoredName'];
        $result['icon'] = $html_json_data['results'][0]['artworkUrl100'];
        $result['description'] = $html_json_data['results'][0]['description'];
        $result['min_os_vs'] = $html_json_data['results'][0]['minimumOsVersion'];
        $result['category'] = $html_json_data['results'][0]['primaryGenreName'];

        $result['screenshot'] = [];
        foreach($creative_array['screenshotUrls'] as $src){
            $result['screenshot'][] = ["url" => $src];
        }

        return json_encode($result);
    }

    /**
     * @param $url iTunes url
     * @param bool $post_data
     * @param bool $ignore_ssl
     * @param string $dataType
     * @return array|mixed
     */
    protected static function makeCurl($url,$post_data=false,$ignore_ssl=true,$dataType='text'){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, 'Chrome 42.0.2311.135 Pentamob');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        if($ignore_ssl){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //信任任何证书
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 检查证书中是否设置域名,0不验证
        }
        /*$proxy = ['host'=>'127.0.0.1','port'=>'1080','type'=>CURLPROXY_SOCKS5];   //代理

        curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_PROXYTYPE, $proxy['type']);
        curl_setopt($curl, CURLOPT_PROXY, $proxy['host']);
        curl_setopt($curl, CURLOPT_PROXYPORT, $proxy['port']);*/

        if($post_data){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        }
        $data = curl_exec($curl);

        $status = curl_getinfo($curl);
        $error_info = [ //组装错误信息
            'error_no'   => curl_errno($curl),
            'error_info' => curl_getinfo($curl),
            'error_msg'  => curl_error($curl),
            'result'     => $data
        ];

        curl_close($curl);
        if (isset($status[ 'http_code' ]) && $status[ 'http_code' ] == 200) {
            if ($dataType == 'json') {
                $data = json_decode($data, true);
            }
            return $data;
        } else {
            return $error_info;
        }
    }
}
