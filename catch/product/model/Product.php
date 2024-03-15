<?php

namespace catchAdmin\product\model;

use catcher\base\CatchModel;

class Product extends CatchModel
{
    // 表名
    public $name = 'product';
    //未激活
    public const NOACTIVE = 0;
    // 数据库字段映射
    public $field = array(
        'id',
        // 图标
        'icon',
        // 名称
        'name',
        // 描述
        'describe',
        // 状态 0 未激活  1 已激活 2 停用
        'status',
        'sort',
        //推荐 0不推荐 1推荐
        'is_recommend',
        // 注册时间
        'created_at',
        // 更新时间
        'updated_at',
        // 删除时间
        'deleted_at',
    );

    /**
     * 查询列表
     *
     * @time 2020年04月28日
     * @return mixed
     */
    public function getList()
    {
        // 不分页
        if (property_exists($this, 'paginate') && $this->paginate === false) {
            return $this->catchSearch()
                ->field('*')
                ->catchOrder('asc')
                ->creator()
                ->select();
        }

        // 分页列表
        return $this->catchSearch()
            ->field('*')
            ->catchOrder('asc')
            ->creator()
            ->paginate();
    }
}
