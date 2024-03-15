<?php

namespace catchAdmin\product\controller;

use catchAdmin\product\validate\CategoryValidate;
use catcher\CatchResponse;
use catcher\base\CatchController;
use catchAdmin\product\model\Category as CategroyModel;
use catcher\base\CatchRequest as Request;
use Exception;
use think\exception\ValidateException;

class Category extends CatchController
{
    protected $model;

    public function __construct(CategroyModel $model)
    {
        $this->model = $model;
    }

    /**
     * TODO 栏目列表
     */
    public function index()
    {
        return CatchResponse::paginate($this->model->getList());
    }

    /**
     * TODO  添加栏目
     */
    public function save(Request $request)
    {
        $data = $request->post();
        try {
            validate(CategoryValidate::class)
                ->scene('save')
                ->check($data);
            $this->model->storeBy($data);
        } catch (ValidateException $e) {
            return CatchResponse::fail($e->getError());
        } catch (Exception $e) {
            return CatchResponse::fail('添加失败');
        }
        return CatchResponse::success([], '添加成功');
    }

    /**
     *  TODO 修改栏目
     */
    public function update(Request $request)
    {
        $data = $request->post();
        try {
            validate(CategoryValidate::class)
                ->scene('edit')
                ->check($data);
            $this->model->updateBy($data['id'], $data);
        } catch (ValidateException $e) {
            return CatchResponse::fail($e->getError());
        } catch (Exception $e) {
            return CatchResponse::fail('修改失败');
        }
        return CatchResponse::success([], '修改成功');
    }
}