<?php


namespace app\api\controller;

use catcher\CatchUpload;
use app\api\validate\FinanceDayCount as vaFinanceDayCount;
use app\api\validate\FinanceMonthCount as vaFinanceMonthCount;
use app\api\validate\Order as vaOrder;
use app\api\validate\Deposit as vaDeposit;
use app\common\service\ApiLogService;
use app\common\service\BankService;
use app\common\service\ConfigService;
use app\common\service\FinanceService;
use app\common\service\WalletLogService;
use app\common\model\Bank as BankModel;

class Finance extends Base
{
    /*
     * 日消耗统计
     */
    function dayLog(){
        $validate      = new vaFinanceDayCount();
        $result        = $validate->check($this->request);
        !$result       && exit(ajaxReturn($validate->getError(), [], 10001));

        $start         = $this->request['start'];
        $end           = $this->request['end'];
        $member_id     = $this->member['id'];

        $ApiLogService = new ApiLogService();
        $list          = $ApiLogService->getApiLogDay($member_id, $start, $end);

        exit(ajaxReturn('success', $list));
    }

    /*
     * 月消耗统计
     */
    function monthLog(){
        $validate      = new vaFinanceMonthCount();
        $result        = $validate->check($this->request);
        !$result       && exit(ajaxReturn($validate->getError(), [], 10001));

        $start         = $this->request['start'];
        $end           = $this->request['end'];
        $member_id     = $this->member['id'];

        $ApiLogService = new ApiLogService();
        $list          = $ApiLogService->getApiLogMonth($member_id, $start, $end);

        exit(ajaxReturn('success', $list));
    }

    /**
     * 充值 银行卡列表
     */
    function bankCardList(){
        $bank_service = New BankService();
        $list         = $bank_service->getList();

        exit(ajaxReturn('success', $list));
    }

    /**
     * 获取财务
     */
    function getContact(){
        $config_service = new ConfigService();
        $info           = $config_service->getContact('contact',['finance']);

        exit(ajaxReturn('success',$info));
    }

    /**
     * 充值
     */
    function deposit(){
        $validate  = new vaDeposit();
        $result    = $validate->check($this->request);
        !$result   && exit(ajaxReturn($validate->getError(), [], 10001));
        $bank_id   = (int)$this->request['bank_id'];
        $bankcard  = (new BankModel())->getInfo($bank_id);
        if(!$bankcard){
            exit(ajaxReturn('bank_id错误',[],10001));
        }
        $params  = [
                    'member_id'   => $this->member['id'],
                    'amount'      => $this->request['money'],
                    'bank_id'     => $bank_id,
                    'images'      => $this->request['image'],
                    'status'      => 0,
                    'created_at'  => time(),
        ];
        $params['images'] = str_replace(CatchUpload::getCloudDomain('local'),'',$params['images']);
        $finance_service  = new FinanceService();
        $res              = $finance_service->deposit($params);
        if($res){
            exit(ajaxReturn('success'));
        }else{
            exit(ajaxReturn('操作失败',[],10001));
        }

    }

    /**
     * 充值订单列表
     */
    function orderList(){
        $validate  = new vaOrder();
        $result    = $validate->check($this->request);
        !$result   && exit(ajaxReturn($validate->getError(), [], 10001));
        $params = [
            'member_id'  => $this->member['id'],
            'order_no'   => request()->param('order_no'),
            'start'      => request()->param('start'),
            'end'        => request()->param('end'),
            'order'      => request()->param('order'),
            'order_type' => request()->param('order_type'),
        ];

        $finance_service = new FinanceService();
        $res             = $finance_service->orderList($params);
        exit(ajaxReturn('success', $res));
    }

    /**
     * 收支明细
     */
    function countList(){
        $validate           = new vaOrder();
        $result             = $validate->check($this->request);
        !$result            && exit(ajaxReturn($validate->getError(), [], 10001));
        $start              = request()->param('start');
        $end                = request()->param('end');
        $member_id          = $this->member['id'];
        $wallet_log_service = new WalletLogService();
        $list               = $wallet_log_service->countList($member_id, $start, $end);
        if($list['data']){
            foreach ($list['data'] as &$v){
                $v['date']   = $v['created_day'];
                $v['in']     = floatNumber($v['add_sums']);
                $v['out']    = floatNumber($v['sub_sums']);
                $v['amount'] = floatNumber($v['last_money']);
                unset($v['created_day'], $v['add_sums'], $v['sub_sums'], $v['last_money']);
            }
            unset($v);
        }
        exit(ajaxReturn('success', $list));
    }

}