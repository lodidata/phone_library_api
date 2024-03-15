<?php


namespace app\api\controller;

use think\facade\Request;
use app\common\service\MemberService;
use edward\captcha\facade\CaptchaApi;


class Login
{
    /*
     * 用户登录
     */
    function login()
    {
        $params         = Request::param();
        $memberService = new MemberService();
        $info          = $memberService->getLogin($params);

        exit(ajaxReturn('登录成功', $info));
    }


    function logout()
    {
        $params        = Request::param();
        $params['id']  = Request::middleware('member_id');
        $memberService = new MemberService();
        $info          = $memberService->logout($params);

        exit(ajaxReturn('退出成功', $info));
    }

    /*
    * 用户注册
    */
    function register()
    {
        $param = Request::param();
        $memberService = new MemberService();
        $info = $memberService->register($param);
        exit(ajaxReturn('注册成功', $info));
    }

    function modifyPassword(){
        $params        = Request::param();
        $params['id']  = Request::middleware('member_id');
        $memberService = new MemberService();
        $memberService->modifyPassword($params);
        exit(ajaxReturn('修改成功'));
    }

    /**
     * 图形验证码
     */
    public function captcha()
    {
        $data           = CaptchaApi::create();
        $data['base64'] = str_replace("\r\n", '', $data['base64']);
        //上线时需要去掉code字段
        unset($data['code']);
        exit(ajaxReturn('获取成功', $data));
    }

}