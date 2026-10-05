<?php
error_reporting(0);
header('Content-type: text/json;charset=utf-8');
const CacheTime = 60*60;
const CacheFlag = 1;
const CachePath = 'cache/iqiyi';//文件放在网站一级目录下
if (!is_dir(CachePath)) {
    mkdir(CachePath, 0777, true); //文件夹权限
}
$chunLei = new iqiyi();
$chunLei->index();
class iqiyi
{
    public $url;
    public $cookie = '';
    public $tg = '火苗';  // 自行修改
    public $domain = '127.0.0.1';
    public $iqiyiAccountList = [
        0 => [
            'cookie' => '',
            'apiParam' => [
                'k_uid' => '0113a90cec4a70565d157fdfb20540f4',                                                                        // 账号
                'uid' => '1550198296',                                                                                                // 账号
                'dfp' => 'a1dbca06d3cb174e1aa60300686dddd7941a9fecbf7cafb092fe402b251fb509f7',                                        // 账号
                'authKey' => '4e5fa020fce76ff4493ed4d84c4bbe04',                                                                      // 账号
                'pck' => 'b1GMSLapptnm20CFIkJoFCm2USuIGQsbOm2a2m1E0PLDvvDQFn0T3cWQq7yQaGvcwX27xs9b',                                // F12调试搜索authKey获取
            ],
        ]
    ];
    public function index()
    {
        $this->domain = 'https://' . $_SERVER["HTTP_HOST"].'/iqy/';     // 如有配置https 否则 http 协议即可
        if (!file_exists(CachePath)) {
            mkdir(CachePath, 0777, true);
        }
        $this->url = $_REQUEST['url'];
        if ($_REQUEST['url'] == '') {die('请输入URL网址！');
        }
        $cache_file = CachePath . '/' . md5($this->url) . '.m3u8';
        if (!file_exists($cache_file) || filemtime($cache_file) + CacheTime < time() || CacheFlag == 0) {
            $m3u8 = $this->parse();
            if ($m3u8) {
                file_put_contents($cache_file, $m3u8);
                $this->ajaxmsg(
                    [
                        'code' => 200,
                        'msg' => "解析成功！：{$this->tg}",
                        'url' => $this->domain . $cache_file
                    ]
                );die(json_encode($this, 456));
            } else {
                $this->ajaxmsg([
                    'code' => 404, 
                    'msg' => "解析失败！：{$this->tg}"
                ]);die(json_encode($this, 456));
            }
        }
        $json = @file_get_contents($cache_file);
        if (strstr($json, 'EXTM3U') == true) {
            $this->ajaxmsg([
                'code' => 200, 
                'msg' => "缓存调用成功！：{$this->tg}",
                'url' => $this->domain . $cache_file,
            ]);die(json_encode($this, 456));
        } else {
            $this->ajaxmsg([
                'code' => 404, 
                'msg' => "解析失败！：{$this->tg}"
            ]);die(json_encode($this, 456));
        }
    }

