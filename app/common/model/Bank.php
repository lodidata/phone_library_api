<?php


namespace app\common\model;


class Bank extends Base
{
    public function getList($field = null){
        !$field && $field = 'id,bank_name,bank_address,bank_account name,bank_code account';
        $where = [
            ['status','=',1],
            ['deleted_at','=',0],
        ];
        return $this->field($field)->where($where)->order('sort','asc')->select()->toArray();
    }

    public function getInfo($id, $field = null){
        !$field && $field = 'id,bank_name,bank_address,bank_account name,bank_code account';
        $where = [
            ['status','=',1],
            ['deleted_at','=',0],
        ];
        return $this->field($field)->where($where)->find($id);
    }
}