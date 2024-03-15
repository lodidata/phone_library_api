<?php


namespace app\common\service;

use app\common\service\ApiService;
use think\Cache;
use app\common\model\Api as ApiModel;


class ApiCountService extends BaseService
{

    protected $cache;

    public function __construct()
    {
        $this->cache = cache()->store('redis');
    }

    /**
     * @param $member_id
     * @param $api_id
     * @param $num
     * @return bool
     * TODO 按天检测api 调用频率
     */
    public function CheckApiRate($member_id, $api_id, $num)
    {
        $redisKey = RedisKey::memberApiTotal($member_id);

        $use_num = $this->cache->hget($redisKey, $api_id);

        $apiService = new ApiModel();
        $list = $apiService->getInfoByIdFind(['id' => $api_id], 'rate_num');
        if (empty($list) || $use_num >= $list['rate_num']) {
            exit(ajaxReturn('调用频率过高', [], 40009));
        }
        $this->cache->Hincrby($redisKey, $api_id, $num);
        if ($this->cache->ttl($redisKey) < 0) {
            $this->cache->expire($redisKey, 86400);
        }
        return true;
    }

    /**
     * @param $member_id
     * @param $api_id
     * @param $num
     * @return bool
     * TODO 检测接口免费调用次数
     */
    public function CheckFreeApi($member_id, $api_id, $num)
    {
        $redisKey = RedisKey::memberFreeApiTotal($member_id);
        $use_num = $this->cache->hGet($redisKey, $api_id);
        $apiService = new ApiModel();
        $list = $apiService->getInfoByIdFind(['id' => $api_id], 'free_num');
        if (empty($list) || $use_num >= $list['free_num']) {
            exit(ajaxReturn('免费次数已使用完', [], 40009));
        }
        $this->cache->Hincrby($redisKey, $api_id, $num);
        return true;
    }


}