<?php


namespace app\api\validate;

use think\Validate;
class ModifyPassword extends Validate
{
    protected $rule =   [
        'old_password' => 'require|min:6|max:16|alphaDash',
        'password'     => 'require|min:6|max:16|alphaDash',
        'repassword'   => 'require|confirm:password'
    ];
    protected $message  =   [
        'old_password'          => '旧密码不能为空',
        'old_password.min'      => '旧密码最少6位数',
        'old_password.max'      => '旧密码不能超过16位数',
        'old_password.alphaDash'  => '旧密码只能是字母、数字和下划线_及破折号-',
        'password'              => '密码不能为空',
        'password.min'          => '密码最少6位数',
        'password.max'          => '密码不能超过16位数',
        'password.alphaDash'      => '密码只能是字母、数字和下划线_及破折号-',
        'repassword.require'    => '确认密码不能为空',
        'repassword.confirm'    => '两次输入密码不一致',
    ];

    protected $scene = [
        'editpwd'  =>  ['password','repassword']
    ];
}
