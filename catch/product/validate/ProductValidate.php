<?php

namespace catchAdmin\product\validate;

use think\Validate;
use catchAdmin\product\model\Product as ProductModel;

class  ProductValidate extends Validate
{
    protected $rule = [
        'name' => 'require|max:20|checkName',
        'icon' => 'require',
        'describe' => 'require|max:20',
        'status' => 'require|in:0,1,2',
    ];

    protected $message = [
        'name.require' => '产品名称不能为空',
        'name.max' => '产品名称最多不能超过25个字符',
        'describe.require' => '描述不能为空',
        'status.require' => '状态不能为空',
    ];

    /**
     * 判断产品名称是否重复
     */
    protected function checkName($value, $rule, $data = [])
    {
        $ret = ProductModel::where(['name' => $value])->find();
        if (!empty($ret) && (empty($data['id']) || $data['id'] != $ret['id'])) {
            return '产品名称重复';
        }
        return true;
    }
}
