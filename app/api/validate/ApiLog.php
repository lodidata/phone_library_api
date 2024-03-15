<?php


namespace app\api\validate;

use think\Validate;
class ApiLog extends Validate
{
    protected $rule =   [
        'id'     => 'require|number',
        'start'  => 'require|date',
        'end'    => 'require|date',
    ];
    protected $message  =   [
        'id.require'    => 'id不能为空',
        'start.require' => '开始日期不能为空',
        'end.require'   => '结束日期不能为空',
    ];

}