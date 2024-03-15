<?php

namespace catchAdmin\product\controller;

use catcher\base\CatchRequest as Request;
use catcher\CatchResponse;
use catcher\base\CatchController;
use catchAdmin\product\model\Product as productModel;
use catcher\CatchUpload;
use catcher\Utils;
use Exception;
use catchAdmin\product\validate\ProductValidate;
use think\exception\ValidateException;

class Index extends CatchController
{
    protected $productModel;

    public function __construct(ProductModel $productModel)
    {
        $this->productModel = $productModel;
    }

    /**
     * 列表
     * @param Request $request
     */
    public function index(Request $request): \think\Response
    {
        return CatchResponse::paginate($this->productModel->getList());
    }

    /**
     * 保存信息
     * @param Request $request
     */
    public function save(Request $request): \think\Response
    {
        $data = $request->post();
        $file = $request->file();
        $upload = new CatchUpload();
        try {
            validate(ProductValidate::class)
                ->scene('save')
                ->check($data);
            !empty($file['icon']) && $data['icon'] = $upload->checkImages($file)->multiUpload($file['icon']);
            $this->productModel->storeBy($data);
        } catch (ValidateException $e) {
            return CatchResponse::fail($e->getError());
        } catch (Exception $e) {
            return CatchResponse::fail('添加失败');
        }
        return CatchResponse::success([], '添加成功');
    }

    /**
     * 读取
     * @param $id
     */
    public function read($id) : \think\Response
    {
        $data = $this->productModel->findBy($id);
        return CatchResponse::success($data);
    }

    /**
     * 更新
     * @param Request $request
     */
    public function update(Request $request, $id): \think\Response
    {
        $data = $request->post();
        $upload = new CatchUpload();
        $file = $request->file();
        try {
            validate(ProductValidate::class)
                ->check($data);
            !empty($file['icon']) && $data['icon'] = $upload->checkImages($file)->multiUpload($file['icon']);
            $this->productModel->updateBy($id, $data);
        } catch (ValidateException $e) {
            return CatchResponse::fail($e->getError());
        } catch (Exception $e) {
            return CatchResponse::fail('修改失败');
        }
        return CatchResponse::success([], '修改成功');
    }

    /**
     * 删除
     * @time 2021年04月28日 10:30
     * @param $id
     * @return \think\response\Json
     */
    public function delete($id)
    {
        $this->productModel->deleteBy($id);
        return CatchResponse::success([], '删除成功');
    }

    /**
     *
     * @param $id
     * @param catchRequest $request
     * @return \think\response\Json
     */
    public function editStatus(Request $request, $id)
    {
        $status = $request->param('status');
        $product = $this->productModel->findBy($id);
        if($product['status'] != $status && $status > 0){
            $this->productModel->updateBy($id, ['status' => $status]);
        }
       return CatchResponse::success([], '操作成功');
    }

}
