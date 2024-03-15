<?php


namespace app\api\validate;

use think\Validate;
class Product extends Validate
{
    protected $rule =   [
        'id'  => 'require|number',
    ];
    protected $message  =   [
        'id.require' => 'id不能为空',
    ];

}