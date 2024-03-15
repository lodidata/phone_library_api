<?php
namespace catchAdmin\login\validate;

use think\validate;

class LoginValidate extends validate
{
    protected $rules = [
            //'email|用户名'    => 'email',
            'username|用户名'    => 'require',
            'password|密码'  => 'require',
            'code|验证码' => 'require'
        ];


    protected $message = [
    ];
}
