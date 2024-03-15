<?php


namespace app\api\validate;

use think\Validate;
class MonthCount extends Validate
{
    protected $rule =   [
        'id'     => 'require|number',
        'start'  => 'require|date|max:7',
        'end'    => 'require|date|max:7',
    ];
    protected $message  =   [
        'id.require'    => 'id不能为空',
        'start.require' => '开始日期不能为空',
        'end.require'   => '结束日期不能为空',
        'start.max'     => '开始日期不能超过7位数',
        'end.max'       => '结束日期不能超过7位数',
    ];

}