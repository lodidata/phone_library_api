<?php


namespace app\common\service;
use app\common\model\Category as CategoryModel;


class CategoryService extends BaseService
{
    public function __construct() {
        //数据操作类
        $this->datamodel = new CategoryModel();
        //主键
        $this->pk = $this->datamodel->getPk();
    }
    function getCategoryList(){

        $list = $this->datamodel->getCategoryList()->toArray();

        return $list;
    }

}