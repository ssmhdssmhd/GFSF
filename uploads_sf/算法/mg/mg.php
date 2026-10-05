<?php
header('Content-type: application/json');  //json
$url = $_GET['url'];

   if(strstr($url,"mgtv.com")==false){
        
    $arr=array("code" => "404",
	"msg" => "缺少URL");
   
	
	echo json_encode($arr,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
            exit;
            }
  	preg_match('/[0-9]+\/[0-9]+/',$url,$matches);
	$result = explode('/',$matches[0]);
	$videoId = $result[1];
	$clipId = $result[0];
          
$ticket = mycurl('https://sf.zxyang.cn/mg/mggetck.php');
//$ticket="2CF59CB99A4C351EBFF3A1F5671898C7";//请到app抓ticket

	

  $vvv = xin_mian($ticket,$videoId);
  
     
$player['code']=200;
$player['msg']='解析成功';
$player['url']=$vvv."&api.zxyang.cn";
if(strpos($player['url'],'not found') !== false ){
    $player['code']=400;
$player['msg']='解析失败';
}


echo json_encode(($player), JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	

	
	function xin_mian($ticket,$videoId){
	
    $api="https://mobile.api.mgtv.com/v8/video/getSource?appVersion=7.2.3&osType=ios&ticket=".$ticket."&videoId=".$videoId;
    

	$content = html($api);
	$json = json_decode($content,true);
	
if (empty($zurl)){$zurl = $json['data']['videoSources'][1]['url'];}
if (empty($zurl)){$zurl = $json['data']['videoSources'][2]['url'];}
if (empty($zurl)){$zurl = $json['data']['videoSources'][3]['url'];}
if (empty($zurl)){$zurl = $json['data']['videoSources'][4]['url'];}

	//    $zurl=$json['data']['videoSources'][1]['url'];
		$vipapi = "https://disp.titan.mgtv.com".$zurl;
	
	$vip = html($vipapi);
		$vipurl = json_decode($vip,true);
	$player=$vipurl['info'];
	$player = str_replace('http','https',$player);

       
     return $player;
     }
    
    
   function html($url){
    $curl = curl_init();
	$header = array( 
		"X-FORWARDED-FOR:"."82.157.73.165",//请填自己抓包时候的ip
		"CLIENT-IP:"."127.0.0.1",
		"X-Real-IP:"."127.0.0.1",
        "referer: https://www.mgtv.com",
        "Connection: Keep-Alive",
        "user-agent: Dalvik/2.1.0 (Linux; U; Android 10; MIX 2S MIUI/V12.5.1.0.QDGCNXM) imgotv-aphone-7.2.3",
		//"Content-Length: 326",
		"Accept: application/json, text/javascript, */*; q=0.01",
		"Accept-Language: zh-CN,zh;q=0.9",
		//"cookie:".$ticket,
     );
    curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($curl, CURLOPT_HEADER,0);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	$content = curl_exec($curl);
	curl_close($curl);
    return $content;
}
	function mycurl($url, $header = [], $type = 0, $post_data = '', $redirect = true) {
        // 初始化cURL
        $curl = curl_init();
        // 设置网址
        curl_setopt($curl, CURLOPT_URL, $url);
        
        // 设置请求头
        if (empty($header) == false) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        // 设置POST数据
        if ($type == 1) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        }
        // 设置重定向
        if ($redirect == false) {
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        }
        //允许执行的最长秒数 超时时间
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        // 过SSL验证证书
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        // 将头部作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, false);
        // 设置以变量形式存储返回数据
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // 请求并存储数据
        $return = curl_exec($curl);
        // 关闭cURL
        curl_close($curl);
        // 返回数据
        return $return;
    }


 
