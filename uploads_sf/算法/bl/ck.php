<?php

// 引入Curl请求类

require_once './curl.php';

	$api = new PasswordLogin();

// $url=$_GET['url'];


//$data = $api->getCaptacha();
$data = $api->getHash();

echo json_encode($data, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


class PasswordLogin

{

    // 登录密钥

    public $key;



    // 极验ID

    public $gt ;



    // 极验Key

    public $challenge ;



    /**

     * @description: 构造方法，请求API获取人机验证码参数

     */

    public function __construct()

    {

        $res = new Curl('https://passport.bilibili.com/web/captcha/combine?plat=6');

        $array = json_decode($res->get(), true);

        $this->key = $array['data']['result']['key'];

        $this->gt = $array['data']['result']['gt'];

        $this->challenge = $array['data']['result']['challenge'];

    }



    /**

     * @description: 获取人机验证参数

     * @return array 极验ID以及极验Key

     */

    public function getCaptacha()

    {

        return [

            'gt' => $this->gt,

            'challenge' => $this->challenge,

        ];

    }



    /**

     * @description: 获取登录盐值和RSA公钥

     * @return array 登录验证以及RSA公钥

     */

    private function getHash()

    {

        $res = new Curl('https://passport.bilibili.com/login?act=getkey');

        $array = json_decode($res->get(), true);

        return $array;

    }



    /**

     * @description: 验证登录

     * @param string $username 用户名（手机号或者邮箱）

     * @param string $password 密码

     * @param string $challenge 极验Key

     * @param string $validate 验证结果1

     * @param string $seccode 验证结果2

     * @return array

     */

    public function verify($username , $password, $challenge, $validate, $seccode)

    {

        $hash = ($this->getHash())['hash'];

        $public_key = ($this->getHash())['key'];

        $hash_pwd = $hash . $password;

        extension_loaded('openssl') or die('php需要openssl扩展支持');

        openssl_public_encrypt($hash_pwd, $result_pwd, $public_key);

        $result_pwd = base64_encode($result_pwd);

        $res = new Curl('https://passport.bilibili.com/web/login/v2');

        $post = 'captchaType=6&username=' . $username . '&password=' . $result_pwd . '&keep=true&key=' . $this->key . '&challenge=' . $challenge . '&validate=' . $validate . '&seccode=' . $seccode;

        $array = json_decode($res->post($post), true);

        if ($array['code'] == 0) {

            preg_match_all("/DedeUserID=(.*)&/U", $array['data']['redirectUrl'], $uid);

            preg_match_all("/DedeUserID__ckMd5=(.*)&/U", $array['data']['redirectUrl'], $uid_md5);

            preg_match_all("/Expires=(.*)&/U", $array['data']['redirectUrl'], $expires);

            preg_match_all("/SESSDATA=(.*)&/U", $array['data']['redirectUrl'], $SESSDATA);

            preg_match_all("/bili_jct=(.*)&/U", $array['data']['redirectUrl'], $bili_jct);

            return [

                'code' => 0,

                'data' => [

                    'uid' => $uid[1][0],

                    'uid_md5' => $uid_md5[1][0],

                    'expires' => $expires[1][0],

                    'SESSDATA' => $SESSDATA[1][0],

                    'bili_jct' => $bili_jct[1][0]

                ]

            ];

        } else {

            switch ($array['code']) {

                case '-400':

                    $msg = '请求错误';

                    break;

                case '-629':

                    $msg = '账号或密码错误';

                    break;

                case '-653':

                    $msg = '用户名或密码不能为空';

                    break;

                case '-662':

                    $msg = '提交超时，请重新提交';

                    break;

                case '-2001':

                    $msg = '缺少必要的参数';

                    break;

                case '-2100':

                    $msg = '需验证手机号和邮箱';

                    break;

                case '2400':

                    $msg = '登录密钥错误';

                    break;

                case '2406':

                    $msg = '验证极验服务错误';

                    break;

            }

            return [

                'code' => $array['code'],

                'msg' => $msg

            ];

        }

    }

}

?>



