<?php


namespace app\common\model;


class CmsNotices extends Base
{
    function getNoticeList($params=null, $field=null){
        !$field && $field = 'title, content, from_unixtime(start_time) date';
        $time             = time();
        $where            = [
            ['status', '=', 1],
            ['deleted_at', '=', 0],
            ['start_time', '<=', $time],
            ['end_time', '>=', $time],
        ];
        return $this->field($field)->where($where)->order('start_time','desc')->paginate(request()->param('size'),false,['query'=>request()->param()])->toArray();
    }
}