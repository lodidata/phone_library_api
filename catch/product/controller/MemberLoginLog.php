<?php

namespace catchAdmin\product\controller;

use catcher\base\CatchRequest as Request;
use catcher\CatchResponse;
use catcher\base\CatchController;
use catchAdmin\product\model\MemberLoginLog as MemberLoginLogModel;

class MemberLoginLog extends CatchController
{
    protected $memberChargeModel;

    public function __construct(MemberLoginLogModel $loginLogModel)
    {
        $this->loginLogModel = $loginLogModel;
    }

    /**
     * 充值列表
     * @time 2021年04月28日 14:29
     * @param Request $request
     */
    public function index(Request $request): \think\Response
    {
        return CatchResponse::paginate($this->loginLogModel->getList());
    }


}
