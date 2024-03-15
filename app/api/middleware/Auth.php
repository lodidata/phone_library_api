<?php

namespace app\api\middleware;


use app\common\model\redis\MemberRedis;
use app\common\service\DesEncryptService;

class Auth
{
    public function handle($request, \Closure $next)
    {
        $token = $request->header('Authorization');
        !$token && exit(ajaxReturn('token不能为空',[],401));
        try{
            $res = DesEncryptService::verifyToken($token);
        }catch (\Exception $e){
            exit(ajaxReturn($e->getMessage(), [], $e->getCode()));
        }
        $request->member_id = $res['id'];
        return $next($request);

    }
}