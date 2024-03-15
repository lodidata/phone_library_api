<?php

namespace catchAdmin\product\controller;

use app\common\service\MemberService;
use app\common\service\Safety;
use catcher\base\CatchRequest as Request;
use catcher\CatchResponse;
use catcher\base\CatchController;
use catchAdmin\product\model\Member as MemberModel;
use catcher\Utils;
use app\api\validate\Register as vaRegister;
use catchAdmin\product\model\WalletLog as WalletLogModel;


class Member extends CatchController
{
    protected $memberModel;

    public function __construct(MemberModel $memberModel)
    {
        $this->memberModel = $memberModel;
    }

    /**
     * 列表
     * @time 2021年04月28日 10:30
     * @param Request $request
     * @return \think\response\Json
     */
    public function index(Request $request)
    {
        return CatchResponse::paginate($this->memberModel->getList());
    }

    /**
     * 保存信息
     * @time 2021年04月28日 10:30
     * @param Request $request
     * @return \think\response\Json
     */
    public function save(Request $request)
    {
        try {
            $data = $request->post();
            unset($data['creator_id']);
            $service = new MemberService();
            $ret = $service->register($data);
            if (isset($ret['code']) && $ret['code'] == 10001) {
                return CatchResponse::fail('添加失败');
            }
        } catch (\Exception $e) {
            return CatchResponse::fail('添加失败');
        }
        return CatchResponse::success([], '添加成功');
    }

    /**
     * 读取
     * @time 2021年04月28日 10:30
     * @param $id
     * @return \think\response\Json
     */
    public function read($id)
    {
        return CatchResponse::success($this->memberModel->withoutField('token,password')->find($id));
    }

    /**
     * 删除
     * @time 2021年04月28日 10:30
     * @param $id
     * @return \think\response\Json
     */
    public function delete($id)
    {
        $this->memberModel->updateBy($id, ['status' => 2, 'deleted_at' => time()]);
        return CatchResponse::success([], '删除成功');
    }

    /**
     *
     * @time 2019年12月07日
     * @param $id
     * @return \think\response\Json
     */
    public function switchStatus($id)
    {
        $ids = Utils::stringToArrayBy($id);

        foreach ($ids as $_id) {

            $member = $this->memberModel->findBy($_id);

            $this->memberModel->updateBy($_id, [
                'status' => $member->status == $this->memberModel::ENABLE ? $this->memberModel::DISABLE : $this->memberModel::ENABLE,
            ]);
        }

        return CatchResponse::success([], '操作成功');
    }

    /**
     * 修改余额
     * @param $id
     * @return \think\response\Json
     */
    public function editWallet(Request $request, $id)
    {
        try{
            $data = $request->filterEmptyField()->post();
            if( !checkMoney($data['action_money'],4) || bccomp($data['action_money'], 0, 4)<=0 ){
                return CatchResponse::fail("请输入正确的金额");
            }
            if(!isset($data['charge_type']) || $data['charge_type'] == 0 ){
                return CatchResponse::fail("未选择修改余额存款或者扣款");
            }

            if(! in_array($data['charge_type'], [WalletLogModel::CHARGE_TYPE_ADD,WalletLogModel::CHARGE_TYPE_SUB])){
                return CatchResponse::fail("非法操作");
            }

            $memberInfo = $this->memberModel->field('id,wallet')->find($id)->toArray();
            if(!$memberInfo){
                return CatchResponse::fail("变更的会员不存在");
            }
            if($data['charge_type'] == WalletLogModel::CHARGE_TYPE_SUB && bccomp($data['action_money'], $memberInfo['wallet'], 4) > 0){
                return CatchResponse::fail("会员余额不足扣款");
            }

            $data['member_id'] = $id;
            $walletLogModel = new WalletLogModel();
            $walletLogModel->addCharge($data);
        } catch (\Exception $e){
            return CatchResponse::fail($e->getMessage());
        }
        return CatchResponse::success([], '修改成功');
    }

    /**
     * 重置密码
     * @param Request $request
     * @param $id
     * @return \think\response\Json
     */
    public function editpwd(Request $request, $id)
    {
        try {
            $params = $request->post();
            $validate = new vaRegister;
            $result = $validate->scene('editpwd')->check($params);
            if (!$result) {
                return CatchResponse::fail($validate->getError());
            }

            $data['password'] = Safety::mPassword($params['password']);
            $this->memberModel->updateBy($id, $data);
        } catch (\Exception $e) {
            return CatchResponse::fail('修改失败');
        }

        return CatchResponse::success([], '修改成功');
    }

    /**
     * 修改名称
     * @param $id
     * @return \think\response\Json
     */
    public function editname(Request $request, $id): \think\response\Json
    {
        try {
            $params = $request->filterEmptyField()->post();
            if (!(isset($params['user_name']) || isset($params['user_contact']))) {
                return CatchResponse::fail('不能都为空');
            }
            if (isset($params['user_name'])) {
                $data['user_name'] = $params['user_name'];
            }
            if (isset($params['user_contact'])) {
                $data['user_contact'] = $params['user_contact'];
            }
            $this->memberModel->updateBy($id, $data);
        } catch (\Exception $e) {
            return CatchResponse::fail('修改失败');
        }

        return CatchResponse::success([], '修改成功');
    }
}
