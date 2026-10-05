<?php
error_reporting();
header('Content-type: text/json;charset=utf-8'); //查看源代码形式显示  如果不以代码形式显示就用//注释掉这个

define('VS_', 60*60*2);//2小时
define('VS__', 1);//是否缓存;1=缓存;0=不缓存
define('PATH', 'cache');//缓存创建的文件夹名称
define('HOSTNAME','sf.huomiao.cc/iqy');
define('CKDIR','ck.txt');
$ipsq = false;//true 开启ip授权 false 关闭 开启后，只有授权过的ip才能调用此接口
$ips = '127.0.0.1|61.136.164.154|58.39.110.157';// 授权ip列表，什么隔开都行
$dytip['code'] = 200;
$dytip['url'] = 'http://api.zxyan.cn/';//防盗视频地址
$dytip['msg'] = '火苗API：api.huomiao.cc';
    if ($ipsq == true) {
        $ip = trim($_SERVER['REMOTE_ADDR']);
        if (strstr($ips, $ip) == false) {
            exit(json_encode($dytip,456));
        }
    }


if (!file_exists(PATH)) {
mkdir(PATH, 0777, true);//文件夹权限
}
//redisp配置
//     $r = $_GET['r'] ?? '';
//     $f = $_GET['f'] ?? '';
//     $redisConfig = [
//     'host' => '127.0.0.1',
//     'port' => 6379,
//     'pass' => '',//没有密码请留空
// ];
// // 连接redis
// $redis = new Redis();
// $redis->connect($redisConfig['host'], $redisConfig['port']);
// if (empty($redisConfig['pass'])==false) {
//     $redis->auth($redisConfig['pass']);
// }

