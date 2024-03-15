<?php


namespace app\common\model;


class Category extends Base
{

    protected $name = 'category';

    function getCategoryList(){
        $order = 'rank asc';
        $field = 'id,name';
        $list = $this->where(['status'=>1])->order($order)->field($field)->select();
        return $list;

    }
}