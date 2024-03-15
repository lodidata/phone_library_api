<?php

namespace app\common\service;


use app\common\model\Bank as BankModel;
use app\common\model\MemberCharge as MemberChargeModel;

class FinanceService  extends BaseService
{

    public function deposit($params){
        $bank_model          = new BankModel();
        $member_charge_model = new MemberChargeModel();
        $bankcard            = $bank_model->getInfo($params['bank_id']);

        $data = [
            'member_id'      => $params['member_id'],
            'amount'         => $params['amount'],
            'bank_id'        => $params['bank_id'],
            'order_no'       => getOrderNum(),
            'charge_account' => json_encode($bankcard),
            'images'         => $params['images'],
            'status'         => $params['status'],
            'type'           => 1,
            'created_at'     => $params['created_at'],
            'remark'         => '',
        ];
        return $member_charge_model->saveData($data);
    }

    /**
     * 订单列表
     * @param $params
     * @return array
     */
    public function orderList($params){
        $member_charge_model = new MemberChargeModel();
        $list                = $member_charge_model->getOrderList($params);
        if($list['data']){
            //充值状态 0待处理 1已通过 2未通过
            $status = ['待处理','已通过','未通过'];
            foreach ($list['data'] as &$v){
                $v['amount'] = floatNumber($v['amount']);
                $v['type']   = getOrderType($v['type']);
                $v['status'] = $status[$v['status']];
            }
            unset($v);
        }
        return $list;
    }
}