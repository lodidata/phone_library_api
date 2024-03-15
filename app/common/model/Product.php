<?php

namespace app\common\model;
use app\common\model\Api;

class Product extends Base
{

    protected $name = 'product';

    function getProductList($where,$order){
        $field = "p.id,p.icon,p.name,p.describe,max(a.price) price,a.charge_type";
        $list  = $this->alias('p')->leftJoin('api a','p.id=a.product_id')->where($where)->order($order)->field($field)->group('p.id')->select();
        return $list;
    }

    /**
     * @param $ids
     * TODO 获取产品名称
     */
    public function getNames($ids){
       return $this->whereIn('id', $ids)->column('name', 'id');
    }

    /**
     * 根据api_ids获取产品名称
     * @param $apiIds
     * @return array
     */
    public function getNamesByApiIds($apiIds){
        $apiIds     = explode(',', $apiIds);
        $api_model  = new Api();
        $product_id = $api_model->getProductIds($apiIds);
        return $this->whereIn('id', $product_id)->value('group_concat(`name`) name');
    }
}