<?php


namespace app\common\service;

use app\common\model\ApiLog;
use think\facade\Cache;
use app\common\model\redis\IndexRedis;


class IndexService extends BaseService
{

    /**
     * @param $id
     * @return array
     * TODO 获取指定用户的接口调用记录
     */
    function getUsage($id)
    {
        $info = IndexRedis::getIndexCount($id);
        if(!$info){
            $time       = time();
            $day        = date('Y-m-d', $time);
            $lastDay    = date("Y-m-d", strtotime("-1 day", $time));
            $month      = date('Y-m', $time);
            $lastMonth  = date('Y-m', strtotime(date('Y-m-01') . " - 1 month", $time));
            $ApiLog     = new ApiLog();
            $data = [
                'dayData'       => [['created_at','between', [getStartDate($day), getEndDate($day)]]],
                'lastDayData'   => [['created_at','between', [getStartDate($lastDay), getEndDate($lastDay)]]],
                'monthData'     => [['created_at','between', [getStartDate($month), getEndDate($month, 1)]]],
                'lastMonthData' => [['created_at','between', [getStartDate($lastMonth), getEndDate($lastMonth, 1)]]]
            ];
            $percent = [
                ['dayData', 'total', 'lastDayData'],
                ['dayData', 'success_num', 'lastDayData'],
                ['monthData', 'total', 'lastMonthData'],
                ['monthData', 'success_num', 'lastMonthData'],
            ];
            $ret = [];
            foreach ($data as $key => $val) {
                $tmp          = $ApiLog->getCountData(array_merge($val, [['member_id','=', $id]]))->toArray();
                $tmp['total'] = $tmp['success_num'] + $tmp['fail_num'];
                $ret[$key]    = $tmp;
            }
            $info             = $this->_computeData($percent, $ret);
            //保存缓存
            if($info){
                IndexRedis::setIndexCount(['id' => $id, 'data' => $info]);
            }

        }
        return $info;
    }

    /**
     * @param $member_id
     * @param $api_id
     * TODO  获取指定用户指定接口的调用记录
     */
    public function getApiUsage($member_id=0, $api_id=0)
    {
        //数据时时更新,每10分钟更新一次数据
        $cacheKey = 'index_get_api_usage_'.$member_id.'_'.$api_id;
        if(!$info = Cache::get($cacheKey)){
            $day     = date('Y-m-d', time());
            $lastDay = date("Y-m-d", strtotime("-1 day"));
            $data    = [
                'dayData'     => [['created_at','between', [getStartDate($day),getEndDate($day)]]],
                'lastDayData' => [['created_at','between', [getStartDate($lastDay),getEndDate($lastDay)]]],
            ];
            $ApiLog = new ApiLog();
            $ret    = [];
            $where  = [];
            if($member_id && $api_id){
                $where = [
                    ['member_id', '=', $member_id],
                    ['api_id', '=', $api_id]
                ];
            }
            foreach ($data as $key => $val) {
                $tmp          = $ApiLog->getCountData(array_merge($val, $where), 'sum(success_num) success_num, sum(fail_num) fail_num,sum(money) money')->toArray();
                $tmp['total'] = $tmp['success_num'] + $tmp['fail_num'];
                $ret[$key]    = $tmp;
            }
            $percent = [
                ['dayData', 'total', 'lastDayData'],
                ['dayData', 'success_num', 'lastDayData'],
                ['dayData', 'money', 'lastDayData']
            ];
            $info = $this->_computeData($percent, $ret);
            Cache::set($cacheKey, $info, 600);
        }
        return $info;
    }

    /**
     *  计算环比
     */
    public function _getPercent($num, $num1)
    {
        if ($num > 0 && $num1 <= 0) {
            return 100;
        }
        if ($num == 0 && $num1 == 0) {
            return 0;
        }
        return floatNumber(($num - $num1) / $num1) * 100;
    }

    /**
     * 计算统计数据
     */
    protected function _computeData($percent, $ret)
    {
        $info = [];
        foreach ($percent as $item => $val) {
            $tmpData  = $ret[$val[0]];
            $tmpData1 = $ret[$val[2]];
            $percent1 = $this->_getPercent($tmpData[$val[1]], $tmpData1[$val[1]]);
            $type     = $percent1 > 0 ? 1 : 2;
            $info[$val[0]][$val[1]] = ['num' => floatNumber($tmpData[$val[1]]), 'percent' => $percent1 . '%', 'type' => $type];
        }
        return $info;

    }
}