    public function parse()
    {
        $html = $this->curl($this->url);
        preg_match('#window.Q.PageInfo.playPageInfo={\"tvId\":(.*?),#', $html, $tvid);
        preg_match('#\"vid\":\"(.*?)\",#', $html, $vids);
        preg_match('#\"member\":\"(.*?)\",#', $html, $bool);
        preg_match('#param\[\'isMember\'\]\s*=\s*"(.*)";#', $html, $bool);
        preg_match('#"isMember":(.*?),#', $html, $bool2);
        $vid = $vids[1];
        $tvid = $tvid[1];
        $ids['member'] = $bool[1];
        $h2_kf_arr = [
            1 => [3 => !0, 37 => !1, 40 => !0, 42 => !0, 48 => !0, 50 => !0],
            2 => [1 => !1, 2 => !1, 3 => !1, 4 => !1, 5 => !1, 6 => !1],
            4 => [3 => !0, 5 => !1, 14 => !0, 27 => !0, 28 => !1, 41 => !1, 46 => !0, 51 => !0],
            5 => [1 => !0, 25 => !1],
            7 => [3 => !0],
        ];
        // 请求账号参数定义
        $apiParam = [
            'tvid' => $tvid,
            'bid' => 600,                                                                         //清晰度 600：蓝光1080p，500：超清720p，300：高清480p，100：流畅360p
            'vid' => $vid,
            'src' => '01010031010000000000',
            'vt' => 0,
            'rs' => 1,
            'uid' => '',
            'ori' => 'pcw',
            'ps' => 1,                                                                            //参数ps=1和ps=0 的区别
            'k_uid' => '',
            'pt' => 0,
            'd' => 0,
            's' => '',
            'lid' => '',
            'cf' => '',
            'ct' => '',
            'authKey' => '',
            'k_tag' => 1,
            'dfp' => '',
            'locale' => 'zh_cn',
            'prio' => '{\"ff\":\"f4v\",\"code\":2}',
            'pck' => '',
            'k_err_retries' => 0,
            'up' => '',
            'sr' => 1,
            'qd_v' => 5,
            'tm' => $this->msectime(),
            'qdy' => 'u',
            'qds' => 0,
            //'k_ft1' => $this->getKft($h2_kf_arr, 1),
            'k_ft1' => '706436220846084',
            'k_ft4' => '1161084347621380',
            'k_ft5' => '262145',
            'k_ft7' => 4,
            'bop' => json_encode(['version' => '10.0', 'dfp' => '']),
            'ut' => 1,                                                                            // 1：1080p，0：其他
        ];

        $iqiyiAccountList = $this->iqiyiAccountList;
        if ($ids['member'] == 'true' || $bool2[1] == 'true') {
            $Index = mt_rand(0, count($iqiyiAccountList) - 1);
            $this->cookie = $iqiyiAccountList[$Index]['cookie'];
            $apiParam = array_merge($apiParam, $iqiyiAccountList[$Index]['apiParam']);
            $apiParam['pck'] = $iqiyiAccountList[$Index]['apiParam']['pck'];
            $apiParam['bop'] = json_encode(['version' => '10.0', 'dfp' => $iqiyiAccountList[$Index]['apiParam']]['dfp']); //print_r($apiParam);exit;
        } else {
            $apiParam['bid'] = 500;
            $apiParam['k_uid'] = '';
            $apiParam['uid'] = '';
            $apiParam['dfp'] = '';
            $apiParam['authKey'] = '';
            $this->cookie = '';
        }

        //print_r($apiParam);exit;
        $cookie = $this->cookie;
        $args = "/dash?" . http_build_query($apiParam);
        $apiURL = 'http://116.255.154.75:7870/index/getvf?args=' . base64_encode($args);   // vf验签地址
        $html = $this->curl($apiURL);                                                  // echo $html;exit;
        $apiParam['vf'] = json_decode($html, true)['data'];                            // print_r($apiParam);exit;

        $apiURL = "https://cache.video.iqiyi.com/dash?" . http_build_query($apiParam); // echo $apiURL;exit;
        $html = $this->curl($apiURL, $cookie);                                         // echo $html;exit;
        $json = json_decode($html, true);
        if ($json['code'] != 'A00000') {
            return '';
        }
        $m3u8Video = $json['data']['program']['video'];
        foreach ($m3u8Video as $k => $v) {
            if (!isset($v['m3u8']) || !$v['m3u8']) {
                unset($m3u8Video[$k]);
            }
        }
        array_multisort(array_column($m3u8Video, 'bid'), SORT_DESC, $m3u8Video);
        return  str_replace("http://", "https://", $m3u8Video[0]['m3u8']);
    }

    public function curl($url, $cookie = '')
    {
        $header = array(
            "referer: https://www.iqiyi.com/",
            "Connection: Keep-Alive",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.25 Safari/537.36 Core/1.70.3883.400 QQBrowser/10.8.4559.400",
            "Accept: application/json, text/javascript, */*; q=0.01",
            "Accept-Language: zh-CN,zh;q=0.9",
            "Cookie: " . $cookie,
        );
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        $content = curl_exec($curl);
        curl_close($curl);
        return $content;
    }
    public function ajaxmsg($data)
    {
        echo json_encode($data);
        exit;
    }
    public function msectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }
}