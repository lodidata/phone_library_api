<?php

namespace catchAdmin\product\controller;

use catcher\base\CatchRequest as Request;
use catcher\CatchResponse;
use catcher\base\CatchController;
use catchAdmin\product\model\Member as MemberModel;
use catchAdmin\product\model\MemberCharge as MemberChargeModel;
use catchAdmin\product\model\WalletLog as WalletLogModel;

class MemberCharge extends CatchController
{
    protected $memberChargeModel;

    public function __construct(MemberChargeModel $memberChargeModel)
    {
        $this->memberChargeModel = $memberChargeModel;
    }

    /**
     * 充值列表
     * @time 2021年04月28日 14:29
     * @param Request $request
     */
    public function index(Request $request): \think\Response
    {
        return CatchResponse::paginate($this->memberChargeModel->getList());
    }

    /**
     * 充值审核
     * @param Request $request
     */
    public function verify(Request $request, $id): \think\response\Json
    {
        try {
            $data = $request->filterEmptyField()->post();

            if(empty($data['remark'])){
                return CatchResponse::fail("审核备注不能为空");
            }
            $data['status'] = $data['status'] == 1 ? 1: 2;

            $chargeInfo = $this->memberChargeModel->findBy($id);
            if(!$chargeInfo){
                return CatchResponse::fail("充值记录不存在");
            }
            if($chargeInfo['status'] != 0){
                return CatchResponse::fail("充值已经审核过了");
            }
            $memberModel = new MemberModel();
            $memberInfo = $memberModel->field('id,wallet')->find($chargeInfo['member_id'])->toArray();
            if(!$memberInfo){
                return CatchResponse::fail("会员不存在");
            }

            //审核成功
            if ($data['status'] == 1) {
                $data['action_money'] = $chargeInfo['amount'];
                $data['charge_type'] = ($chargeInfo['type'] ?: 1) + 3;
                $data['member_charge_id'] = $id;
                $data['member_id'] = $chargeInfo['member_id'];
                $walletLogModel = new WalletLogModel();
                $res = $walletLogModel->addCharge($data);
                if(!$res){
                    return CatchResponse::fail('审核充值失败');
                }
            }
            $chargeData = [
                'remark'        => $data['remark'],
                'status'        => $data['status'],
                'creator_id'    => $data['creator_id']
            ];
            $this->memberChargeModel->updateBy($id, $chargeData);

        } catch (\Exception $e) {
            return CatchResponse::fail($e->getMessage());
        }
        return CatchResponse::success([], '审核成功');
    }

    /**
     * 修改备注
     * @param Request $request
     * @return CatchResponse
     */
    public function editRemark(Request $request, int $id)
    {
        try {
            $data = $request->filterEmptyField()->post();
            if(empty($data['remark'])){
                return CatchResponse::fail("备注内容不能为空");
            }

            $chargeInfo = $this->memberChargeModel->findBy($id);
            if(!$chargeInfo){
                return CatchResponse::fail("充值记录不存在");
            }
            $chargeData = [
                'remark'        =>$data['remark'],
                'creator_id'    => $data['creator_id']
            ];
            $this->memberChargeModel->updateBy($id, $chargeData);

        } catch (\Exception $e) {
            return CatchResponse::fail($e->getMessage());
        }
        return CatchResponse::success([], '修改成功');
    }
}
