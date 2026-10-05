<?php
//授权代码

//授权代码    
error_reporting(0);
header('Content-type: text/json;charset=utf-8');
define('VS_', 3600*1);//缓存时间 60*1为1分钟 3600*1为1小时
define('VS__', 0);//是否缓存;1=缓存;0=不缓存
define('PATH', 'cache/yk');//缓存创建的文件夹名称
$hosts = "sf.huomiao.cc/yk";
if (!file_exists(PATH)) {
mkdir(PATH, 0777, true);//文件夹权限
}

$list = array(
    "0" =>"",//填你的ck
    //"1" =>"",
    //"2" =>"",
);

function dataPollingInterval( $list , $polling_time , $polling_number ) {
// 规划轮询间隔时间的参数:
$interval = false;

$arg = array(
's'=>1 , // 秒
'm'=>60 , // 分= 60 sec
'h' =>3600 , // 时= 3600 sec
'd' => 86400 , // 天= 86400 sec
);

// 判断间隔时间的类型，并计算间隔时间
foreach ( $arg as $k => $v ) {
if ( false !== stripos( $polling_time , $k ) ) {
$interval = intval( $polling_time ) * $v;
break;
}
}

// 判断间隔时间
if( !is_int( $interval ) ){
return false;
}

// 从今年开始的秒数
$this_year_begin_second = strtotime( date( 'Y-01-01 01:00:01' , time() ) );

// 当前秒数 - 今年开始的秒数，得到今年到目前为止的秒数。
$polling_time = time() - $this_year_begin_second;

// 从今年到目前为止的秒数，计算得到当前轮数
$len = count( $list ); // 总长度
$start_index = intval( $polling_time / $interval );
$start_index = $polling_number * $start_index % $len; // 轮排数量 * 轮数 , 取余 总数量。

$res = array( );

// 将轮数 指向到数组的索引，然后从这个索引开始接着往下循环遍历。
for ( $i=0; $i < $len ; ++$i ) {
$index = $i + $start_index; // 索引的变化是根据时间来变

// 当遍历索引超过数组的最大下标时，
if ( $index >= $len ) {
$index = $index - $len ;
}
$res[] = $list[ $index ]; // 存入结果
}
return $res;
}

$new_list=dataPollingInterval($list,'1800 sec',1); // 秒=1 sec 分=60 sec 时=3600 sec 天=86400 sec

//print_r($new_list);

/*========================Cookie轮询 结束==============================*/
/*========================Cookie轮询 结束==============================*/
/*========================Cookie轮询 结束==============================*/




$cookieNO1=$new_list[0];//VIPcookie1


/*
$cookieNO2=YOUKU2;//VIPcookie2

$cookieNO3=YOUKU3;//VIPcookie3

*/


$url=$_GET['url'];

if(!$url){
$player=[];
$player['code']=404;
$player['msg']='请输入地址';
$json=json_encode($player,456);
print_r($json);
exit;
}

$xid = explode(".html", basename($url))[0];

$vid = str_ireplace('id_','',$xid);


//$m3u8=youkuapi($url,$vid,$cookieNO1);
//print_r($m3u8);

$ep_file=PATH.'/'.md5($vid).'.m3u8';
if (!file_exists($ep_file) || filemtime($ep_file)+VS_ < time() || VS__==0){

$m3u8=youkuapi($url,$vid,$cookieNO1);

if(strstr($m3u8,"EXTINF:")==true){
$m3u8x=$m3u8;
file_put_contents($ep_file,$m3u8x);
}
}

$youkum3u8=@file_get_contents($ep_file);



if(strstr($youkum3u8,"EXTINF:")==true){
$player=[];
$player['code']=200;
$player['txt']="5566";
$player['player']="cplayer";
$player['url']='https://'.$hosts.'/'.$ep_file;
print_r(json_encode($player,456));
}else{
$player=[];
$player['code']=404;
$player['txt']="5566";
$player['msg']="没解析出地址！";
print_r(json_encode($player,456));
}




