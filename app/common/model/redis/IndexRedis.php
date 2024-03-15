<?php


namespace app\common\model\redis;
use \think\facade\Cache;

class IndexRedis
{
    public static function setIndexCount($params){
        $redis       = Cache::store('redis');
        $key         = "api:index:count:{$params['id']}";
        $expire_time = 600;

        $redis->set($key, json_encode($params['data']), $expire_time);
    }

    public static function getIndexCount($id){
        $redis     = Cache::store('redis');
        $key       = "api:index:count:{$id}";
        $data      = $redis->get($key);
        if($data){
            return json_decode($data, true);
        }
        return [];
    }
}