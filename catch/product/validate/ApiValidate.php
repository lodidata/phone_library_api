<?php


namespace catchAdmin\product\validate;

use catchAdmin\product\model\Api as ApiModel;
use catchAdmin\product\model\Product as ProductModel;
use think\Validate;

class ApiValidate extends Validate
{


    public $request_param_data = ['参数' => 'param', '说明' => 'remarks', '是否必填' => 'is_require', '类型' => 'type', '示例' => 'examples'];

    public $response_param_data = ['param', 'remarks', 'type'];

    public $demo_example_data = ['php'];

    protected $rule = [
        'product_id' => 'require|checkProduct',
        'name' => 'require|max:20|checkName',
        'url' => 'require|url',
        'status' => 'require|in:1,2',      // 状态 1 启用 2禁用
        'sort' => 'integer',                    // 排序
        'price' => 'require',                    //  价格
        'charge_type' => 'require|in:1,2',  // 收费类型 1 按条 1按次
        'method_type' => 'require|in:1,2',  // 请求方式 1 GET 2 POST
        'request_param' => 'require|checkRequest', // 请求参数
        'response_param' => 'require|checkResponse', //返回参数
        'response_success' => 'require',              // 返回成功示例
        'response_failed' => 'require',   // 返回失败示例
        'detail' => 'require',                //详细说明
        'demo_example' => 'require|checkDemo',   // demo 示例
        'id' => 'require',
    ];

    protected $message = [
        'product_id.require' => '产品ID不能为空',
        'product_id.checkProduct' => '产品ID不存在',
        'rank.integer' => '排序值必须是整数',
        'name.require' => '名称不能为空',
        'name.max' => '名称最多不能超过25个字符',
        'status.require' => '状态不能为空',
        'price.require' => '产品价格不能为空',
        'charge_type.require' => '收费类型不能为空',
        'method_type.require' => '请求方式不能为空',
        'request_param.require' => '请求参数不能为空',
        'response_param.require' => '返回参数不能为空',
        'response_success.require' => '返回成功示例不能为空',
        'response_failed.require' => '返回失败示例不能为空',
        'demo_example.require' => 'demo示例不能为空',
        'detail.require' => '接口详细说明不能为空'
    ];

    /**
     * TODO 判断产品是否存在
     */
    protected function checkProduct($value, $rule, $data = [])
    {
        $ret = ProductModel::find($value);
        if (empty($ret)) {
            return '产品不不存在';
        }
        return true;
    }


    /**
     * 判断接口名称是否重复
     */
    protected function checkName($value, $rule, $data = [])
    {
        $ret = ApiModel::where(['name' => $value])->find();
        if (!empty($ret) && (empty($data['id']) || $data['id'] != $ret['id'])) {
            return '接口名称重复';
        }
        return true;
    }

    /**
     * TODO 验证请求参数
     */
    protected function checkRequest($value, $rule, $data = [])
    {
        $json_data = json_decode($value, true);
        foreach ($json_data as $val) {
            foreach ($this->request_param_data as $key) {
                if (empty($val[$key])) {
                    return "请求参数：{$key}不能为空";
                }
            }
        }
        return true;
    }

    /**
     * TODO 验证返回参数
     */
    protected function checkResponse($value, $rule, $data = [])
    {
        $json_data = json_decode($value, true);
        foreach ($json_data as $val) {
            foreach ($this->response_param_data as $key) {
                if (empty($val[$key])) {
                    return "返回参数：{$key}不能为空";
                }
            }
        }
        return true;
    }

    /**
     * TODO 验证demo示例
     */
    protected function checkDemo($value, $rule, $data = [])
    {
        $json_data = json_decode($value, true);
        foreach ($this->demo_example_data as $key) {
            if (empty($json_data[$key])) {
                return "demo示例：{$key}不能为空";
            }
        }
        return true;
    }


    protected $scene = [
        'update'  =>  ['name','price','sort','status'],
    ];
}
