<?php


namespace app\common\service;


use app\common\model\Bank as BankModel;

class BankService extends BaseService
{
    public function __construct()
    {
        //数据操作类
        $this->datamodel = new BankModel();
    }

    public function getList(){
        return $this->datamodel->getList();
    }
}