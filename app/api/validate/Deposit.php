<?php


namespace app\api\validate;

use think\Validate;
class Deposit extends Validate
{
    protected $rule =   [
        'bank_id'  => 'require|number',
        'money'    => 'require|checkMoney',
        'image'    => 'require',
    ];
    protected $message  =   [
        'bank_id.require'   => '请选择充值的银行卡',
        'money.require'     => '充值金额不能为空',
        'money.checkMoney'  => '金额必须大于0且最多4位小数',
        'image.require'     => '图片url不能为空',
    ];

    /**
     * 金额校验 金额必须大于0且最多4位小数
     * @return bool
     */
    function checkMoney($value, $rule, $data = []){
        if (!is_numeric($value)) {
            return false;
        }
        if ($value <= 0) {
            return false;
        }

        if (preg_match('/^[0-9]+(\.\d{1,4})?$/', $value)) {
            return true;
        } else {
            return false;
        }
    }
}