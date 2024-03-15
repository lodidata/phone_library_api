<?php


namespace app\api\validate;

use think\Validate;
class FinanceMonthCount extends Validate
{
    protected $rule =   [
        'start'  => 'require|date|max:7',
        'end'    => 'require|date|max:7',
    ];
    protected $message  =   [
        'start.require' => '开始日期不能为空',
        'end.require'   => '结束日期不能为空',
        'start.max'     => '开始日期不能超过7位数',
        'end.max'       => '结束日期不能超过7位数',
    ];

}