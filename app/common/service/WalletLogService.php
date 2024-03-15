<?php

namespace app\common\service;

use app\common\model\WalletLog as WalletLogModel;


class WalletLogService extends BaseService
{

    protected  $pageSize = 10;

    public function __construct()
    {
        //数据操作类
        $this->datamodel = new WalletLogModel();
        //主键
        $this->pk = $this->datamodel->getPk();
    }


    /**
     * 交易流水统计
     * @param $memberId
     * @param $start
     * @param $end
     * @return mixed
     */
    public function  countList($memberId, $start, $end)
    {
        $where = [];
        //所有用户数据
        if($memberId){
            $where[] = ['member_id','=',$memberId];
        }
        if(!empty($start) || !empty($end)){
            $start && $start = strtotime(getStartDate($start));
            $end   && $end   = strtotime(getEndDate($end));
            $where[]         = ['created_at','between',[$start,$end]];
        }

        $field  = "from_unixtime(created_at,'%Y-%m-%d') as created_day,";
        $field .= "sum( case when charge_type in (1,4,5) then action_money else 0 end) as add_sums,";
        $field .= "sum( case when charge_type in (2,3) then action_money else 0 end) as sub_sums,";
        $field .= "SUBSTRING_INDEX(GROUP_CONCAT(after_money ORDER BY id DESC),',',1) as last_money";
        $list = $this->datamodel->field($field)
                                ->where($where)
                                ->group("from_unixtime(created_at,'%Y-%m-%d')")
                                ->order('created_day desc')
                                ->paginate(request()->param('size'),false,['query'=>request()->param()])->toArray();

        return $list;
    }
}