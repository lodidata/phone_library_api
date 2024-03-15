<?php


namespace app\api\validate;

use think\Validate;
class Code extends Validate
{
    protected $rule =   [
        'page'  => 'require|number',
        'size'  => 'require|number',
    ];
    protected $message  =   [
        'page.require' => 'page不能为空',
        'size.require' => 'size不能为空',
    ];

}