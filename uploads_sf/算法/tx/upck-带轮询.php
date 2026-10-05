<?php
header("Content-type:text/html;charset=utf-8");
define('CKDIR','ck.txt');// 保存cookie的文件名
define('CKDIR2','ck2.txt');// 保存cookie的文件名
define('LX','lx.txt');// 保存cookie的文件名
define('ZHTYPE','qq'); //qq/wx
$g_tk = '1992354098';
$g_vstk = '101138289';
$g_actk = '798299078';

//     $data = pd($url,$pz,$api);
	
// 	$data -> code = 200;
//     $data -> ck = $cookie;
// 	echo(json_encode($data, 456));
	$api = new UPCK();
$cookie1 = @file_get_contents(CKDIR);
$cookie2 = @file_get_contents(CKDIR2);
$lxcd = @file_get_contents(LX);
if($lxcd == 1){
    $cookie = $cookie1;
    @file_put_contents(LX,2);
}else{
    $cookie = $cookie2;
     @file_put_contents(LX,1);
}
echo json_encode($cookie);
exit;
if( strpos(strval($cookie), '失效') !== false){
   $data = 'Cookie失效!';
}else{
    $cishu = file_get_contents("txtj.txt");
      $chi = $cishu+1;
      $shu = file_put_contents("txtj.txt",$chi);
$data = $api->getck($cookie);
}
echo($data);
class UPCK
{
public static function getck($cookie) {
        if (empty($cookie)==true) {
            return;
        }
        $time = self::getMillisecond();
        preg_match('/lskey=(.*?);/',$cookie,$lskeyA);
        $lskey = $lskeyA[1];
        if (ZHTYPE == 'wx') {
            $lt = 'wx';
            preg_match('/vusession=(.*?);/',$cookie,$vusessionA);
            $vusession = $vusessionA[1];
            preg_match('/access_token=(.*?);/',$cookie,$access_tokenA);
            $access_token = $access_tokenA[1];
        } else {
            $lt = 'qq';
            preg_match('/vqq_vusession=(.*?);/',$cookie,$vusessionA);
            $vusession = $vusessionA[1];
            preg_match('/vqq_access_token=(.*?);/',$cookie,$access_tokenA);
            $access_token = $access_tokenA[1];
        }
        if (empty($lskey)==false) {
            $g_tk = self::time33($lskey);
        } else {
            $g_tk = 1992354098;
        }
        if (empty($vusession)==false) {
            $g_vstk = self::time33($vusession);
        } else {
            //修改
          //  $g_vstk = 827353871;
          $g_vstk =101138289;
        }
        if (empty($access_token)==false) {
            $g_actk = self::time33($access_token);
        } else {
            //修改位置
            //$g_actk = 282290544;
            $g_actk =798299078;
        }
        $api = "https://access.video.qq.com/user/auth_refresh?vappid=11059694&vsecret=fdf61a6be0aad57132bc5cdf78ac30145b6cd2c1470b0cfe&type=$lt&g_tk=$g_tk&g_vstk=$g_vstk&g_actk=$g_actk&_=$time";
        $header = [
            'Referer: https://v.qq.com/',
            'Cookie: '.$cookie,
        ];
        $res = self::mycurl($api, $header);
        $json = json_decode(str_replace('data=','',$res),true);
        //通知
        $errcode = $json['errcode'];
         if($errcode !== 0){
              @file_put_contents(CKDIR,"Cookie失效！");
           self::mycurl("https://sf.huomiao.cc/note/cookieNote.php/?type=qq");
          return '';
         }
        if (ZHTYPE == 'wx') {
            $pArr = ['/vuserid=.*?;/','/vusession=.*?;/','/access_token=.*?;/','/next_refresh_time=.*?;/'];
        } else {
            $pArr = ['/vqq_vuserid=.*?;/','/vqq_vusession=.*?;/','/vqq_access_token=.*?;/','/vqq_next_refresh_time=.*?;/'];
        }
        //修改位置
        $newckArr = ['vqq_vuserid=' . $json['vuserid'] . ';','vqq_vusession=' . $json['vusession'] .';','vqq_access_token=' . $json['access_token'] . ';','vqq_next_refresh_time=' . $json['next_refresh_time'] . ';'];
        $cookieNew = preg_replace($pArr,$newckArr,$cookie);
        @file_put_contents(CKDIR,$cookie);
        // if (CKTIME == 0) {
        //     if (empty($json['next_refresh_time'])==false) @file_put_contents('qqtime.txt',time()+(int)$json['next_refresh_time']);
        // }
        return $cookieNew;
       //exit(json_encode($json,456));
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