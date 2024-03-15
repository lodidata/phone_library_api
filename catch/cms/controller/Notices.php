<?php
// +----------------------------------------------------------------------
// | Catch-CMS Design On 2020
// +----------------------------------------------------------------------
// | CatchAdmin [Just Like ～ ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2020 http://catchadmin.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://github.com/yanwenwu/catch-admin/blob/master/LICENSE.txt )
// +----------------------------------------------------------------------
// | Author: JaguarJack [ njphper@gmail.com ]
// +----------------------------------------------------------------------

namespace catchAdmin\cms\controller;

use catcher\base\CatchRequest as Request;
use catcher\CatchResponse;
use catcher\base\CatchController;
use catchAdmin\cms\model\Notices as NoticesModel;
use catchAdmin\cms\validate\NoticesValidate;
use catcher\Utils;

class Notices extends CatchController
{
    protected $noticesModel;

    public function __construct(NoticesModel $noticesModel)
    {
        $this->noticesModel = $noticesModel;
    }

    /**
     * 列表
     * @time 2020年12月27日 19:40
     * @param Request $request
     */
    public function index(Request $request) : \think\Response
    {
        return CatchResponse::paginate($this->noticesModel->getList());
    }

    /**
     * 保存信息
     * @time 2020年12月27日 19:40
     * @param Request $request
     */
    public function save(Request $request) : \think\Response
    {
        try{
            $params = $request->post();
            $validate = new NoticesValidate;
            $res = $validate->check($params);
            if(!$res){
                throw new \think\Exception($validate->getError());
            }
            $params['start_time'] = strtotime($params['start_time']);
            $params['end_time'] = strtotime($params['end_time']);
            $res = $this->noticesModel->storeBy($params);
            if(!$res){
                throw new \think\Exception($this->noticesModel->getError());
            }
        } catch (\Exception $e){
            return CatchResponse::fail($e->getMessage());
        }
        return CatchResponse::success('添加成功');
    }

    /**
     * 读取
     * @time 2020年12月27日 19:40
     * @param $id
     */
    public function read($id) : \think\Response
    {
        return CatchResponse::success($this->noticesModel->findBy($id));
    }

    /**
     * 更新
     * @time 2020年12月27日 19:40
     * @param Request $request
     * @param $id
     */
    public function update(Request $request, $id)
    {
        $params = $request->filterEmptyField()->param();
        try{
            $validate = new NoticesValidate;
            $res = $validate->check($params);
            if(!$res){
                throw new \think\Exception($validate->getError());
            }
            $params['start_time'] = strtotime($params['start_time']);
            $params['end_time'] = strtotime($params['end_time']);
            $res = $this->noticesModel->updateBy($id, $params);
            if(!$res){
                throw new \think\Exception($this->noticesModel->getError());
            }
        } catch (\Exception $e){
            return CatchResponse::fail($e->getMessage());
        }
        return CatchResponse::success('更新成功');
    }

    /**
     * 删除
     * @time 2020年12月27日 19:40
     * @param $id
     */
    public function delete($id) : \think\Response
    {
        return CatchResponse::success($this->noticesModel->deleteBy($id));
    }

    /**
     *
     * @time 2019年12月07日
     * @param $id
     * @return \think\response\Json
     */
    public function switchStatus($id): \think\response\Json
    {
        $ids = Utils::stringToArrayBy($id);
        foreach ($ids as $_id) {
            $info = $this->noticesModel->findBy($_id);
            $this->noticesModel->updateBy($_id, [
                'status' => $info->status == $this->noticesModel::ENABLE ? $this->noticesModel::DISABLE : $this->noticesModel::ENABLE,
            ]);
        }

        return CatchResponse::success([], '操作成功');
    }
}
