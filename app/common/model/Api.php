<?php

namespace app\common\model;


class Api extends Base
{
    protected $name = 'api';

    function getInfoById($where, $field)
    {
        $order = 'sort asc';
        return $this->where($where)->order($order)->field($field)->select();
    }

    function getInfoByIdFind($where, $field)
    {

        return $this->where($where)->field($field)->find();
    }

    /**
     * TODO 获取产品id
     */
    function getProductNames($ids)
    {
        $data = $this->whereIn('id', $ids)->column('product_id', 'id');
        $names = (new Product())->getNames($data);
        $ret = [];
        foreach ($data as $key => $val) {
            $ret[$key] = $names[$val] ?? '';
        }
        return $ret;
    }

    /**
     * 根据产品id获取api id
     * @param $productId
     * @return array
     */
    public function getApiId($productId){
        return $this->where('product_id', $productId)->column('id');
    }

    /**
     * 根据api_id获取产品id
     * @param $apiIds
     * @return mixed
     */
    public function getProductIds($apiIds){
        return $this->whereIn('id', $apiIds)->group('product_id')->column('product_id');
    }

}