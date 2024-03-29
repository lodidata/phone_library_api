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
use catchAdmin\cms\model\Banners as bannersModel;
use catcher\Utils;

class Banners extends CatchController
{
    protected $bannersModel;
    
    public function __construct(BannersModel $bannersModel)
    {
        $this->bannersModel = $bannersModel;
    }
    
    /**
     * 列表
     * @time 2020年12月27日 19:58
     * @param Request $request 
     */
    public function index(Request $request) : \think\Response
    {
        return CatchResponse::paginate($this->bannersModel->getList());
    }
    
    /**
     * 保存信息
     * @time 2020年12月27日 19:58
     * @param Request $request 
     */
    public function save(Request $request) : \think\Response
    {
        return CatchResponse::success($this->bannersModel->storeBy($request->post()));
    }
    
    /**
     * 读取
     * @time 2020年12月27日 19:58
     * @param $id 
     */
    public function read($id) : \think\Response
    {
        return CatchResponse::success($this->bannersModel->findBy($id));
    }
    
    /**
     * 更新
     * @time 2020年12月27日 19:58
     * @param Request $request 
     * @param $id
     */
    public function update(Request $request, $id) : \think\Response
    {
        return CatchResponse::success($this->bannersModel->updateBy($id, $request->post()));
    }
    
    /**
     * 删除
     * @time 2020年12月27日 19:58
     * @param $id
     */
    public function delete($id) : \think\Response
    {
        return CatchResponse::success($this->bannersModel->deleteBy($id));
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
            $info = $this->bannersModel->findBy($_id);
            $this->bannersModel->updateBy($_id, [
                'status' => $info->status == $this->bannersModel::ENABLE ? $this->bannersModel::DISABLE : $this->bannersModel::ENABLE,
            ]);
        }

        return CatchResponse::success([], '操作成功');
    }
}