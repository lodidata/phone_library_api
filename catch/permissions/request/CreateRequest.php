<?php
namespace catchAdmin\permissions\request;

use catchAdmin\permissions\model\Users;
use catcher\base\CatchRequest;

class CreateRequest extends CatchRequest
{

    protected function rules(): array
    {
        // TODO: Implement rules() method.
        return [
            'username|用户名' => 'require|max:20|unique:'.Users::class,
            'password|密码' => 'require|min:5|max:12',
            'repassword|确认密码' => 'require|confirm:password',
            //'email|邮箱'    => 'require|email|unique:'.Users::class,
        ];
    }

    protected function message()
    {
        // TODO: Implement message() method.
    }
}
