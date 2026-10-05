<?php 
error_reporting(0);
header('Content-type: text/json;charset=utf-8');

$cookies="buvid3=58AA9D52-9938-25B0-4690-F51786BCC35227445infoc; b_nut=100; i-wanna-go-back=-1; bsource=search_baidu; _uuid=5289C83B-6EF8-5873-51049-522D4C6F4710F27307infoc; header_theme_version=CLOSE; home_feed_column=4; buvid4=4A23CF10-2F3E-14C5-1523-EB0151353D9029585-023040206-H1YOlymzNa6XJPTB%2BRo5vQ%3D%3D; fingerprint=d39298847d7623146e50e1b9fc075e79; buvid_fp_plain=undefined; b_ut=5; buvid_fp=eacad704c585c4cee05cc0af5f1298f2; CURRENT_FNVAL=4048; innersign=0; CURRENT_PID=f87bf270-d207-11ed-b334-4946950e139f; DedeUserID=344187804; DedeUserID__ckMd5=6d22e18d4e763adb; b_lsid=8747C1F2_18749F7DEEF; SESSDATA=d73fbcdd%2C1696125224%2C57d82%2A41; bili_jct=ceb4a5545e3854d45e6ecd5922d422e8; sid=pqw1s198";

$url=$_GET['url'];

if(!$url){
$player=[];
$player['code']=404;
$player['msg']='请输入地址';
$json=json_encode($player,456);
print_r($json);
exit;
}


if (strstr($url,'play') == true && strstr($url,'ep') == true || strstr($url,'play') == true && strstr($url,'ss') == true) {

    $html = httpget($url);
preg_match('/episodes\":\[\{\"aid\":(.*?),\"badge/',$html,$aid);

$aid = $aid[1];

preg_match('/\"cid\":(.*?),\"cover/',$html,$cid);
$cid = $cid[1];

}elseif (strstr($url,'video') == true && strstr($url,'BV') == true) {
    $urlarr = explode('?',$url);
    $urlaum = $urlarr[0];
    $urlaum = explode('/',$urlaum);
    $videoid = $urlaum[4];
    $api = "https://api.bilibili.com/x/web-interface/view?bvid=$videoid";//https://api.bilibili.com/pgc/player/web/playurl?avid
    $content = curl($api,$cookies);
    $data = json_decode($content,true);
    $aid = $data["data"]["aid"];
    $cid = $data["data"]["cid"];
    $bvid = $data["data"]["bvid"];
}
   
   
//&qn=1为MP4 &qn=112为高清1080P+  &qn=80为高清1080P  &qn=64为高清720P &qn=32为清晰480P &qn=16为流畅360P
//https://api.bilibili.com/pgc/player/web/playurl?avid//
$api = "https://api.bilibili.com/x/player/playurl?cid=$cid&avid=$aid&qn=80";//&high_quality=1  &qn=1
        //"https://api.bilibili.com/pgc/player/web/playurl?cid=$cid&avid=$aid&qn=80";
//$content = curl($api,true);//普通
//print_r($content);
// $content = curl($api,$cookies);
//$data = json_decode($content,true);
//$vurl = $data["data"]["durl"][0]["url"];

//if (empty($vurl)||$vurl==null||$vurl=='') {
	$content = curl($api,$cookies);//VIP
	$data = json_decode($content,true);
	$vurl = $data["data"]["durl"][0]["url"];
//}
if (empty($vurl)||$vurl==null||$vurl=='') {
	$players['code']=404;
	$players['msg']='解析失败';
	$json=json_encode($players,456);
print_r($json);exit;
}

if (strstr($vurl,'upos-szbyjkm8g1.bilivideo.com') == false){

$vurl= preg_replace('/:\/\/.*?.com\//','://upos-szbyjkm8g1.bilivideo.com/',$vurl);
}

if (strstr($vurl,'upos-szbyjkm8g1.bilivideo.com') == false){
$vurl= preg_replace('/:\/\/.*?.cn:4483\//','://upos-szbyjkm8g1.bilivideo.com/',$vurl);
}

list($t1, $t2) = explode(' ', microtime()); 
    $time =  (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000); 
$players['code'] = 200;
$players['url'] = $vurl."&_t=".$time."&www.itvbox.cc";
$players['player'] = 'flv';
$players['txt'] = '猫影视_api.maoyingshi.cc';
$json=json_encode($players,456);
print_r($json);exit;

 

function curl($url,$cookie) {

    $curl = curl_init();
    curl_setopt($curl,CURLOPT_URL, $url);
    $header[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36';//
    //$header[] = 'Host: api.bilibili.com';
    $header[] = 'Accept: */*';
    $header[] = 'Range: bytes=0-';
    $header[] = 'Referer: https://www.bilibili.com';
    $header[] = 'Origin: https://www.bilibili.com';
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_ENCODING,'gzip');
    curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $return = curl_exec($curl);
    curl_close($curl);
    return $return;
}

function httpget($url)//数据传输时间
{
    $ch = curl_init();                                                      //初始化 curl
    curl_setopt($ch, CURLOPT_URL, $url);                                    //要访问网页 URL 地址
    curl_setopt($ch, CURLOPT_NOBODY, false);                                //设定是否输出页面内容
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                         //返回字符串，而非直接输出到屏幕上
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);                        //连接超时时间，设置为 0，则无限等待
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);                            //数据传输的最大允许时间超时,设为一小时
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);                       //HTTP验证方法
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);                        //不检查 SSL 证书来源
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);                        //不检查 证书中 SSL 加密算法是否存在
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);                         //跟踪爬取重定向页面
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);                            //当Location:重定向时，自动设置header中的Referer:信息
    curl_setopt($ch, CURLOPT_ENCODING, 'true');                                 //解决网页乱码问  
     
    
    $httpheaders = array( 
		 "X-FORWARDED-FOR:".long2ip(mt_rand(1884815360, 1884890111)),
		"CLIENT-IP:".long2ip(mt_rand(1884815360, 1884890111)),
		"X-Real-IP:".long2ip(mt_rand(1884815360, 1884890111)),
		"Host: www.bilibili.com",
        "Connection: keep-alive",
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36",	
	//	"Origin: https://www.iqiyi.com",
		"Referer:".$url,
		"Accept-Language: zh-CN,zh;q=0.9",
		"Cookie: buvid3=58AA9D52-9938-25B0-4690-F51786BCC35227445infoc; b_nut=100; i-wanna-go-back=-1; bsource=search_baidu; _uuid=5289C83B-6EF8-5873-51049-522D4C6F4710F27307infoc; header_theme_version=CLOSE; home_feed_column=4; buvid4=4A23CF10-2F3E-14C5-1523-EB0151353D9029585-023040206-H1YOlymzNa6XJPTB%2BRo5vQ%3D%3D; fingerprint=d39298847d7623146e50e1b9fc075e79; buvid_fp_plain=undefined; b_ut=5; buvid_fp=eacad704c585c4cee05cc0af5f1298f2; CURRENT_FNVAL=4048; innersign=0; CURRENT_PID=f87bf270-d207-11ed-b334-4946950e139f; DedeUserID=344187804; DedeUserID__ckMd5=6d22e18d4e763adb; b_lsid=8747C1F2_18749F7DEEF; SESSDATA=d73fbcdd%2C1696125224%2C57d82%2A41; bili_jct=ceb4a5545e3854d45e6ecd5922d422e8; sid=pqw1s198",
		     );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheaders);
    
    $data = curl_exec($ch);                                                 //运行 curl，请求网页并返回结果
    curl_close($ch);                                                        //关闭 curl
    

    return $data;
}