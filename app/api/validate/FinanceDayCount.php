<?php


namespace app\api\validate;

use think\Validate;
class FinanceDayCount extends Validate
{
    protected $rule =   [
        'start'  => 'require|date|max:10',
        'end'    => 'require|date|max:10',
    ];
    protected $message  =   [
        'start.require' => '开始日期不能为空',
        'end.require'   => '结束日期不能为空',
        'start.max'     => '开始日期不能超过10位数',
        'end.max'       => '结束日期不能超过10位数',
    ];

}