/*========================Cookie轮询 开始==============================*/
/*========================Cookie轮询 开始==============================*/
/*========================Cookie轮询 开始==============================*/
// $ckk = file_get_contents(CKDIR);
//  preg_match('/P00001=.*?;/',$ckk, $mc);
//   $tk = str_replace('P00001=','',$mc[0]);
//   $tk = str_replace(';','',$tk);
// echo $tk;
// exit;
$list = array(
    // "0" => "4eHYFXGvm1U2H5kGLY1Oz9EXd5oHrsN1g52Xm1m3Ht2HAeXpWjl60IkqOFrMupShUaTsRb5",//填你的ck//p0001=只要这数据
    // "1" => "",
    "0" => "",//
    
   // "2" => "33rP2vGxSCaWdIyMePdvcYHDsBrPFog9K59CEJd56Twtkhf9R7iTQlg9WtyfQbXm3M64e",//

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
//现在是半小时轮训一次ck

//print_r($new_list);

/*========================Cookie轮询 结束==============================*/
/*========================Cookie轮询 结束==============================*/
/*========================Cookie轮询 结束==============================*/



$url = $_GET['url'];
 $url = str_replace('m.iqiyi.com','www.iqiyi.com',$url);

if($url==''){
$player=[];
$player['code']=404;
$player['msg']='请输入地址';
$json=json_encode($player,456);
print_r($json);
exit;
}

$ep_file=PATH.'/'.md5($url).'.m3u8';//缓存创建的文件名称(可用MD5加密如md5($id))

//Redis检查
        // if ($redis ->exists($fileKey) === 1) {
        //      $content = $redis -> get($fileKey);
        //     echo $content;
        // }else{}

if (!file_exists($ep_file) || filemtime($ep_file)+VS_ < time() || VS__==0){//检查文件或目录和文件是否存在 与 缓存时间判断



$url = str_replace("https://","http://", $url);

$html = file_get_contents($url);
preg_match('#\"tvId\":(.*?),#',$html,$tvid);
preg_match('#\"vid\":\"(.*?)\",#',$html,$vids);
preg_match('/isMember\":(.*?),\"isNew/',$html,$bool);
$vid = $vids[1];
$tvid = $tvid[1];
$ids['member'] = $bool[1];



		if($ids['member']==='true'){//会员
       
       $Cookie = $new_list[0];
      $cishu = file_get_contents("qyty.txt");
      $chi = $cishu+1;
      $shu = file_put_contents("qyty.txt",$chi);

		
		}else{//免费
		
	    $Cookie = '';
		}





    $h2_kf_arr = array(
        1=> [3 => !0,37 => !1,40 => !0,42 => !0,48 => !0],
        2=> [],
        4=> [3 => !1],
    );

    $time = number_format(microtime(true),3,'','');
    $authkey = md5(''.$time.$tvid);
    
    $vf_api = "http://113.125.122.109:5566/?url=";






$api = $vf_api.base64_encode("https://cache.video.iqiyi.com/dash?tvid={$tvid}&bid=720&vid={$vid}&src=01010031010000000000&vt=0&rs=1&uid=&ori=pcw&ps=1&k_uid=600d56ffd00dca589d9dd99d625282bf&pt=0&d=0&s=&lid=&cf=&ct=&authKey={$authkey}&k_tag=1&dfp=e08b895391d97f4f108cc6a2c87e26fe9a47bb3645318f483c1a8f6f6164501835&locale=zh_cn&prio=%7B%22ff%22%3A%22f4v%22%2C%22code%22%3A2%7D&pck={$Cookie}&k_err_retries=0&up=&sr=1&qd_v=5&tm={$time}&qdy=u&qds=0&k_ft1=".h2_kf_m3u8($h2_kf_arr)."&k_ft4=1161084346048516&k_ft5=134217729&k_ft7=4&bop=%7B%22version%22%3A%2210.0%22%2C%22dfp%22%3A%22e08b895391d97f4f108cc6a2c87e26fe9a47bb3645318f483c1a8f6f6164501835%22%2C%22b_ft1%22%3A8%7D&ut=0");

$api = httpget($api,$Cookie);
$json = json_decode($api,true);
$vipurl = $json['data']['url'];
 $json = json_decode(preg_replace("#var tvInfoJs=#","",get_curl_iqy($vipurl)),true);
 
         $urlm3u8 =  $json['data']['program']['video'][0]['url'];
        
        if (!isset($urlm3u8)) {$urlm3u8 =  $json['data']['program']['video'][1]['url'];}
        if (!isset($urlm3u8)) {$urlm3u8 =  $json['data']['program']['video'][2]['url'];}
        if (!isset($urlm3u8)) {$urlm3u8 =  $json['data']['program']['video'][3]['url'];}
        if (!isset($urlm3u8)) {$urlm3u8 =  $json['data']['program']['video'][4]['url'];}
        if (!isset($urlm3u8)) {$urlm3u8 =  $json['data']['program']['video'][5]['url'];}
        
       
        
        $m3u8 = str_replace('http://','https://',get_curl_iqy($urlm3u8));
        
          
        $m3u8 = str_replace('&br=100','&br=100'.'&From=admin&name=',$m3u8);
        $m3u8 = str_replace('&qd_vipres=0','&qd_vipres=0'.'&From=admin&name=',$m3u8);
        
      // print_r ($m3u8);
       
      
               if(strstr($m3u8,"EXTM3U")==true){
    


	
	file_put_contents($ep_file,$m3u8);//把抓取$mp4HD这个变量的真实链接地址参数 写入缓存文件里
}
}


$json=@file_get_contents($ep_file);//读取缓存文件里的参数 加@不返回错误





        if(strstr($json,"EXTM3U")==true){


$player=[];
$player['code']=200;
$player['txt']="火苗API:api.huomiao.cc";
$player['player']="cplayer";
//$player['url']='http://'.$_SERVER['HTTP_HOST'].'/'.$ep_file;
$player['url']='https://'.HOSTNAME.'/'.$ep_file;
print_r(json_encode($player,456));
}else{
$player=[];
$player['code']=404;
$player['txt']="火苗API:api.huomiao.cc";
$player['msg']="没解析出地址！";
print_r(json_encode($player,456));
}


          
 
   
    
function httpget($url,$Cookie)//数据传输时间
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
		"Host: cache.video.iqiyi.com",
        "Connection: keep-alive",
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36",	
		"Origin: https://www.iqiyi.com",
		"Referer:".$url,
		"Accept-Language: zh-CN,zh;q=0.9",
		"Cookie:".$Cookie,
		     );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheaders);
    
    $data = curl_exec($ch);                                                 //运行 curl，请求网页并返回结果
    curl_close($ch);                                                        //关闭 curl
    

    return $data;
}
    function h2_kf_m3u8($h2_kf_arr) {//m3u8

    $e = [];
    $h2_kf_arr[1][37] = "!0";
    $h2_kf_arr[1][38] = "!0";
    for ($t = 1; $t <= 64; $t++){
        $e[] = $h2_kf_arr[1][$t] ? 1 : 0;
    }
    return intval(implode("",array_reverse($e)),2);
}

function get_curl_iqy($url,$post=0,$referer=0,$cookie=0,$header=0,$ua=0,$nobaody=0,$randip=0){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0); //强制协议为1.0
		$httpheader[] = "Accept:application/json, text/plain, */*";
		$httpheader[] = "Accept-Encoding:gzip, deflate, br";
		$httpheader[] = "Accept-Language:zh-CN,zh;q=0.8";
		$httpheader[] = "Connection:close";
		if($randip == 1){
			$rand_ip = get_rand_ip();
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
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_ENCODING, "gzip");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
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
 $rand_key = mt_rand(0, 9);
 $huoduan_ip= long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
 return $huoduan_ip;
}
  
