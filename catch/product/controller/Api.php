<?php

namespace catchAdmin\product\controller;

use app\common\service\CodeService;
use catchAdmin\product\validate\ApiValidate;
use catcher\base\CatchRequest as Request;
use catcher\CatchResponse;
use catcher\base\CatchController;
use catchAdmin\product\model\Api as apiModel;
use think\Exception;
use think\exception\ValidateException;

class Api extends CatchController
{
    protected $apiModel;

    public function __construct(ApiModel $apiModel)
    {
        $this->apiModel = $apiModel;
    }

    /**
     * @param Request $request
     * @return \think\Response
     * TODO 接口列表
     */
    public function index(Request $request): \think\Response
    {
        $proudct_id = $request->filterEmptyField()->param('product_id');
        if(!$proudct_id){
            return CatchResponse::fail('缺少产品参数');
        }
        return CatchResponse::paginate($this->apiModel->getList());
    }

    /**
     * @param Request $request
     * @return \think\Response
     * TODO  添加接口
     */
    /*public function save(Request $request): \think\Response
    {
        $data = $request->post();
        try {
            validate(ApiValidate::class)->scene('save')
                ->check($data);
            $data['http_type'] = $this->getUrlType($data['url']);
            $this->apiModel->storeBy($data);
        } catch (ValidateException $e) {
            return CatchResponse::fail($e->getError());
        } catch (Exception $e) {
            return CatchResponse::fail('添加失败');
        }
        return CatchResponse::success([], '添加成功');
    }*/

    /**
     * @param Request $request
     * @return \think\Response
     * TODO 获取接口详情
     */
    public function detail(Request $request): \think\Response
    {
        $id = $request->get('id');
        $data = $this->apiModel->findBy($id);
        if (!empty($data)) {
            $data['request_param'] = json_decode($data['request_param'], true);
            $data['response_param'] = json_decode($data['response_param'], true);
            $data['demo_example'] = json_decode($data['demo_example'], true);
        }
        return CatchResponse::success($data);
    }

    /**
     * @param Request $request
     * @return \think\Response
     * TODO 更新接口 名称、价格、排序、状态
     */
    public function update(Request $request, $id): \think\Response
    {
        $data = $request->post();
        try {
            validate(ApiValidate::class)->scene('update')
                ->check($data);
            $info = $this->apiModel->findBy($id);
            if(!$info){
                throw new \Exception('接口不存在');
            }
            $this->apiModel->updateBy($id, $data);
        } catch (ValidateException $e) {
            return CatchResponse::fail($e->getError());
        } catch (Exception $e) {
            return CatchResponse::fail($e->getMessage());
        }
        return CatchResponse::success([], '修改成功');
    }

    /**
     * 删除
     * @time 2021年04月27日 14:23
     * @param $id
     */
    /*public function delete($id): \think\Response
    {
        return CatchResponse::success($this->apiModel->updateBy($id, ['status' => 2]));
    }*/

    /**
     * @param $url
     * TODO 获取url的类型
     */
    protected function getUrlType($url)
    {
        return parse_url($url)['scheme'] == 'http' ? 1 : 2;
    }

    //获取状态码
    function getCode(Request $request){
        $codeService = new CodeService();
        $list        = $codeService->getList();
        return CatchResponse::success($list, '操作成功');
    }
}
