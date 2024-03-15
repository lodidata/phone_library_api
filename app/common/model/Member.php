<?php
namespace app\common\model;

class Member extends Base
{
    protected $name = 'member';
    /*
     * 根据id查询用户信息
     */
    function getUserById($where,$field){
        $info = $this->where($where)->field($field)->find();
        return $info;
    }

    /*
     * 根据id查询用户信息
     */
    function getInfoById($id, $field){
        $info = $this->where(['id' => $id])->field($field)->find();
        return $info;
    }
    /*
     * 根据账号查询用户信息
     */
    function getUserInfo($mobile){
        $info = $this->where(array('user_account'=>$mobile))->find();
        return $info;
    }
    /*
     * 编辑用户信息
     */
    function editInfo($data,$where){
        return $this->where($where)->save($data);
    }
    function getIdBykey($appid,$appkey){
        return $this->where(['appid'=>$appid,'appkey'=>$appkey])->value('id');
    }

}
