<?php


namespace app\common\service;


use app\common\model\Api;
use app\common\model\ApiLog;
use app\common\model\Product as ProductModel;
use app\Request;

class ApiLogService extends BaseService
{
    public function __construct()
    {
        //数据操作类
        $this->datamodel = new ApiLog();
        //主键
        $this->pk = $this->datamodel->getPk();
    }

    /**
     * @param $page
     * @return array
     * @throws \think\db\exception\DbException
     * TODO 获取列表
     */
    public function getLogList($member_id)
    {
        $request = request();
        $type = $request->param('type', 1);
        $start_time = $request->param('start_time');
        $end_time = $request->param('end_time');
        $group_field = $type == 1 ? 'day' : 'month';
        $query = $this->datamodel->field('api_id, sum(success_num) success_num, sum(fail_num) fail_num, member_id, sum(money) money, ' . "$group_field date")
            ->group("$group_field, api_id")
            ->where(['member_id' => $member_id]);
        if (!empty($start_time)) {
            $query->where('day', '>=', $start_time);
        }
        if (!empty($end_time)) {
            $query->where('day', '<=', $end_time);
        }
        $list = $query->paginate()->toArray();
       // echo $query->getLastSql();exit;
        if (!empty($list['data'])) {
            $api_ids = array_column($list['data'], 'api_id');
            $productNames = (new  Api())->getProductNames($api_ids);
            foreach ($list['data'] as &$val) {
                $val['total_num'] = $val['fail_num'] + $val['success_num'];
                $val['product_name'] = $productNames[$val['api_id']] ?? '';
            }
        }
        return $list;
    }

    /*
     * 日消耗统计（财务）
     */
    function getApiLogDay($memberId, $start, $end)
    {
        $produnct_model = new ProductModel();
        $where[] = [
            ['member_id','=',$memberId],
        ];
        if(!empty($start) || !empty($end)){
            $start && $start = getStartDate($start);
            $end   && $end   = getEndDate($end);
            $where[]         = ['created_at','between',[$start,$end]];
        }
        $field = 'left(created_at,10) date,group_concat(DISTINCT(`api_id`)) api_id,sum(success_num) success_num, sum(fail_num+success_num) num ,sum(money) money';
        $info  = $this->datamodel->getListDay($where, $field)->toArray();

        if(empty($info['data'])){
            return $info;
        }

        foreach ($info['data'] as &$v){
            $v['name']  = $produnct_model->getNamesByApiIds($v['api_id']);
            $v['money'] = floatNumber($v['money']);
            unset($v['api_id']);
        }
        unset($v);

        return $info;
    }

    /*
     * 月消耗统计（财务）
     */
    function getApiLogMonth($memberId, $start, $end)
    {
        $produnct_model = new ProductModel();
        $where[] = [
            ['member_id','=',$memberId],
        ];
        if(!empty($start) || !empty($end)){
            $start && $start = getStartDate($start);
            $end   && $end   = getEndDate($end, 1);
            $where[]         = ['created_at','between',[$start,$end]];
        }
        $field = 'left(created_at,7) date,group_concat(DISTINCT(`api_id`)) api_id,sum(success_num) success_num, sum(fail_num+success_num) num ,sum(money) money';
        $info  = $this->datamodel->getListMonths($where, $field)->toArray();

        if(empty($info['data'])){
            return $info;
        }

        foreach ($info['data'] as &$v){
            $v['name']  = $produnct_model->getNamesByApiIds($v['api_id']);
            $v['money'] = floatNumber($v['money']);
            unset($v['api_id']);
        }
        unset($v);

        return $info;
    }

    function getCountData($params){
        $Api     = new Api();
        $api_ids = $Api->getApiId($params['id']);
        $where   = [
                ['created_at', 'between', [$params['start'], $params['end']]],
                ['member_id',  '=',       $params['member_id']],
                ['api_id',     'in',      $api_ids],
        ];
        $column = 'sum(success_num + fail_num) num, sum(success_num) success_num, sum(money) money';
        return $this->datamodel->getCountData($where, $column);
    }

}