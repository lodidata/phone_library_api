<?php

namespace catchAdmin\product\model;

use catcher\base\CatchModel as Model;

class Api extends Model
{
    // 表名
    public $name = 'api';

    // 数据库字段映射
    public $field = array(
        'id',
        // 产品ID
        'product_id',
        // 接口名称
        'name',
        // 接口url
        'url',
        // 排序 越小越靠前
        'sort',
        // 1 启用 2禁用
        'status',
        // 价格
        'price',
        // 收费类型 1 按条 2按次
        'charge_type',
        // 请求方式 1 GET 2 POST
        'method_type',
        // http方式 1 http 2 https
        'http_type',
        // 请求参数
        'request_param',
        // 返回参数
        'response_param',
        // 返回成功示例
        'response_success',
        // 返回失败示例
        'response_failed',
        // 详细说明
        'detail',
        // demo 示例
        'demo_example',
        // 每天可调用的次数
        'rate_num',
        // 免费调用次数
        'free_num',
        // 是否推荐接口 1 是 2 不是
        'is_recommend',
        // 添加时间
        'created_at',
        // 修改时间
        'updated_at',
        // 删除时间
        'deleted_at',
    );

    public function getList()
    {
        $params = \request()->param();
        $where = [
            'product_id' => $params['product_id']
        ];

        // 不分页
        if (property_exists($this, 'paginate') && $this->paginate === false) {
            return $this->catchSearch()
                ->field('id, product_id, name ,url, price, charge_type, status, sort')
                ->where($where)
                ->catchOrder('asc')
                ->select();
        }

        // 分页列表
        return $this->catchSearch()
            ->field('id, product_id, name ,url, price, charge_type, status, sort')
            ->where($where)
            ->catchOrder('asc')
            ->paginate();
    }

    /**
     * @param $query
     * @param $value
     * @param $data
     * TODO 根据接口名称查找
     */
    public function searchNameAttr($query, $value, $data)
    {
        $query->where(['name' => $value]);
    }

    /**
     * @param $query
     * @param $value
     * @param $data
     * TODO 根据启用状态查找
     */
    public function searchStatusAttr($query, $value, $data)
    {
        $query->where(['status' => $value]);
    }

    /**
     * @param $query
     * @param $value
     * @param $data
     * TODO 根据是否推荐查找
     */
    public function searchIsRecommendAttr($query, $value, $data)
    {
        $query->where(['is_recommend' => $value]);
    }


}
