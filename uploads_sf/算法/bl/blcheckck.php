<?php
header('Content-Type:application/json; charset=utf-8');
define('CKDIR','ck.txt');// 保存cookie的文件名

	$api = new UPCK();

$cookie = file_get_contents(CKDIR);
$data = $api->blgetinfo($cookie);
//echo json_encode($data['code'],456);

if( strpos(strval($cookie), 'Cookieout') !== false){
    $datare['code'] =400;
  $datare['msg'] = 'Cookie失效!';
 //  echo json_encode($data,456);
}else{

    if($data['code']!==0){
   $api ->mycurl('https://sf.zxyang.cn/note/cookieNote.php/?type=bl&statue=true&url=cksx');
    file_put_contents(CKDIR,$cookie."cookie-Statue:Cookieout;");
        $datare['code'] =401;
  $datare['msg'] = 'Cookie失效!';
 //  echo json_encode($data,456);
    }else{
        $datare['code'] =200;
  $datare['msg'] = 'Cookie正常!';

    }

}
 $datare['ck'] = $cookie;
 $datare['bl-info']=$data;
 echo json_encode($datare,456);

class UPCK
{
public static function blgetinfo($cookie) {
        if (empty($cookie)==true) {
            return 'Cookie为空';
        }
  
        $api = "https://api.bilibili.com/x/web-interface/nav";
        $header = [
          //  'Referer: https://v.qq.com/',
          'Accept: */*',
          'User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.146 Safari/537.36',
          'Referer: https://www.bilibili.com/',
          'Origin: https://www.bilibili.com/',
            'Cookie:'.$cookie,
        ];
        $res = self::mycurl($api, $header);
       // $json = json_decode(str_replace('data=','',$res),true);
     
        //@file_put_contents(CKDIR,$cookie);
        // if (CKTIME == 0) {
        //     if (empty($json['next_refresh_time'])==false) @file_put_contents('qqtime.txt',time()+(int)$json['next_refresh_time']);
        // }
        return json_decode($res,456);
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