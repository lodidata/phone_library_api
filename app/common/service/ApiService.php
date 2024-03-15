<?php


namespace app\common\service;
use app\common\model\Api;
use app\common\model\ApiLog;

class ApiService extends BaseService

{
    public function __construct() {
        //数据操作类
        $this->datamodel = new Api();
        //主键
        $this->pk = $this->datamodel->getPk();
    }

    /*
     * 接口列表
     */
    function apiList($productId){
        $where[] = ['status','=',1];
        $where[] = ['product_id','=',$productId];
        $field   = 'id,name,price,url,status';
        $list    = $this->datamodel->getInfoById($where,$field)->toArray();
        return $list;
    }

    function apiDetail($api_id){
        $where[] = ['id','=',$api_id];
        $field = 'id,http_type,url,price,method_type,name,request_param,response_param,response_success,response_failed,demo_example';
        $info = $this->datamodel->getInfoByIdFind($where,$field)->toArray();
        $info['http_type'] =  $info['http_type'] == 1?'http':'https';
        $info['method_type'] =  $info['method_type'] == 1?'get':'post';
        $info['request_param'] =  !empty($info['request_param'])?json_decode($info['request_param'],true):null;
        $info['response_param'] =  !empty($info['response_param'])?json_decode($info['response_param'],true):null;
        $info['demo_example'] =  !empty($info['demo_example'])?json_decode($info['demo_example'],true):null;
        return $info;
    }

    /**
     * 调用记录
     * @param int $productId 产品ID
     * @param int $memberId 会员ID
     * @param datetime $start 开始时间
     * @param datetime $end 结束时间
     * @param int $apiId 接口ID号
     * @return array|float[]|int[]
     */
    function getApiLog($productId, $memberId, $start, $end, $apiId = 0)
    {
        $ApiLog  = new ApiLog();
        $Api     = new Api();
        if($apiId){
            $api_ids = [$apiId];
        }else{
            $api_ids = $Api->getApiId($productId);
        }
        //无接口
        if(empty($api_ids)){
            return $api_ids;
        }
        $where = [
            ['api_id','in',$api_ids],
        ];
        //所有用户数据
        if($memberId){
            $where[] = ['member_id','=',$memberId];
        }

        $where[] = ['created_at','between',[$start, $end]];
        $field   = 'api_id, success_num, code, money, created_at';
        $info    = $ApiLog->getLists($where, $field)->toArray();

        if(empty($info['data'])){
            return $info;
        }
        $total_money = 0;
        $total_num   = 0;
        foreach ($info['data'] as $k=> &$v){
            $apiInfo           = $Api->getInfoByIdFind(['id'=>$v['api_id']],'name');
            $v['api_name']     = $apiInfo['name'];
            $v['money']        = floatval($v['money']);
            $total_money       += $v['money'];
            $total_num         += $v['success_num'];
            unset($v['api_id']);
            //$v['result'] = json_decode($v['result'],true);
        }
        unset($v);
        $info = array_merge(['total_money' => floatNumber($total_money),'total_num' => $total_num], $info);
        return $info;
    }

    function getMobileCheckLog($memberId){
        $where = [
            ['api_id', '=', 1],
            ['deleted_at', '=', 0],
            ['member_id', '=', $memberId],
            ['type', '<>', 0],
            ['created_at', '>=', date('Y-m-d 0:0:0',strtotime('-6 days'))]
        ];

        $ApiLog  = new ApiLog();
        //结果类型(0：未验证，1：实号real_num，2：沉默号silent_num，3：危险号risk_num，4：空号empty_num，5：库无号ku_num)
        $field   = 'al.created_at date,al.id,al.file_name,al.success_num + al.fail_num as total_num, al.success_num, 
                    max(case when ar.type=0 then ar.num else 0 end) as unverified_num,
                    max(case when ar.type=1 then ar.num else 0 end) as real_num,
                    max(case when ar.type=2 then ar.num else 0 end) as silent_num,
                    max(case when ar.type=3 then ar.num else 0 end) as risk_num,
                    max(case when ar.type=4 then ar.num else 0 end) as empty_num,
                    max(case when ar.type=5 then ar.num else 0 end) as ku_num,
                    al.money,al.code';
        $info    = $ApiLog->getUheckLists($where, $field);
        return $info;
    }

