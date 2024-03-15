<?php
namespace catchAdmin\login\controller;

use catchAdmin\permissions\model\Users;
use catchAdmin\login\validate\LoginValidate;
use catcher\base\CatchController;
use catcher\CatchAuth;
use catcher\CatchCacheKeys;
use catcher\CatchResponse;
use catcher\Code;
use catcher\exceptions\LoginFailedException;
use thans\jwt\facade\JWTAuth;
use edward\captcha\facade\CaptchaApi;
use catcher\base\CatchRequest;
use think\facade\Cache;
use think\Request;

class Index extends CatchController
{
    /**
     * 登陆
     *
     * @time 2019年11月28日
     * @param CatchRequest $request
     * @param CatchAuth $auth
     * @return bool|string
     */
    public function login(Request $request, CatchAuth $auth)
    {
        $condition = $request->post();
        $validate = new LoginValidate;
        $res = $validate->check($condition);
        if(!$res){
           return CatchResponse::fail($validate->getError());
        }
        //判断验证码
        $res         = CaptchaApi::check($condition['code'], $condition['key']);
        !$res        && exit(ajaxReturn('验证码错误',[],10001));

        try {
            $token = $auth->attempt($condition);
            $user = $auth->user();

            $this->afterLoginSuccess($user, $token);
            // 登录事件
            $this->loginEvent($user->username);

            //登录权限
            $permissionIds = $user->getPermissionsBy($user->id);
            // 缓存用户权限
            Cache::set(CatchCacheKeys::USER_PERMISSIONS . $user->id, $permissionIds);

            return CatchResponse::success([
                'token' => $token,
            ], '登录成功');
        } catch (\Exception $exception) {
            $this->detailWithLoginFailed($exception, $condition);
            $code = $exception->getCode();
            return CatchResponse::fail($code == Code::USER_FORBIDDEN ?
                '该账户已被禁用，请联系管理员' : '登录失败,请检查账户和密码', Code::LOGIN_FAILED);
        }
    }

    /**
     * 处理登录失败
     *
     * @time 2020年10月26日
     * @param $exception
     * @param $condition
     * @return void
     */
    protected function detailWithLoginFailed($exception, $condition)
    {
        $message = $exception->getMessage();

        if (strpos($message, '|') !== false) {
            $username = explode('|', $message)[1];
        } else {
            $username = $condition['username'];
        }

        $this->loginEvent($username, false);
    }

    /**
     * 用户登录成功后
     *
     * @time 2020年09月09日
     * @param $user
     * @param $token
     * @return void
     */
    protected function afterLoginSuccess($user, $token)
    {
        $user->last_login_ip = request()->ip();
        $user->last_login_time = time();
        if ($user->hasField('remember_token')) {
            $user->remember_token = $token;
        }
        $user->save();
    }

    /**
     * 登录事件
     *
     * @time 2020年09月09日
     * @param $name
     * @param bool $success
     * @return void
     */
    protected function loginEvent($name, $success = true)
    {
        $params['login_name'] = $name;
        $params['success'] = $success ? 1 : 2;
        event('loginLog', $params);
    }


    /**
     * 登出
     *
     * @time 2019年11月28日
     * @return \think\response\Json
     */
    public function logout(): \think\response\Json
    {
        return CatchResponse::success();
    }

    /**
     * refresh token
     *
     * @author JaguarJack
     * @email njphper@gmail.com
     * @time 2020/5/18
     * @return \think\response\Json
     */
    public function refreshToken()
    {
        return CatchResponse::success([
            'token' => JWTAuth::refresh()
        ]);
    }
}
