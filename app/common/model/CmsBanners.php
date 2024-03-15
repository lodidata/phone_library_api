<?php


namespace app\common\model;


use catcher\CatchUpload;

class CmsBanners extends Base
{
    function getBannersList($params=null, $field=null){
        !$field && $field = 'title, banner_img, link_to';
        $where            = [
            ['status', '=', 1],
            ['deleted_at', '=', 0],
        ];
        $list = $this->field($field)->where($where)->order('sort','asc')->select()->toArray();
        if($list){
            foreach ($list as &$v){
                $v['banner_img'] = CatchUpload::getCloudDomain('local').$v['banner_img'];
            }
            unset($v);
        }
        return $list;
    }
}