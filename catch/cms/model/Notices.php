<?php

namespace catchAdmin\cms\model;

use catcher\base\CatchModel;
use think\Model;
use DateTime;
/**
 * Class Notices
 * @package catchAdmin\cms\model
 * @auth CatchAdmin
 * @time 2021年05月22日
 *
 * @property Notices id
 * @property Notices title
 * @property Notices content
 * @property Notices startTime
 * @property Notices endTime
 * @property Notices creator_id
 * @property Notices created_at
 * @property Notices updated_at
 * @property Notices deleted_at
 * @property Notices status
 *
 */
class Notices extends CatchModel
{
    // 表名
    public $name = 'cms_notices';
    // 数据库字段映射
    public $field = array(
        'id',
        //标题
        'title',
        //内容
        'content',
        // 1 展示 2 隐藏
        'status',
        //开始时间
        'start_time',
        //结束时间
        'end_time',
        // 创建人ID
        'creator_id',
        // 创建时间
        'created_at',
        // 更新时间
        'updated_at',
        // 软删除
        'deleted_at',
    );

    public static function onAfterRead($data)
    {
        $dateTime = new DateTime();
        $dateTime->setTimestamp((int) $data->start_time);
        $data->start_time_txt = $dateTime->format('Y-m-d H:i:s');
        $dateTime->setTimestamp((int) $data->end_time);
        $data->end_time_txt = $dateTime->format('Y-m-d H:i:s');
        return $data;
    }

    public function searchStatusAttr($query, $value, $data)
    {
        $query->where('status', $value);
    }
}