function youkuapi($url,$vid,$cookieNO1) {

$defaultUA = "122#cEpzK4oNEEJf9EpZy4p0EJponDJE7SNEEP7ZpJRBuDPpJFQLpCGwp2Z4pJEL7SwBEyGZpJLlu4Ep+FQLpoGUEELWn4yE7SNEEP7ZpERBuDPE+BQPpC76EJponDJLKMQEImWiXDnTtByWAfaPwr8S14Rqur0QgVnIlrui4MELW3vr+iOB0qDQf74gJSlOjgp1uOjNDLVr8bp6+4EEyFfDqM3bDEpxngR4ul5EDtGPt4AiJDbEfC3mqM3WE8pangL4ul0EDLVr8CpU+4EEyFfDqMfbDEpxnSp4uOIEELXZ8oL6JwTEyF3F7S32DEpadSaDyHu51MZtnBRiTJLEq+gIGRkP+8Dek8h9fh11CSLkuL/P0R4Hlnikrss2mlEJz0UIAMry3D7C83z4502GSbmm1yTp4p6BD5B4EZTSdtb8jMcciSkR2T3vr1MmAhICTwESj4T7+JhGPSqslBt/Qrdd3zt1VtdrWI5lRa3igvDQ/BW+ILb+xi2jk4tN75e8D3EQzrMeCTsgMp79iqhm4cQ3J4curS664wQG2RJjxmEUbDe4cKZP5XTxcuvfSlhx0PTyc8U0R93mzWuVGnaX0nwHMlhRKZ/Pr5Oxjcdq8vziCikuHjkK8xiqFk7kslHGl3uDes55ltaEFK54DsseJu2t4JmeC5q=";
$ckey = urlencode($defaultUA);
$time = time();
$ccode = "050F";//0505
$cna = fetch_cna();
$utid = urlencode($cna);//nkF%2FERTmyQcCARsmOAfEsdTh
$client_ip = "192.168.1.1";
$api = "https://ups.youku.com/ups/get.json?vid=$vid&ccode=$ccode&client_ip=$client_ip&utid=$utid&client_ts=$time&ckey=$ckey&site=-1&wintype=interior&p=1&fu=0&vs=1.0&rst=mp4&dq=auto&os=win&osv=&d=0&bt=pc&aw=w&needbf=1";
$content = curl($api,true);
//return $content;
//print_r($content);exit;



if(strstr($content,'屏蔽视频')||strstr($content,'视频出错')||strstr($content,'格式错误')||strstr($content,'基本信息失败')) {echo json_encode(["code"=>"404","msg"=>"视频出错，请检查视频链接是否正确"]);exit;}
$apiyouk = json_decode($content,true);
$isvip = $apiyouk['data']['fee']['ad'];//判断VIP视频
$vodbuy = $apiyouk['data']['fee']['paid_type'][0];//判断付费视频 vod 为付费

if ($isvip == 0 && $vodbuy == 'mon'){
$cookie = $cookieNO1;
$api = "https://ups.youku.com/ups/get.json?vid=$vid&ccode=$ccode&client_ip=$client_ip&utid=$utid&client_ts=$time&ckey=$ckey&site=-1&wintype=interior&p=1&fu=0&vs=1.0&rst=mp4&dq=auto&os=win&osv=&d=0&bt=pc&aw=w&needbf=1";//&ptoken=$ptoken
$content = vipcurl($api,$cookie);
$apiyouk = json_decode($content,true);
$iscookie = $apiyouk['data']['user']['code'];//判断COOKIE失效
if ($iscookie == -1){
$cookie = $cookieNO2;
$api = "https://ups.youku.com/ups/get.json?vid=$vid&ccode=$ccode&client_ip=$client_ip&utid=$utid&client_ts=$time&ckey=$ckey&site=-1&wintype=interior&p=1&fu=0&vs=1.0&rst=mp4&dq=auto&os=win&osv=&d=0&bt=pc&aw=w&needbf=1";//&ptoken=$ptoken
$content = vipcurl($api,$cookie);
$apiyouk = json_decode($content,true);
$iscookie = $apiyouk['data']['user']['code'];//判断COOKIE失效
}
if ($iscookie == -1){
$cookie = $cookieNO3;
$api = "https://ups.youku.com/ups/get.json?vid=$vid&ccode=$ccode&client_ip=$client_ip&utid=$utid&client_ts=$time&ckey=$ckey&site=-1&wintype=interior&p=1&fu=0&vs=1.0&rst=mp4&dq=auto&os=win&osv=&d=0&bt=pc&aw=w&needbf=1";//&ptoken=$ptoken
$content = vipcurl($api,$cookie);
$apiyouk = json_decode($content,true);
$iscookie = $apiyouk['data']['user']['code'];//判断COOKIE失效
}
}

preg_match('/height":1638,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];
if(empty($vipurl)){preg_match('/height":1080,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":960,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":720,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":718,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":716,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":714,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":712,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":710,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":708,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":706,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":704,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":702,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":700,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":698,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":696,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":694,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":692,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":690,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":600,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":580,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":560,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":550,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":548,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":546,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":544,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":542,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":540,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":538,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":536,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":534,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":532,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":530,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":528,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":526,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":524,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":522,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":520,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":518,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":516,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":514,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":512,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":510,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":508,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":506,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":504,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":502,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":500,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":498,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":496,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":494,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":492,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":490,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":488,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":486,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":484,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":482,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":480,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":420,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":418,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":416,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":414,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":412,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":410,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":408,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":406,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":404,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":402,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":400,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":398,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":396,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":394,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":392,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":390,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":388,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":386,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":384,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":382,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":380,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":378,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":376,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":374,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":372,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":370,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":368,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":366,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":364,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":362,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":360,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":358,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":356,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":354,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":352,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":350,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":348,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":346,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":344,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":342,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":340,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":338,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":336,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":334,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":332,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":330,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":280,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":278,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":276,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":274,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":272,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":270,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":268,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":266,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":264,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":262,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":260,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":258,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":256,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":254,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":252,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":250,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":230,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":220,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":218,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":216,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":214,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":212,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":210,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":208,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":206,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":204,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":202,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":200,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":198,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":196,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":194,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":192,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":190,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":188,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":186,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":184,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":182,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
if(empty($vipurl)){preg_match('/height":180,"logo":"none","m3u8_url":"(.*)"/isU',$content,$m3u8);$vipurl = $m3u8[1];}
$m3u8url_html = get_curl_yk($vipurl);
$m3u8file = preg_replace('/((http|https)?:\/\/(.*?)\/)/i','https://yys-valipl-vip.cp12.wasu.tv/',$m3u8url_html);
if(!$m3u8file){
$m3u8file=get_0523_man($url,$vid,$cookie);
}
return $m3u8file;
exit;

}




//==================================付费视频================================
//==================================付费视频================================
//==================================付费视频================================
function get_0523_man($url,$vid,$cookie) {

$api_param['vid'] = $vid;
$api_param['ccode'] = '0523';
$api_param['client_ts'] = time();
$api_param['client_ip'] = '192.168.1.1';
$api_param['os'] = 'Ykplayer';
$api_param['ckey'] = '7B19C0AB12633B22E7FE81271162026020570708D6CC189E4924503C49D243A0DE6CD84A766832C2C99898FC5ED31F3709BB3CDD82C96492E721BDD381735026';
$api_param['utid'] = "";
$api_url = 'https://ups.youku.com/ups/get.json?'.http_build_query($api_param);
$apiyk_html = get_curl_yk($api_url,0,'https://v.youku.com',0,0,0,0,1);
$apiyk_arr = json_decode($apiyk_html,true);
$isvip = $apiyk_arr['data']['fee']; //判断VIP视频
$apiyk_error = $apiyk_arr['data']['error']['code']; //判断错误代码
$vodbuy = $apiyk_arr['data']['fee']['paid_type'][0]; //判断付费视频 vod 为付费
$apiyk_urlbox = $apiyk_arr['data']['stream'];
$apiyk_geturl_arr = get_data_m3u8_yk($apiyk_urlbox);
$apiyk_m3u8url = $apiyk_geturl_arr['m3u8_url'];
$apiyk_m3u8url_html = curl($apiyk_m3u8url);
$url_box = inter($apiyk_m3u8url_html, ',', '#');
$url_box = trim($url_box);
$url_box = str_replace( array("/r/n", "/r", "/n" , PHP_EOL ), '',$url_box);
$url_box = parse_url($url_box);
if (empty($url_box['path'])||$url_box['path']=='null'||$url_box['path']==''){
echo json_encode(get_0523_vip($url,$vid,$cookie));exit;//判断有无视频地址
}
$v_url['path'] = str_replace('-00001.ts','.m3u8',$url_box['path']);
$v_url['path'] = str_replace('-0.ts','.m3u8',$v_url['path']);
$v_url['query'] = str_replace('ccode=0523','ccode=0502',$url_box['query']);//伪装
$m3u8url = 'https://lvo-live.youku.com/youku'.$v_url['path'].'?'.$v_url['query'];
$m3u8url = preg_replace('/(ups_client_netip=(.*?)&)/i','ups_client_netip=0&QQ=335583&',$m3u8url);
$m3u8url = preg_replace('/(ups_userid=(.*?)&)/i','ups_userid='. time() .'&From=www.nxflv.com&',$m3u8url);
$m3u8file = get_curl_yk($m3u8url,0,'https://m.youku.com',0,0,0,0,1);
$m3u8file = str_replace('lvo-live.youku.com','vali.cp31.ott.cibntv.net',$m3u8file);
$m3u8file = preg_replace('/((http|https)?:\/\/(.*?)\/)/i','https://yys-valipl-vip.cp12.wasu.tv/',$m3u8file);

if(empty($m3u8file)||strstr($m3u8file,'Forbidden')||strstr($m3u8file,'NoSuchKey')) {
echo json_encode(get_0523_vip($url,$vid,$cookie));exit;//判断有无视频地址
} else {
return $m3u8file;
}
}

function get_0523_vip($url,$vid,$cookie) {
$vinfo['cookie'] = str_replace('P_pck_rm=','',$cookie);
$api_param['vid'] = $vid;
$api_param['ccode'] = '0523';
$api_param['client_ts'] = time();
$api_param['client_ip'] = '192.168.1.1';
$api_param['os'] = 'Ykplayer';
$api_param['ptoken'] = str_replace(';','',$vinfo['cookie']);
$api_param['ckey'] = '7B19C0AB12633B22E7FE81271162026020570708D6CC189E4924503C49D243A0DE6CD84A766832C2C99898FC5ED31F3709BB3CDD82C96492E721BDD381735026';
$api_param['utid'] = "";
$api_url = 'https://ups.youku.com/ups/get.json?'.http_build_query($api_param);
$apiyk_html = get_curl_yk($api_url,0,'https://m.youku.com',$cookie,0,0,0,1);
$apiyk_arr = json_decode($apiyk_html,true);
$apiyk_urlbox = $apiyk_arr['data']['stream'];
$apiyk_geturl_arr = get_data_m3u8_yk($apiyk_urlbox);
$apiyk_m3u8url = $apiyk_geturl_arr['m3u8_url'];
$apiyk_m3u8url_html = GlobalBase::curl($apiyk_m3u8url);
$url_box = inter($apiyk_m3u8url_html, ',', '#');
$url_box = trim($url_box);
$url_box = str_replace( array("/r/n", "/r", "/n" , PHP_EOL ), '',$url_box);
$url_box = parse_url($url_box);
if (empty($url_box['path'])||$url_box['path']=='null'||$url_box['path']==''){
return GlobalBase::ziyuanvideo($url);exit;//判断有无视频地址
}
$v_url['path'] = str_replace('-00001.ts','.m3u8',$url_box['path']);
$v_url['path'] = str_replace('-0.ts','.m3u8',$v_url['path']);
$v_url['query'] = str_replace('ccode=0523','ccode=0503',$url_box['query']);//伪装
$m3u8url = 'https://lvo-live.youku.com/youku'.$v_url['path'].'?'.$v_url['query'];
$m3u8url = preg_replace('/(ups_client_netip=(.*?)&)/i','ups_client_netip=0&QQ=335583&',$m3u8url);
$m3u8url = preg_replace('/(ups_userid=(.*?)&)/i','ups_userid='. time() .'&From=www.nxflv.com&',$m3u8url);
$m3u8file = get_curl_yk($m3u8url,0,'https://m.youku.com',0,0,0,0,1);
$m3u8file = str_replace('lvo-live.youku.com','vali.cp31.ott.cibntv.net',$m3u8file);
$m3u8file = preg_replace('/((http|https)?:\/\/(.*?)\/)/i','https://yys-valipl-vip.cp12.wasu.tv/',$m3u8file);

if(empty($m3u8file)||strstr($m3u8file,'Forbidden')||strstr($m3u8file,'NoSuchKey')) {
return "";
} else {
return $m3u8file;
}
}







function inter($str, $start, $end) {
$wd2 = '';
if ($str && $start) {
$arr = explode($start, $str);
if (count($arr) > 1) {
$wd = $arr[1];
if ($end) {
$arr2 = explode($end, $wd);
if (count($arr2) > 1) {
$wd2 = $arr2[0];
} else {
$wd2 = $wd;
}
} else {
$wd2 = $wd;
}
}
}
return $wd2;
}

function get_data_m3u8_yk($date2=[]) {
global $vodinfo;
$data_arr = [];
$re_arr = [];
$date1 = array_reverse($date2);
foreach ($date1 as $k => $v){
$data_arr[$v['stream_type']]['m3u8_url'] = $v['m3u8_url'];
$data_arr[$v['stream_type']]['width'] = $v['width'];
$data_arr[$v['stream_type']]['height'] = $v['height'];
}
if(empty($data_arr)){
$re_arr['stream_type'] = null;
$re_arr['m3u8_url'] = null;
$re_arr['width'] = null;
$re_arr['height'] = null;
return $re_arr;
}
if(isset($data_arr['mp4hd3v2'])){
$re_arr['stream_type'] = 'mp4hd3v2';
$re_arr['m3u8_url'] = $data_arr['mp4hd3v2']['m3u8_url'];
$re_arr['width'] = $data_arr['mp4hd3v2']['width'];
$re_arr['height'] = $data_arr['mp4hd3v2']['height'];
}elseif(isset($data_arr['mp4hd2v3'])){
$re_arr['stream_type'] = 'mp4hd2v3';
$re_arr['m3u8_url'] = $data_arr['mp4hd2v3']['m3u8_url'];
$re_arr['width'] = $data_arr['mp4hd2v3']['width'];
$re_arr['height'] = $data_arr['mp4hd2v3']['height'];
}elseif(isset($data_arr['mp4hd2v2'])){
$re_arr['stream_type'] = 'mp4hd2v2';
$re_arr['m3u8_url'] = $data_arr['mp4hd2v2']['m3u8_url'];
$re_arr['width'] = $data_arr['mp4hd2v2']['width'];
$re_arr['height'] = $data_arr['mp4hd2v2']['height'];
}elseif(isset($data_arr['mp4hd2'])){
$re_arr['stream_type'] = 'mp4hd2';
$re_arr['m3u8_url'] = $data_arr['mp4hd2']['m3u8_url'];
$re_arr['width'] = $data_arr['mp4hd2']['width'];
$re_arr['height'] = $data_arr['mp4hd2']['height'];

}elseif(isset($data_arr['mp4hd'])){
$re_arr['stream_type'] = 'mp4hd';
$re_arr['m3u8_url'] = $data_arr['mp4hd']['m3u8_url'];
$re_arr['width'] = $data_arr['mp4hd']['width'];
$re_arr['height'] = $data_arr['mp4hd']['height'];
}elseif(isset($data_arr['mp4sd'])){
$re_arr['stream_type'] = 'mp4sd';
$re_arr['m3u8_url'] = $data_arr['mp4sd']['m3u8_url'];
$re_arr['width'] = $data_arr['mp4sd']['width'];
$re_arr['height'] = $data_arr['mp4sd']['height'];
}elseif(isset($data_arr['3gphd'])){
$re_arr['stream_type'] = '3gphd';
$re_arr['m3u8_url'] = $data_arr['3gphd']['m3u8_url'];
$re_arr['width'] = $data_arr['3gphd']['width'];
$re_arr['height'] = $data_arr['3gphd']['height'];
}elseif(isset($data_arr['flvhd'])){
$re_arr['stream_type'] = 'flvhd';
$re_arr['m3u8_url'] = $data_arr['flvhd']['m3u8_url'];
$re_arr['width'] = $data_arr['flvhd']['width'];
$re_arr['height'] = $data_arr['flvhd']['height'];
}
return $re_arr;
}

function fetch_cna() {
preg_match('#Etag="(.*)";#',file_get_contents("http://log.mmstat.com/eg.js"),$cnn);
$cna = $cnn[1];
if (!$cna) {$cna = 'oqikEO1b7CECAbfBdNNf1PM1';}
return $cna;
}

function get_curl_yk($url,$post=0,$referer=0,$cookie=0,$header=0,$ua=0,$nobaody=0,$randip=0,$h2_httpheader=0) {
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0); //强制协议为1.0
$httpheader[] = "Accept:application/json, text/plain, */*";
$httpheader[] = "Accept-Encoding:gzip, deflate, br";
$httpheader[] = "Accept-Language:zh-CN,zh;q=0.8";
$httpheader[] = "Connection:close";
if($h2_httpheader){
$httpheader[] = $h2_httpheader;
}
if($randip == 1){
if($randip == 2){
$rand_ip = $_SERVER["REMOTE_ADDR"];
}else{
$rand_ip = get_rand_ip();
}
$httpheader[] = "X-FORWARDED-FOR:".$rand_ip;
$httpheader[] = "CLIENT-IP:".$rand_ip;
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);

if($post){
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
}
if($header){
curl_setopt($ch, CURLOPT_HEADER, TRUE);
}
if($cookie){
curl_setopt($ch, CURLOPT_COOKIE, $cookie);
}
if($referer){
curl_setopt($ch, CURLOPT_REFERER, $referer);
}
if($ua){
curl_setopt($ch, CURLOPT_USERAGENT,$ua);
}else{
curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36');
}
if($nobaody){
curl_setopt($ch, CURLOPT_NOBODY,1);

}
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_ENCODING, "gzip");
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
$ret = curl_exec($ch);
curl_close($ch);
return $ret;
}

function curl($url){
$curl = curl_init();
//$Agent = $_SERVER['HTTP_USER_AGENT'];//获取浏览器相关参数
$header = array(
"X-FORWARDED-FOR:".get_rand_ip(),
"CLIENT-IP:".get_rand_ip(),
"X-Real-IP:".get_rand_ip(),
"referer:http://video.tudou.com/",//模拟来路访问
"Connection: Keep-Alive",//可持久连接、连接重用。。。避免了重新建立连接
"User-Agent:Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36",
//"Content-Length: 326",
"Accept: application/json, text/javascript, */*; q=0.01",
"Accept-Language: zh-CN,zh;q=0.9",
'cookie:',
);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $header);//读头部数据
//curl_setopt($curl, CURLOPT_POSTFIELDS, $post_string);//post的时候用
curl_setopt($curl, CURLOPT_FOLLOWLOCATION,1);//重定向处理
curl_setopt($curl, CURLOPT_HEADER,0); //显示头部数据为1 不显示为0
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);// 在尝试连接时等待的秒数
curl_setopt($curl, CURLOPT_TIMEOUT, 10);// 最大执行时间
$content = curl_exec($curl); //抓取URL并把它传递给浏览器
curl_close($curl);//释放curl句柄
//print_r($content);//测试输出
return $content;
}

