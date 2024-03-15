<?php

namespace catchAdmin\product\controller;

use app\common\service\WalletLogService;
use catchAdmin\product\model\Member as MemberModel;
use catcher\base\CatchRequest as Request;
use catcher\CatchResponse;
use catcher\base\CatchController;
use catchAdmin\product\model\WalletLog as walletLogModel;
use think\Exception;
use think\exception\ValidateException;

class WalletLog extends CatchController
{
    protected $walletLogModel;
    
    public function __construct(WalletLogModel $walletLogModel)
    {
        $this->walletLogModel = $walletLogModel;
    }

    /**
     * 消费记录明细列表
     * @param Request $request
     * @return \think\Response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(Request $request) : \think\Response\Json
    {
        return CatchResponse::paginate($this->walletLogModel->getList());
    }

    /**
     * 交易统计
     * @param Request $request
     * @return \think\Response\Json
     */
    public function countList(Request $request)
    {
        $params = $request->filterEmptyField()->param();
        if(!isset($params['start_time'])){
            $params['start_time'] = date('Y-m-d', strtotime('-10 day'));
            $params['end_time'] = date('Y-m-d');
        }
        try {
            $validateRule = [
                'user_account|账号' => 'require',
                'start_time|开始时间' => 'date',
                'end_time|开始时间' => 'date',
            ];
            validate($validateRule)->check($params);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            return CatchResponse::fail($e->getError());
        }

        //账号 精准查
        $memberModel = new MemberModel();
        $memberId = $memberModel->where('user_account', $params['user_account'])->value('id');
        if(!$memberId){
            return CatchResponse::success();
        }
        $walletLogService = new WalletLogService();
        $list = $walletLogService->countList($memberId, $params['start_time'], $params['end_time']);
        exit(ajaxReturn('success',$list));
    }
}