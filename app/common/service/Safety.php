<?php


namespace app\common\service;


class Safety
{

    /*
     * 生成token
     */
    public static function generateToken($mobile)
    {
        $randChar = self::getRandChar(32);
        $timestamp = $_SERVER['REQUEST_TIME'];
        $token = md5($randChar . $timestamp . $mobile);//加上账号
        return $token;
    }
    //生成随机字符串
    public static function getRandChar($length)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for ($i = 0;
             $i < $length;
             $i++) {
            $str .= $strPol[rand(0, $max)];
        }

        return $str;
    }
    //制定token密码
    public static function mPassword($data){
        return md5(md5($data).config('app.salf'));
    }
}