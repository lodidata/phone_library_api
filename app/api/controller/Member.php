<?php


namespace app\api\controller;
use app\api\validate\Login as vaLogin;
use app\common\model\MemberCharge;
use app\common\service\ApiLogService;
use app\common\service\MemberService;
use app\Request;

class Member extends Base
{

    /*
     * 获取用户基本信息
     */
    function index()
    {
        $id             = $this->member['id'];
        $memberService  = new MemberService();
        $info           = $memberService->getUserById($id);
        $info['wallet'] = floatNumber($info['wallet']);
        exit(ajaxReturn('', $info));
    }

    /**
     * 获取appid+appkey
     */
    function getKey()
    {
        $id = $this->member['id'];
        $memberService = new MemberService();
        $info = $memberService->getKeyById($id);
        exit(ajaxReturn('', $info));
    }

    /**
     * 修改ip白名单
     */
    function UpdateIp()
    {
        $id   = $this->member['id'];
        $ips  = trim($this->request['ips']);
        !$ips && exit(ajaxReturn('ips不能为空',[],10001));

        $memberService = new MemberService();
        $info          = $memberService->UpdateIp($id, $ips);

        if ($info) {
            exit(ajaxReturn('success', $info));
        } else {
            exit(ajaxReturn('fail', $info, 10001));
        }
    }

    /**
     * 获取ip白名单
     */
    function getIp()
    {
        $id = $this->member['id'];
        $memberService = new MemberService();
        $info = $memberService->getIp($id);
        exit(ajaxReturn('', $info));
    }

    /**
     * 消耗统计
     */
    public function apiLog(Request  $request){
        $service = new ApiLogService();
        $data = $service->getLogList($this->member['id']);
        exit(ajaxReturn('', $data));
    }

    /**
     * 充值记录
     */
    public function rechargeLog(){
        $model = new MemberCharge();
        $data = $model->getLog($this->member['id']);
        exit(ajaxReturn('', $data));
    }

}
