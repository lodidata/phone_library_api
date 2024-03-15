<?php
// +----------------------------------------------------------------------
// | Catch-CMS Design On 2020
// +----------------------------------------------------------------------
// | CatchAdmin [Just Like ～ ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2020 http://catchadmin.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://github.com/yanwenwu/catch-admin/blob/master/LICENSE.txt )
// +----------------------------------------------------------------------
// | Author: JaguarJack [ njphper@gmail.com ]
// +----------------------------------------------------------------------

namespace catchAdmin\cms\model;

use catcher\base\CatchModel;
class Banners extends CatchModel
{
    // 表名
    public $name = 'cms_banners';
    // 数据库字段映射
    public $field = array(
        'id',
        // banner 标题
        'title',
        // banner 图片
        'banner_img',
        //排序
        'sort',
        // 链接地址
        'link_to',
        // 创建人ID
        'creator_id',
        // 创建时间
        'created_at',
        // 更新时间
        'updated_at',
        // 软删除
        'deleted_at',
        // 1 展示 2 隐藏
        'status',
    );
}
