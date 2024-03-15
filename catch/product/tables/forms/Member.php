<?php
namespace catchAdmin\product\tables\forms;


use catcher\library\form\Form;

class Member extends Form
{
    public function fields(): array
    {
        // TODO: Implement fields() method.
        return [
            self::input('user_account', '*账号')->clearable(true)->required(),
            self::input('password', '密码')->placeholder('请输入密码')->clearable(true),
            self::input('password_conf', '确认密码')->placeholder('请再次输入密码')->clearable(true),
            self::input('user_name', '名称')->clearable(true)->required(),
            self::input('user_contact', '联系方式')->clearable(true)->required()
        ];
    }

}
