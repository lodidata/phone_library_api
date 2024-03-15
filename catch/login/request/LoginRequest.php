<?php
namespace catchAdmin\login\request;

use catcher\base\CatchRequest;

class LoginRequest extends CatchRequest
{
    protected $needCreatorId = false;

    protected function rules(): array
    {
        // TODO: Implement rules() method.
        return [
            //'email|用户名'    => 'email',
            'username|用户名'    => 'require',
            'password|密码'  => 'require',
            'code|验证码' => 'require'
        ];
    }

    protected function message(): array
    {
        // TODO: Implement message() method.
        return [];

    }
}
