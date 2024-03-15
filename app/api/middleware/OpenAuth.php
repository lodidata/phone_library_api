<?php

namespace app\api\middleware;


use app\common\model\redis\MemberRedis;
use app\common\service\DesEncryptService;

class OpenAuth
{
    public function handle($request, \Closure $next)
    {
        $token = $request->header('Authorization');
        //页面测试时 带token 不判断ip白名单
        if($token){
            try{
                $res = DesEncryptService::verifyToken($token);
            }catch (\Exception $e){
                exit(ajaxReturn($e->getMessage(), [], $e->getCode()));
            }
            $request->member_id = $res['id'];
        }

        return $next($request);

    }
}