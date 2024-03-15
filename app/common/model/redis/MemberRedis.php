<?php


namespace app\common\model\redis;


class MemberRedis
{
    protected $cache;

    public function __construct()
    {
        $this->cache = cache()->store('redis');
    }

}