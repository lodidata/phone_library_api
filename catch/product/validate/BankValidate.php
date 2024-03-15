<?php


namespace catchAdmin\product\validate;

use think\validate;

class BankValidate extends validate
{

    protected $rule = [
        'bank_account' => 'require',
        'bank_name' => 'require',
        'bank_code' => 'require'
    ];

    protected $message = [
        'bank_account.require' => '银行账户不能为空',
        'bank_name.require' => '银行名称不能为空',
        'bank_code.require' => '银行卡号不能为空'
    ];


}
