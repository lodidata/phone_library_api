<?php
// +----------------------------------------------------------------------
// | AES加密验证 key 通过服务器发给客户端
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
namespace app\common\service;

use app\common\model\redis\TokenRedis;

class DesEncryptService
{
    // 秘钥
    static $key      = 'abfc84f9e3b419t149049de208c3b90c';           // 加密使用
    static $sign_key = '957g4cf535y6b4t18d21612487bf63vd';           // 签名使用

    public function __construct($key, $sign_key)
    {
        self::$key      = $key;
        self::$sign_key = $sign_key;
    }

    /**
     * @AES加密
     * @param string $string 需要加密的字符串
     * @return string
     */
    public static function encrypt($string)
    {
        $string = self::pkcsPadding($string, 8);
        $key    = str_pad(self::$key, 8, '0');
        $sign   = openssl_encrypt($string, 'DES-ECB', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
        $sign   = base64_encode($sign);
        $sign   = str_replace('/','_',$sign);
        return $sign;
    }

    /**
     * @AES解密
     * @param string $string 需要解密的字符串
     * @return string
     */
    public static function decrypt($string)
    {
        $string    = str_replace(' ', '+', $string);
        $string    = str_replace('_', '/', $string);
        $encrypted = base64_decode($string);
        $key       = str_pad(self::$key, 8, '0');
        $sign      = @openssl_decrypt($encrypted, 'DES-ECB', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
        $sign      = self::unPkcsPadding($sign);
        $sign      = rtrim($sign);
        return $sign;
    }

    /**
     * 填充
     * @param $str
     * @param $blockSize
     * @return string
     */
    private static function pkcsPadding($str, $blockSize)
    {
        $pad = $blockSize - (strlen($str) % $blockSize);
        return $str . str_repeat(chr($pad), $pad);
    }


    /**
     * 去填充
     * @param $str
     * @return string
     */
    private static function unPkcsPadding($str)
    {
        $pad = ord($str[strlen($str) - 1]);
        if ($pad > strlen($str)) {
            return false;
        }
        return substr($str, 0, -1 * $pad);
    }


    /**
     * @签名
     * @param array $array 需要签名的数组
     * @param int $type 返回类型 1=字符串 2=Array
     * @return string|array
     */
    public static function sign($array, $type = 1)
    {
        $sign = '';
        foreach ($array as $key => $val) {
            if ($key != 'sign_key') {
                $sign .= $key . '=' . $val . '&';
            }
        }
        $sign              .= 'sign_key=' . self::$sign_key;
        $sign              = md5($sign);
        $array['sign_key'] = strtoupper($sign);
        if ($type == 1) {
            return strtoupper($sign);
        } else if ($type == 2) {
            return $array;
        } else {
            return strtoupper($sign);
        }
    }

    /**
     * @签名验证
     * @param array $array 需要验证签名的数组
     * @return bool
     */
    public static function signCheck($array)
    {
        $sign = '';
        foreach ($array as $key => $val) {
            if ($key != 'sign_key') {
                $sign .= $key . '=' . $val . '&';
            }
        }

        $sign .= 'sign_key=' . self::$sign_key;
        if ($array['sign_key'] == strtoupper(md5($sign))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取token
     * @param $params
     * @return string
     */
    public static function getToken($params){
        $jsondata         = $params;
        $jsondata['time'] = time();
        //获取签名后的数组
        $signArray = self::sign($jsondata, 2);
        $string    = json_encode($signArray);
        $token     = self::encrypt($string);
        if($token){
            TokenRedis::setToken($params, $token);
        }
        return $token;
    }

    /**
     * 验证token
     * @param $token
     */
    public static function verifyToken($token){
        if(!$token){
            throw new \Exception('token不能为空', 401);
        }
        $data      = self::decrypt($token);
        $dataArray = json_decode($data, true);
        $res       = TokenRedis::tokenExist($dataArray, $token);
        if(!$res){
            throw new \Exception('token已失效', 401);
        }
        if (!self::signCheck(json_decode($data, true))) {
            throw new \Exception('签名验证失败', 401);
        }
        return $dataArray;
    }

    // 测试方法
    public static function test($params)
    {
//        //需要提交的参数
//        $jsondata = array(
//            'action'    => 'demoLogin',
//            'appSecret' => md5('123456'),
//            'uid'       => '101',
//            'gameCode'  => 'FuLinMen',
//        );

        $jsondata =$params;

        //获取签名
        $sign = self::sign($jsondata, 1);
        echo '签名:' . $sign;
        echo '<br>';
        //获取签名后的数组
        $signArray = self::sign($jsondata, 2);
        $string    = json_encode($signArray);
        echo '签名结果:' . $string;
        echo '<br>';
        $a = self::encrypt($string);
        echo '加密结果:' . $a;
        echo '<br>';
        $b = self::decrypt($a);
        echo '解密结果:' . $b;
        echo '<br>';
        if (self::signCheck(json_decode($b, true))) {
            echo '签名验证成功';
            echo '<br>';
        } else {
            echo '签名验证失败';
            echo '<br>';
        }
    }
}