function vipcurl($url,$cookie){
$curl = curl_init();
//$Agent = $_SERVER['HTTP_USER_AGENT'];//获取浏览器相关参数
$header = array(
"X-FORWARDED-FOR:".get_rand_ip(),
"CLIENT-IP:".get_rand_ip(),
"X-Real-IP:".get_rand_ip(),
"referer:http://video.tudou.com/",//模拟来路访问
"Connection: Keep-Alive",//可持久连接、连接重用。。。避免了重新建立连接
"User-Agent:Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36",
//"Content-Length: 326",
"Accept: application/json, text/javascript, */*; q=0.01",
"Accept-Language: zh-CN,zh;q=0.9",
'cookie:'.$cookie,
);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $header);//读头部数据
//curl_setopt($curl, CURLOPT_POSTFIELDS, $post_string);//post的时候用
curl_setopt($curl, CURLOPT_FOLLOWLOCATION,1);//重定向处理
curl_setopt($curl, CURLOPT_HEADER,0); //显示头部数据为1 不显示为0
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);// 在尝试连接时等待的秒数
curl_setopt($curl, CURLOPT_TIMEOUT, 10);// 最大执行时间
$content = curl_exec($curl); //抓取URL并把它传递给浏览器
curl_close($curl);//释放curl句柄
//print_r($content);//测试输出
return $content;
}

function get_rand_ip(){
$ip_long = array(
array('607649792', '608174079'), //36.56.0.0-36.63.255.255
array('975044608', '977272831'), //58.30.0.0-58.63.255.255
array('999751680', '999784447'), //59.151.0.0-59.151.127.255
array('1019346944', '1019478015'), //60.194.0.0-60.195.255.255
array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
array('1947009024', '1947074559'), //116.13.0.0-116.13.255.255
array('1987051520', '1988034559'), //118.112.0.0-118.126.255.255
array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255
);
$rand_key = mt_rand(0, 14);
$huoduan_ip= long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
return $huoduan_ip;
}