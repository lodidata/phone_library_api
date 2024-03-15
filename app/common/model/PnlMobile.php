<?php
namespace app\common\model;
use think\Db;
class PnlMobile extends Base
{

    protected $name = 'pnl_mobile';
    protected $suffix = '';

    //company 运营商保留
    function getMobile($where){

        return Db::name($this->name.'_879')->where($where)->field('mobile,province,city,type,is_use,use_last_time')->select();
    }
}