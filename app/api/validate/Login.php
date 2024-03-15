<?php


namespace app\api\validate;

use think\Validate;
class Login extends Validate
{
    protected $rule =   [
        'user_account' => 'require|min:4|max:16|alphaNum',
        'password'     => 'require|min:6|max:16|alphaDash',
        'code'         => 'require|alphaNum',
        'key'          => 'require',
    ];
    protected $message  =   [
        'user_account.require'  => '用户账号不能为空',
        'user_account.min'      => '用户账号最少4位数',
        'user_account.max'      => '用户账号不能超过16位数',
        'user_account.alphaNum' => '用户账号只能由数字或者字母',
        'password'              => '密码不能为空',
        'password.min'          => '密码最少6位数',
        'password.max'          => '密码不能超过16位数',
        'password.alphaDash'      => '密码只能是字母、数字和下划线_及破折号-',
        'code.require'          => '验证码不能为空',
        'code.alphaNum'         => '验证码只能由数字或者字母',
        'key.require'           => 'key不能为空',
    ];

}
