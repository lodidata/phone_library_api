<?php

namespace catchAdmin\product\model;

use catcher\base\CatchModel;

class ApiLog extends CatchModel
{
    // 表名
    public $name = 'api_log';
    // 数据库字段映射
    public $field = array(
        'id',
        // 接口ID
        'api_id',
        // 会员ID
        'member_id',
        // api_reuslt表id
        'result_id',
        // 成功号码次数
        'success_num',
        //失败号码数
        'fail_num',
        //消耗金额
        'money',
        //调用结果1成功 2失败
        'code',
        // 写入时间
        'created_at',
    );
}