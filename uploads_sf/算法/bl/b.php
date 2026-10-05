<?php 
error_reporting(0);
header('Content-type: text/json;charset=utf-8');

$cookies=" 你的cookies";

$url=$_GET['url'];

if(!$url){
$player=[];
$player['code']=404;
$player['msg']='请输入地址';
$json=json_encode($player);
print_r($json);
exit;
}


if (strstr($url,'play') == true && strstr($url,'ep') == true || strstr($url,'play') == true && strstr($url,'ss') == true) {

    $html = curl($url,$cookies);
    $html = explode('INITIAL_STATE__=',$html)[1];
    $json = explode(';(function(){var s;(',$html)[0];
    $ep_info = json_decode($json,true);
    $aid = $ep_info['epInfo']['aid'];
    $cid = $ep_info['epInfo']['cid'];
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
$api = "https://api.bilibili.com/x/player/playurl?cid=$cid&avid=$aid&qn=112";//&high_quality=1  &qn=1
        //"https://api.bilibili.com/pgc/player/web/playurl?cid=$cid&avid=$aid&qn=80";
$content = curl($api,true);//普通
//print_r($content);
// $content = curl($api,$cookies);
$data = json_decode($content,true);
$vurl = $data["data"]["durl"][0]["url"];

if (empty($vurl)||$vurl==null||$vurl=='') {
	$content = curl($api,$cookies);//VIP
	$data = json_decode($content,true);
	$vurl = $data["data"]["durl"][0]["url"];
}
/*$arr=('{"code":1,"msg":"ok","data":{"url":"'.$vurl.'","header":{"User-Agent":" 295tvyy:'.$u.'","allowCrossProtocolRedirects":" true","Referer":"'.$url.'","IP":"'.$iipp.'","true":"'.$add.'"}}}');

echo($arr);*/
//print_r($content);exit;
if (empty($vurl)||$vurl==null||$vurl=='') {
	$players['code']=201;
	$players['msg']='解析失败';
	$json=json_encode($players);
print_r($json);exit;
}
$vurl= preg_replace('/:\/\/.*?.com\//','://upos-szbyjkm8g1.bilivideo.com/',$vurl);
list($t1, $t2) = explode(' ', microtime()); 
    $time =  (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000); 
$players['code'] = 200;
$players['url'] = $vurl."&_t=".$time."&4k.baozi66.top:99&qq=1013138336";
$players['player'] = 'flv';
$players['yun'] = '4k.baozi66.top:99';
$json=json_encode($players);
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

?>