    /*
     * 日消耗统计（产品）
     */
    function getApiLogDay($productId, $memberId, $start, $end, $export = 0)
    {
        $ApiLog  = new ApiLog();
        $Api     = new Api();
        //查全部产品
        if($productId){
            $api_ids = $Api->getApiId($productId);
            //无接口
            if(empty($api_ids)){
                return $api_ids;
            }
            $where = [
                ['api_id','in',$api_ids],
            ];
        }else{
            $where = [];
        }
        //所有用户数据
        if($memberId){
            $where[] = ['member_id','=',$memberId];
        }

        if(!empty($start) || !empty($end)){
            $start && $start = getStartDate($start);
            $end   && $end   = getEndDate($end);
            $where[]         = ['created_at','between',[$start,$end]];
        }

        //导出
        if($export){
            $info         = $ApiLog->exportListDay($where);
            $info['data'] = $info;
        }else{
            $info         = $ApiLog->getListDay($where)->toArray();
        }

        if(empty($info['data'])){
            return $info;
        }

        $result_params = [
            'data'      => $info['data'],
            'export'    => $export,
            'need_days' => 0,
        ];
        //所有用户数据
        if($memberId){
            $result_params['member_id'] = $memberId;
        }
        $result       = $this->getResult($result_params);
        $info['data'] = $result;
        return $info;
    }

    /*
     * 月消耗统计（产品）
     */
    function getApiLogMonth($productId, $memberId, $start, $end, $export = 0){
        $ApiLog  = new ApiLog();
        $Api     = new Api();
        //查全部产品
        if($productId){
            $api_ids = $Api->getApiId($productId);
            //无接口
            if(empty($api_ids)){
                return $api_ids;
            }
            $where = [
                ['api_id','in',$api_ids],
            ];
        }else{
            $where = [];
        }
        //所有用户数据
        if($memberId){
            $where[] = ['member_id','=',$memberId];
        }
        if(!empty($start) || !empty($end)){
            $start && $start = getStartDate($start);
            $end   && $end   = getEndDate($end, 1);
            $where[]         = ['created_at','between',[$start,$end]];
        }
        //导出
        if($export){
            $info           = $ApiLog->exportListMonths($where);
            $info['data']   = $info;
        }else{
            $info           = $ApiLog->getListMonths($where)->toArray();
        }

        if(empty($info['data'])){
            return $info;
        }
        $result_params = [
            'data'      => $info['data'],
            'export'    => $export,
            'need_days' => 1,
        ];
        //所有用户数据
        if($memberId){
            $result_params['member_id'] = $memberId;
        }

        $result       = $this->getResult($result_params);
        $info['data'] = $result;
        return $info;
    }

    function getResult($params){
        $ApiLog  = new ApiLog();
        $result  = [];
        foreach ($params['data'] as $k=>&$v){
            $success_num = $fail_num = $money_num =0;
            $start_date  = getStartDate($v['date']);
            $end_date    = getEndDate($v['date'], $params['need_days']);

            $logData = [
                ['al.created_at','between',[$start_date,$end_date]],
            ];
            if(isset($params['member_id'])){
                $logData[] = ['al.member_id','=',$params['member_id']];
            }
            $r           = $ApiLog->getDatas($logData);

            $unverified_num = 0;//未验证号
            $real_num    = 0;//实号
            $empty_num   = 0;//空号
            $risk_num    = 0;//风险
            $silent_num  = 0;//沉默
            $ku_num      = 0;//库无数

            foreach ($r as $kk=>&$vv){
                $success_num    += $vv['success_num'];
                $fail_num       += $vv['fail_num'];
                $money_num      += $vv['money'];
                $unverified_num += $vv['unverified_num'];
                $real_num       += $vv['real_num'];
                $empty_num      += $vv['empty_num'];
                $risk_num       += $vv['risk_num'];
                $silent_num     += $vv['silent_num'];
                $ku_num         += $vv['ku_num'];
            }
            unset($vv);

            $result[] = [
                'date'        => !empty($params['export']) ? $v['date']."\t" : $v['date'],//防止日期变英文缩写
                'total_num'   => $success_num + $fail_num,//总数
                'success_num' => $success_num,//成功数
                'unverified_num' => $unverified_num,//未验证号
                'real_num'    => $real_num,//实号数
                'empty_num'   => $empty_num,//空号数
                'risk_num'    => $risk_num,//风险数
                'silent_num'  => $silent_num,//沉默数
                'ku_num'      => $ku_num,//库无数
                'money_num'   => floatNumber($money_num) ,//消费金额
            ];

        }
        return $result;
    }
}















