<?php
error_reporting(0);
header('Content-type: text/json;charset=utf-8');
const CacheTime = 1;
const CacheFlag = 0;
const CachePath = 'cache/iqiyi1080';//文件放在网站一级目录下
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
            'cookie' => 'QC005=7bdeb72d1eb10ca592b84df1ad113b4c; QC006=6f2b66b76e5e48a48d49c210aaed8933; T00404=cfb663368da0d451299efff155ab8946; QP0030=1; TQC030=1; QC173=0; P00004=.1682355617.e3098a1aa9; P111114=1682607579; PCAU=0; QP0034={"v":6,"dm":{"wv":1},"m":{"wm-vp9":1,"wm-av1":1,"m4-hevc":1},"hvc":false}; QP0035=2; QY_PUSHMSG_ID=7bdeb72d1eb10ca592b84df1ad113b4c; QIYUECK=qy_pc_8ebdb79e96e946c68491f92c7a8ddc1e; P1111129=1683396500; TQC022={"au":"32hRKyWVoRl7iFpivae6UHzvbOouog3QMKqVzXe7DGGh3oSgZY0QTwh5Ve968egvXab1","ak":"8ffffcaffXm2jYVOqEKpvTeMU5KtTEQTG88IXnKGsm1w4M009m12qZ8m4"}; QY00001=1734511708; QP008=360; T00700=EgcI9L-tIRABEgcI58DtIRABEgcI67-tIRATEgcIq8HtIRABEgcIrcHtIRABEgcI8L-tIRABEgcIz7-tIRABEgcIkMDtIRABEgcIg8DtIRABEgcI0b-tIRABEgcI4b-tIRABEgcIhcDtIRABEgcIi8HtIRAlEgcI87-tIRABEgcI7L-tIRABEgcImMDtIRABEgcI57-tIRABEgcIisHtIRAM; QC021=[{"key":"青春环游记第4季"},{"key":"青春环游记第3季"},{"key":"无名"},{"key":"满江红"},{"key":"花木兰2"}]; Hm_lvt_53b7374a63c37483e5dd97d78d9bb36e=1683431665; QC142=9b9c2b170b5c131e; QC186=false; QC190=false; QC187=true; QC008=1682355606.1683467125.1683476001.37; QC007=DIRECT; QC189=5257_B,5465_B,5924_D,5468_B,6151_A,5592_B,6031_B,5670_B,5830_C,6050_B,6082_A,6091_B,6312_A,6237_B,6249_C; QC191=; nu=0; QP0033=1; QP007=1200; P00001=a1bvvEHdsm3JCpBTOn08MZZ23ka5PCBk4qm3XrSkcBxtRYxEPnkqlASMUOoQNm2Q27YZj71; P00003=1734511708; P00010=1734511708; P01010=1683561600; P00007=a1bvvEHdsm3JCpBTOn08MZZ23ka5PCBk4qm3XrSkcBxtRYxEPnkqlASMUOoQNm2Q27YZj71; P00PRU=1734511708; QC175={"upd":true,"ct":1683476341728}; P00002={"uid":1734511708,"pru":1734511708,"user_name":"186****3296","nickname":"\u7528\u623767628c5c","pnickname":"\u7528\u623767628c5c","type":11,"email":""}; QC163=1; QC160={"type":3,"conformLoginType":0}; QC170=1; QC010=58571822; QC179={"vipTypes":"1","userIcon":"//www.iqiyipic.com/common/fix/headicons/male-130.png","uid":1734511708,"iconPendant":"","bannedVip":false,"allVip":true,"validVip":true}; QP0013=1; QP0014=1; QP0027=167; __dfp=a1a6097e413fe8470e9e92246c685693f9a6788a0cd0f6d952c57b56cfd7e7d7d5@1683651606403@1682355607403; QC188=false; QP0037=45; QP0036=202358|300.123; QC193=438724200,300,9; IMS=IggQABj_uOSiBiomCiBjZTFhOTFlMzMzYzc3MjQ0MzdjNjY2MjhjNTdiYTU3NBAAIgByJAogY2UxYTkxZTMzM2M3NzI0NDM3YzY2NjI4YzU3YmE1NzQQAA',
            'apiParam' => [
                'k_uid' => '7bdeb72d1eb10ca592b84df1ad113b4c',                                                                        // 账号
                'uid' => '1734511708',                                                                                                // 账号
                'dfp' => 'a1a6097e413fe8470e9e92246c685693f9a6788a0cd0f6d952c57b56cfd7e7d7d5',                                        // 账号
                'authKey' => '88804bf0d44d82106420cf2490c69ff3',                                                                      // 账号
                'pck' => 'a1bvvEHdsm3JCpBTOn08MZZ23ka5PCBk4qm3XrSkcBxtRYxEPnkqlASMUOoQNm2Q27YZj71',                                // F12调试搜索authKey获取
            ],
        ]
    ];
    public function index()
    {
        $this->domain = 'https://' . $_SERVER["HTTP_HOST"].'/cut/';     // 如有配置https 否则 http 协议即可
        if (!file_exists(CachePath)) {
            mkdir(CachePath, 0777, true);
        }
        $this->url = $_REQUEST['url'];
    
        if ($_REQUEST['url'] == '') {die('请输入URL网址！');
        }
        $cache_file = CachePath . '/' . md5($this->url) . '.m3u8';
        if (!file_exists($cache_file) || filemtime($cache_file) + CacheTime < time() || CacheFlag == 0) {
            $m3u8 = $this->parse();
         
            if ($m3u8 && strstr($m3u8,"#EXTM3U") == true) {
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
            $apiParam['bid'] = 600;
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
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36",
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