<?php


namespace catchAdmin\product\controller;

use app\common\service\ApiService;
use app\common\service\IndexService;
use catcher\base\CatchRequest as Request;
use catcher\CatchResponse;
use catcher\base\CatchController;

class Dashboard extends CatchController
{

    /**
     * 首页数据统计
     * @return \think\response\Json
     */
    public function countData()
    {
        $indexService = new IndexService();
        $info = $indexService->getApiUsage();
        return CatchResponse::success($info);
    }

    /**
     * 统计图表
     * @param Request $request
     */
    public function chartData(Request $request)
    {
        $type = $request->param('type');
        //上个月否则本月
        if($type == 'lastMonth'){
            $month = date('Y-m', strtotime(date('Y-m-01') . " - 1 month"));
        }else{
            $month = date('Y-m');
        }
        $startTime = getStartDate($month.'-01');
        $endTime = getEndDate($month.'-01', 1);
        $apiService = new ApiService();
        $list = $apiService->getApiLogDay(0, 0, $startTime, $endTime);
        exit(ajaxReturn('success',$list));
    }
}