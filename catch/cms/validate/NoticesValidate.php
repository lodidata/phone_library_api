<?php


namespace catchAdmin\cms\validate;

use think\validate;

class NoticesValidate extends validate
{

    protected $rule = [
        'title|标题' => 'require',
        'content|内容' => 'require',
        'start_time|开始时间' => 'require|date',
        'end_time|结束时间' => 'require|date',
    ];

    protected $message = [
    ];


}
