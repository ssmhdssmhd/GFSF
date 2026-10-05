<?php
header("Content-type:text/html;charset=utf-8");
define('CKDIR','ck.txt');// 保存cookie的文件名
define('interval',2);// 每隔5s运行

$api = new UPCK();

$ret = $api->getandnote();
$data=[];
if($ret == ''){
    $data['code'] = 400;
}else{
 $data['code'] = 200;
}
 $data['ck'] = $ret;
echo(json_encode($data,456));
// }
//echo $data;
//echo json_encode($data, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
class UPCK
{
    
    public static function getandnote(){
       $ermjson =  self::getewm();
       $erwmurl = $ermjson['url'];
       $qrcode_key = $ermjson['qrcode_key'];
        $enurl = urlencode($erwmurl);
        self::mycurl("https://sf.zxyang.cn/note/cookieNote.php/?type=bl&url=".$enurl);
        if ($qrcode_key == '') {
            return "获取转跳链接失败！";
        }
        ignore_user_abort(true);
       for($i=0;$i<=30;$i++){
        $cookies =  self::getCookie($qrcode_key);
        if(empty($cookies) == false){
         file_put_contents(CKDIR,$cookies);
         self::mycurl("https://sf.zxyang.cn/note/cookieNote.php/?type=bl&statue=true");
              return $cookies;
        }else{
            sleep(2);// 等待5s
        }
        
        }
         self::mycurl("https://sf.zxyang.cn/note/cookieNote.php/?type=bl");
        return "登陆超时";
    }
    
public static function getewm(){
    $api= 'https://passport.bilibili.com/x/passport-login/web/qrcode/generate';
    $res = self::mycurl($api);
    $json = json_decode($res,true);
    // $erwurl = $json['url'];
    // $qrcode_key = $json['qrcode_key'];
    return $json['data'];
} 
public static function getCookie($qrcode_key){
     $api= 'https://passport.bilibili.com/x/passport-login/web/qrcode/poll?qrcode_key='.$qrcode_key;
    // $res = self::mycurl($api);
    // $json = json_decode(str_replace('data=','',$res),true);    
    // $responseInfo = $http_response_header;

    $cookies =self::get_cookie($api,'set-cookie','https://bilibili.com/');
    return $cookies;
    // $json['code'];    
}
public static function get_cookie($url_){
$header = get_headers($url_);
$cookies;

 for($i=0;$i<sizeof($header);$i++){
     
        if (strstr($header[$i],'Set-Cookie')) {
            $claerstr = str_replace("Set-Cookie:",'',$header[$i]);   
           // str_replace('','X-Bili-Trace-Id',$header[$i]);
            $cookies = $cookies.$claerstr.';';
        }

    }
        return     $cookies;//返回cookie
}



    public static function get_head($sUrl){

$oCurl = curl_init();

// 设置请求头, 有时候需要,有时候不用,看请求网址是否有对应的要求

$header[] = "Content-type: application/x-www-form-urlencoded";

$user_agent = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.146 Safari/537.36";



curl_setopt($oCurl, CURLOPT_URL, $sUrl);

curl_setopt($oCurl, CURLOPT_HTTPHEADER,$header);

// 返回 response_header, 该选项非常重要,如果不为 true, 只会获得响应的正文

curl_setopt($oCurl, CURLOPT_HEADER, true);

// 是否不需要响应的正文,为了节省带宽及时间,在只需要响应头的情况下可以不要正文

curl_setopt($oCurl, CURLOPT_NOBODY, true);

// 使用上面定义的 ua

curl_setopt($oCurl, CURLOPT_USERAGENT,$user_agent);

curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );

// 不用 POST 方式请求, 意思就是通过 GET 请求

curl_setopt($oCurl, CURLOPT_POST, false);


$sContent = curl_exec($oCurl);

// 获得响应结果里的：头大小

$headerSize = curl_getinfo($oCurl, CURLINFO_HEADER_SIZE);

// 根据头大小去获取头信息内容

$header = substr($sContent, 0, $headerSize);



curl_close($oCurl);



return $header;

}

	 public static function getMillisecond() {
		list($t1, $t2) = explode(' ', microtime());
		return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
	}
	public static function mycurl($url, $header = [], $type = 0, $post_data = '', $redirect = true) {
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
       public static function time33($str) {
        if (empty($str)==false) {
            $n = strlen($str);
            $i = 5381;
            for ($t = 0; $t < $n; ++$t) {
                $v = ($i & 0xFFFFFFFF) << (5 & 0x1F);
                $tt = $v & 0x80000000 ? $v | 0xFFFFFFFF00000000 : $v & 0xFFFFFFFF;
                $i = $i + $tt + ord($str[$t]);
            }
            return 2147483647 & $i;
        } else {
            return;
        }
    }
}
?>