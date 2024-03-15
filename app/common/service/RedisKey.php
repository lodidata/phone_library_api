<?php


namespace app\common\service;


class RedisKey
{

    /**
     * @param $member_id
     * @return string
     * TODO 按天统计会员调用接口次数
     */
    public static function memberApiTotal($member_id)
    {
        return __FUNCTION__ . sprintf(':%d:%d', $member_id, date("Ymd"));
    }

    /**
     * @param $member_id
     * @return string
     * TODO 统计用户免费调用API的次数
     */
    public static function memberFreeApiTotal($member_id)
    {
        return __FUNCTION__ . sprintf(':%d', $member_id);
    }
}