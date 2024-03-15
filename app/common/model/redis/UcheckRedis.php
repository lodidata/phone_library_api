<?php


namespace app\common\model\redis;
use \think\facade\Cache;

class UcheckRedis
{
    public static function setAllTables($data){
        $redis       = Cache::store('redis');
        $key         = "api:alltables";
        $expire_time = 3600;

        return $redis->set($key, $data, $expire_time);
    }

    public static function getAllTables(){
        $redis       = Cache::store('redis');
        $key         = "api:alltables";
        $data      = $redis->get($key);
        return $data;
    }

    public static function setUcheck(){
        $redis       = Cache::store('redis');
        $key         = "api:ucheck";
        $expire_time = 3600;

        return $redis->set($key, 1, $expire_time);
    }

    public static function getUcheck(){
        $redis     = Cache::store('redis');
        $key       = "api:ucheck";
        $data      = $redis->get($key);
        return $data;
    }

    public static function delUcheck(){
        $redis     = Cache::store('redis');
        $key       = "api:ucheck";
        $data      = $redis->delete($key);
        return $data;
    }

}