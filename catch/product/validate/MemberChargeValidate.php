<?php


namespace catchAdmin\product\validate;

use catchAdmin\product\model\Member as MemberModel;
use think\validate;

class MemberChargeValidate extends validate
{

    protected $rule = [
        'member_id' => 'require|checkMember',
        'amount' => 'require|float',
        'type' => 'require|in:1,2',
        'images' => 'file',
       // 'status' => 'require|in:1,2',
    ];

    protected $message = [
        'member_id.require' => '会员ID不能为空',
        'amount.require' => '充值金额不能为空',
        'type.require' => '类型不能为空',
        'status.require' => '充值状态不能为空'
    ];


    protected function checkMember($value, $rule, $data = [])
    {
        $ret = MemberModel::find($value);
        if (empty($ret)) {
            return '会员不存在';
        }
        return true;
    }


}