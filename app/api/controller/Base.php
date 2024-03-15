<?php

namespace app\api\controller;

use app\BaseController;
use app\common\service\ApiCountService;
use app\common\service\OpenService;
use think\facade\Cache;
use app\common\service\MemberService;
use think\facade\Request;
class Base extends BaseController
{
    protected $member;
    protected $request;

    //api接口
    protected $filter = [
          'open/ucheck'//空号检测
    ];
    public function __construct()
    {
        $this->request       = Request::param();
        $this->member['id']  = Request::middleware('member_id');
    }

}

















