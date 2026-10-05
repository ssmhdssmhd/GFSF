<?php
header('Content-type: text/json;charset=utf-8');
define('CKDIR','ck.txt');// 保存cookie的文件名

	$api = new UPCK();

$data = $api->getck();


if($data['code']==200){
    //只取出ticket
  $ck =  $data['ck'];
  $ticket= preg_match('/ticket:.*?;/',$ck, $mc);
  $tk = str_replace('ticket:','',$mc[0]);
  $tk = str_replace(';','',$tk);
 echo $tk;
}else{
echo file_get_contents(CKDIR);
}


class UPCK
{
public static function getck() {
     
  
        $api = "https://sf.zxyang.cn/mg/mgcheckck.php";
        $res = self::mycurl($api);
    
        return json_decode($res,456);
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