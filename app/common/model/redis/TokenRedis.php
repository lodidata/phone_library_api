<?php

namespace app\common\model\redis;

use \think\facade\Cache;
class TokenRedis
{
    /**
     * 设置token缓存
     * @param $params
     * @param $token
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function setToken($params, $token){
        $redis       = Cache::store('redis');
        $key         = "api:token:{$params['type']}:{$params['id']}";
        $expire_time = $params['expire'];
        //token过期时间就通过redis有效期来控制
        $redis->set($key, $token, $expire_time);
    }

    /**
     * 验证token是否存在
     * @param $params
     * @param $token
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function tokenExist($params,$token){
        $redis     = Cache::store('redis');
        $key       = "api:token:{$params['type']}:{$params['id']}";
        $old_token = $redis->get($key);
        if($old_token){
            return $old_token == $token;
        }
        return false;
    }

    /**
     * 删除token
     * @param $params
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function delToken($id){
        $redis       = Cache::store('redis');
        $key         = "api:token:member:{$id}";
        //删除token
        $redis->delete($key);
    }

}