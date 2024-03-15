<?php
namespace catchAdmin\product\controller;

use catchAdmin\product\model\MemberIp as IpsModel;
use catcher\base\CatchRequest as Request;
use catcher\CatchResponse;
use catcher\base\CatchController;

/**
 * IP白名单
 */
class MemberIp extends CatchController
{
    protected $ipModel;

    public function __construct(IpsModel $ipModel)
    {
        $this->ipModel = $ipModel;
    }

    /**
     * 列表
     * @return \think\response\Json
     */
    public function index()
    {
        return CatchResponse::paginate($this->ipModel->getList());
    }

    /**
     * 详情
     * @param $id
     * @return \think\response\Json
     */
    public function read($id)
    {
        return CatchResponse::success($this->ipModel->field('id,member_id,ips')->find($id));
    }

    /**
     * 编辑IPS
     * @param Request $request
     * @param $id
     * @return \think\response\Json
     */
    public function update(Request $request, $id)
    {
        $ips = $request->param('ips');
        !$ips && exit(ajaxReturn('ips不能为空',[],10001));

        return CatchResponse::success($this->ipModel->update(['ips' => $ips], ['id' => $id]));
    }
}