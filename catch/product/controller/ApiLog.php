<?php

namespace catchAdmin\product\controller;

use app\api\validate\DayCount as vaDayCount;
use app\api\validate\MonthCount as vaMonthCount;
use catcher\base\CatchRequest as Request;
use catcher\CatchResponse;
use catcher\base\CatchController;
use app\common\service\ApiService;
use catchAdmin\product\model\Member as MemberModel;
use think\exception\ValidateException;

class ApiLog extends CatchController
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * apiLog列表
     * @param CatchRequest $request
     * @return JSON
     */
    public function index(Request $request) : \think\Response\Json
    {
        $return = $this->SearchParams($request);
        if(!$return['status']){
            return CatchResponse::fail($return['message']);
        }
        $list = $this->apiService->getApiLog(
            $return['data']['productId'],
            $return['data']['memberId'],
            $return['data']['startTime'],
            $return['data']['endTime'],
            $return['data']['apiId']
        );
        exit(ajaxReturn('success',$list));
    }

    /**
     * 日消耗统计
     * @param CatchRequest $request
     */
    public function dayLog(Request $request) : \think\Response\Json
    {
        $return = $this->SearchParams($request);
        if(!$return['status']){
            return CatchResponse::fail($return['message']);
        }
        $list = $this->apiService->getApiLogDay(
            $return['data']['productId'],
            $return['data']['memberId'],
            $return['data']['startTime'],
            $return['data']['endTime']
        );
        exit(ajaxReturn('success',$list));
    }

    /**
     * 月消耗统计
     * @param CatchRequest $request
     */
    public function monthLog(Request $request) : \think\Response\Json
    {
        $return = $this->SearchParams($request);
        if(!$return['status']){
            return CatchResponse::fail($return['message']);
        }
        $list = $this->apiService->getApiLogMonth(
            $return['data']['productId'],
            $return['data']['memberId'],
            $return['data']['startTime'],
            $return['data']['endTime']
        );
        exit(ajaxReturn('success',$list));
    }

    /**
     * 统计搜索参数
     * @param CatchRequest $request
     * @return array
     */
    private function SearchParams($request)
    {
        $return = [
            'status' => true,
            'message' => '',
            'data' => []
        ];
        $params = $request->filterEmptyField()->param();
        if(!isset($params['start_time'])){
            $params['start_time'] = date('Y-m-d', strtotime('-10 day'));
            $params['end_time'] = date('Y-m-d');
        }
        try {
            $validateRule = [
                'product_id|产品ID'  => 'require|number',
                'start_time|开始时间' => 'require|date',
                'end_time|开始时间' => 'require|date',
            ];
            validate($validateRule)->check($params);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            return ['status' => false, 'message' => $e->getError()];
        }

        $productId = isset($params['product_id']) ? intval($params['product_id']) : 0;
        if(!$productId){
            return ['status' => false, 'message' => '请选择产品'];
        }
        //账号 精准查
        $memberId = 0;
        if(isset($params['user_account'])){
            $memberModel = new MemberModel();
            $memberId = $memberModel->where('user_account', $params['user_account'])->value('id');
            if(is_null($memberId)){
                return $return;
            }
        }
        $return['data'] = [
            'productId' => $productId,
            'memberId' => $memberId,
            'startTime' => $params['start_time'],
            'endTime' => $params['end_time'],
            'apiId' => isset($params['api_id']) && $params['api_id']? $params['api_id'] : 0
        ];
        return $return;
    }
}
