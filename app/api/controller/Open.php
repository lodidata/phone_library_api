<?php


namespace app\api\controller;
use app\common\model\Api;
use app\common\service\MemberService;
use app\common\service\ApiCountService;
use app\common\service\OpenService;



class Open extends Base
{
    protected $openService;

    public function __construct(OpenService $openService)
    {
        $this->openService = $openService;
        parent::__construct();
    }

    /**
     * B端空号检测
     * appId 接口id
     * appKey string
     * mobiles string 检测手机号，多个手机号码用英文半角逗号隔开
     */
    function uCheck()
    {
        $output = $this->openService->uCheck($this->request);
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($output, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 新空号检测 单点，文件检测
     * appId 接口id
     * appKey string
     * mobiles string 检测手机号，多个手机号码用英文半角逗号隔开
     */
    function newUCheck()
    {
        $output = $this->openService->newUCheck($this->request);
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($output, JSON_UNESCAPED_UNICODE));
    }

}



















