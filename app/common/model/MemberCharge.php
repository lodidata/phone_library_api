<?php


namespace app\common\model;


class MemberCharge extends Base
{
    /**
     * @return array|void
     * TODO 获取充值记录
     */
    public function getLog($member_id)
    {
        return $this->field('member_id, amount, after_amount, before_amount, status, created_at')->where(['member_id' => $member_id])->paginate()->toArray();
    }

    public function saveData($params){
        $data = [
            'member_id'      => $params['member_id'],
            'amount'         => $params['amount'],
            'bank_id'        => $params['bank_id'],
            'order_no'       => $params['order_no'],
            'charge_account' => $params['charge_account'],
            'images'         => $params['images'],
            'status'         => $params['status'],
            'type'           => $params['type'],
            'created_at'     => $params['created_at'],
            'remark'         => $params['remark'],
        ];

        return $this->save($data);
    }

    public function getOrderList($params, $field=null){
        $where = [];
        !empty($params['member_id']) && $where[]  = ['member_id', '=', $params['member_id']];
        !empty($params['order_no']) && $where[]   = ['order_no', '=', $params['order_no']];
        !empty($params['start']) && $where[]      = ['created_at', '>=', strtotime(getStartDate($params['start']))];
        !empty($params['end']) && $where[]        = ['created_at', '<=', strtotime(getEndDate($params['end']))];
        !$field && $field       = 'id, FROM_UNIXTIME(created_at) date, order_no, type, amount, status';
        $query                  = $this->field($field)->where($where);

        if(!empty($params['order']) && !empty($params['order_type'])){
            switch($params['order']){
                case 'money':
                    $order = 'amount';
                    break;
                case 'time':
                    $order = 'created_at';
                    break;
                default:
                    $order = 'created_at';
            }
            switch($params['order_type']){
                case 1:
                    $order_type = 'asc';
                    break;
                case 2:
                    $order_type = 'desc';
                    break;
                default:
                    $order_type = 'asc';
            }

            $query = $query->order($order, $order_type);
        }
        return $query->paginate(request()->param('size'),false,['query'=>request()->param()])->toArray();
    }
}