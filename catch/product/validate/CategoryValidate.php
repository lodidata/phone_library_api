<?php


namespace catchAdmin\product\validate;

use catchAdmin\product\model\Category as categoryModel;
use think\Validate;

class CategoryValidate extends Validate
{
    protected $rule = [
        'name' => 'require|max:20|checkName',
        'status' => 'require|in:1,2',
        'id' => 'require',
    ];

    protected $message = [
        'name.require' => '名称不能为空',
        'name.max' => '名称最多不能超过25个字符',
        'status.require' => '状态不能为空',
    ];

    /**
     * 判断产品名称是否重复
     */
    protected function checkName($value, $rule, $data = [])
    {
        $ret = categoryModel::where(['name' => $value])->find();
        if (!empty($ret) && (empty($data['id']) || $data['id'] != $ret['id'])) {
            return '产品名称重复';
        }
        return true;
    }

    /**
     * TODO 添加数据的验证场景
     */
    public function sceneSave()
    {
        return $this->remove('id', 'require');
    }

    /**
     * TODO 修改数据的验证场景
     */
    public function sceneEdit()
    {
        return $this->remove('status', 'require');
    }


}