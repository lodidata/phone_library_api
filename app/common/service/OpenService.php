<?php


namespace app\common\service;

use app\api\validate\Ucheck as vaUcheck;
use app\common\model\Api;
use app\common\model\ApiLog;
use app\common\model\ApiResult;
use app\common\model\Member;
use app\common\model\MemberIp;
use app\common\model\Product;
use app\common\model\WalletLog;
use app\common\model\redis\UcheckRedis;
use catcher\CatchUpload;
use think\facade\Db;
use think\Request;
use think\facade\Log;

class OpenService extends BaseService
{
    /**
     * @var int $memberId 会员ID
     */
    protected $memberId;
    /**
     * @var int $apiId API接口ID
     */
    protected $apiId;
    /**
     * @var int $apiLogId api_log表id
     */
    protected $apiLogId;
    /**
     * @var int $successNum 查询成功数
     */
    protected $successNum;
    /**
     * @var int $failNum 查询失败数
     */
    protected $failNum;
    /**
     * @var float $money 消费金额
     */
    protected $money;
    /**
     * @var string $apiName api名称
     */
    protected $apiName;
    /**
     * @var string $checkType 文件检测类型 1单点检测 2文件检测
     */
    protected $checkType;
    /**
     * @var string $checkResultFilePath 检测结果文件路径
     */
    protected $checkResultFilePath;

    /**
     * @var int $apiCode api返回code  调用结果 0审核中 1成功 2失败
     */
    protected $apiCode;

    /**
     * 验证用户相关信息
     * 1、用户是否存在
     * 2、用户是否激活状态
     * 3、用户是否有钱
     * 4、用户IP白名单
     * @param $appId
     * @param $appKey
     * @return JSON
     */
    public function checkUserParams($appId, $appKey)
    {
        if(empty($appId)|| empty($appKey)){
            $this->setError(40004);
        }
        $memberService= new MemberService();
        //验证appId,appKey
        $memberId = $memberService->getIdBykey($appId, $appKey);
        if(empty($memberId)){
            $this->setError(40006);
        }
        $memberInfo = $memberService->getUserById($memberId);
        if(!$memberInfo){
            $this->setError(40006);
        }
        //禁用用户 1开启 2禁用
        if($memberInfo['status'] == 2){
            $this->setError(40010);
        }

        //无余额
        if(bccomp($memberInfo['wallet'], 0 ,4) <= 0){
            $this->setError(40007);
        }
        //IP白名单
        $ip = getIp();
        $ips = $this->getIp($memberId);
        //测试页面带了token 就不限制ip白名单
        if(!\think\facade\Request::middleware('member_id')){
            $res = checkIp($ip, $ips);
            if(!$res){
                $this->setError(40008);
            }
        }

        $this->memberId = $memberId;

    }

    /**
     * API接口调用校验
     * 1、接口是否存在及禁用
     * 2、产品是否存在及禁用
     * 3、请求方式
     * 4、请求协议
     * 5、请求参数
     * @param string $apiId APPID
     * @param \think\Request $params
     * @return mixed|JSON
     */
    public function checkApiParms($apiId, $params)
    {
        //接口禁用
        $apiModel = new Api();
        $apiInfo = $apiModel->findOrEmpty($apiId);
        if(!$apiInfo){
            $this->setError(40101, '接口不存在');
        }
        if($apiInfo['status'] == 2){
            $this->setError(40102, '接口已经停用，请联系客服人员');
        }
        //产品禁用
        $productModel = new Product();
        $productInfo = $productModel->findOrEmpty($apiInfo['product_id']);
        if(!$productInfo){
            $this->setError(40103, '产品已经停用，请联系客服人员');
        }
        //状态 0 未激活  1 已激活 2 停用
        if($productInfo['status'] != 1){
            $this->setError(40103, '产品已经停用，请联系客服人员');
        }
        //请求方式get post
        $method = \request()->method();
        $method_type = $method == 'GET' ? 1 : 2;
        if($method_type != $apiInfo['method_type']){
            $this->setError(40003);
        }
        //请求协议 http https
        $isSsl = \request()->isSsl();
        $http_type = $isSsl ? 2 : 1;
        if($http_type != $apiInfo['http_type']){
            $this->setError(40002);
        }
        //请求参数校验
        $request_param = json_decode($apiInfo['request_param'], true, 512, JSON_BIGINT_AS_STRING);
        if(empty($request_param)){
            $this->setError(50001, '系统异常');
        }
        foreach ($request_param as $param){
            if($param['is_require'] && !in_array($param['param'], array_keys($params))){
                $this->setError(40001);
            }
        }
        //每次调用不超过1000个号码
        $mobiles = explode(',', $params['mobiles']);
        if(!trim($params['mobiles'])){
            $this->setError(10001,'手机号不能为空');
        }
        if(count($mobiles) > $apiInfo['rate_num']){
            $this->setError(40011);
        }
    }

    /**
     * 空号检测
     * @param $params
     * @return array
     */
    public function uCheck($params)
    {
        $this->apiId     = $apiId = 1;

        $this->checkUserParams($params['appId'], $params['appKey']);
        $this->checkApiParms($apiId, $params);

        $result      = [];
        $return_data = [];
        $mobiles = explode(',', $params['mobiles']);

        foreach($mobiles as $k => $v){
            $this->getMobileInfo($v, $result,$return_data);
        }
        $api_log_params = [
            'code' => 1,
            'type' => 0,
            'file_name'     => '',
            'new_file_name' => '',
        ];
        $this->apiLogId = $this->insertApiLog($api_log_params);
        try{
            if($result){
                //插入api_result记录
                $this->insertApiResult($result);
                //计算消费金额
                $this->coutMoney();
                if($this->money){
                    //扣钱
                    $this->updateAmount();
                }else{
                    $this->apiCode = 1;
                }
                //更新api_log数据
                $this->updateApiLog();
            }
        }catch (\Exception $e){
            $this->logError('单个检测错误：'.'id：'.$this->apiLogId . $e->getMessage());
        }
        //处理返回结果
        $chargeStatus = 0; //是否收费
        $chargeCount = 0;  //记费条数
        foreach ($return_data as $k => &$v){
            if($v['toMoney']){
                $chargeStatus = 1;
                $chargeCount++;
            }
            $v['chargesStatus'] = $v['toMoney'];
            //$v['isUse'] = $v['is_use'];
            $v['status'] = (int)$v['type'];
            unset($v['toMoney'], $v['is_use'], $v['type'], $v['typeName']);
        }
        unset($v);

        $response = [
            'code'          => 20000,
            'message'       => '请求成功',
            'data'          => $return_data,
            'chargeStatus'  => $chargeStatus,
            'chargeCount'   => $chargeCount,
        ];

        return $response;
    }

    /**
     * 空号单点检测和文件检测
     * @param $params
     * @return array
     */
    public function newUCheck($params)
    {
        $this->apiId     = $apiId = 1;
        $this->checkType = $params['type'];
        //文件检测
        if($params['type'] == 2){
            $this->checkUserParams($params['appId'], $params['appKey']);
            $res = $this->isChecking();
            if($res){
                $this->setError(0,'正在检测中，请稍后再试');
            }
            return $this->multiCheckFile($params);
        }
        $this->checkUserParams($params['appId'], $params['appKey']);
        $this->checkApiParms($apiId, $params);

        $result = [];
        $mobiles = explode(',', $params['mobiles']);
        //预估金额
        $this->successNum = count($mobiles);
        $this->preCountMoney();

        foreach($mobiles as $k => $v){
            $this->getNewMobileInfo($v, $result);
        }
        $api_log_params = [
            'code' => 1,
            'type' => 1,
            'file_name'     => '',
            'new_file_name' => '',
        ];
        $this->apiLogId = $this->insertApiLog($api_log_params);
        try{
            if($result){
                //插入api_result记录
                $this->insertApiResult($result);
                //计算消费金额
                $this->coutMoney();
                if($this->money){
                    //扣钱
                    $this->updateAmount();
                }else{
                    $this->apiCode = 1;
                }
                //更新api_log数据
                $this->updateApiLog();
            }
        }catch (\Exception $e){
            $this->logError('单个检测错误：'.'id：'.$this->apiLogId . $e->getMessage());
        }

        $response = [
            'code'          => 10000,
            'message'       => '请求成功',
            'data'          => [],
        ];

        return $response;
    }

    /**
     * 预估金额
     */
    protected function preCountMoney(){
        $this->coutMoney();
        $MemberService  = new MemberService();
        $memberInfo     = $MemberService->getUserById($this->memberId);

        if(bccomp($memberInfo['wallet'], $this->money, 4) < 0){
            $this->setError(40007);
        }
    }

    protected function multiCheckFile($params){
        $validate = new vaUcheck;
        $result   = $validate->check($params);
        if(!$result){
            exit(ajaxReturn($validate->getError(),[],10001));
        }

        $this->successNum = 0;
        if(count($params['file_name']) < 2){
            $params['file_name']     = current($params['file_name']);
            $params['new_file_name'] = current($params['new_file_name']);
            $this->uCheckFile($params);
        }else{
            //批量上传
            foreach ($params['file_name'] as $k => $v){
                $new_params = [
                    'file_name'     => $v,
                    'new_file_name' => $params['new_file_name'][$k],
                ];
                $this->uCheckFile($new_params);
            }
        }

        $response = [
            'code'          => 10000,
            'message'       => '请求成功',
            'data'          => [],
        ];

        return $response;
    }

    protected function uCheckFile($params){
        //预估金额
        $this->successNum += $this->countFileLine($params['new_file_name']);
        $this->preCountMoney();

        $api_log_params = [
            'code' => 0,
            'type' => 2,
            'file_name'     => $params['file_name'],
            'new_file_name' => $params['new_file_name'],
        ];
        $this->insertApiLog($api_log_params);
    }

    /**
     * 是否有正在查询的接口
     * @param $params
     * @return bool
     */
    protected function isChecking(){
        $params = [
            'member_id' => $this->memberId,
            'code'      => 0
        ];

        $ApiLog = new ApiLog();
        $num    = $ApiLog->getLog($params);
        return $num ? true : false;
    }

    protected function insertApiLog($params){
        $api_log_data = [
            'api_id'            => $this->apiId,
            'member_id'         => $this->memberId,
            'success_num'       => 0,
            'fail_num'          => 0,
            'money'             => 0,
            'file_name'         => $params['file_name'],
            'new_file_name'     => $params['new_file_name'],
            'code'              => $params['code'],
            'type'              => $params['type'],
            'created_at'        => date('Y-m-d H:i:s', time())
        ];
        $ApiLog      = new ApiLog();

        try{
            $api_log_id  = $ApiLog->insertGetId($api_log_data);
        }catch (\Exception $e){
                exit(ajaxReturn($e->getMessage(),[],10001));
        }
        return $api_log_id;
    }

    protected function insertApiResult($data){
        $ApiResult      = new ApiResult();
        $this->successNum = $this->failNum = 0;

        foreach ($data as $k => $v){
            $count = substr_count($v,'mobile');
            //失败
            if($k == 5){
                $this->failNum    += $count;
            }else{
                $this->successNum += $count;
            }
            //插入检测数据到文件
            $this->insertToFile(trim($v));

            $api_result_data = [
                'api_log_id' => $this->apiLogId,
                'num'        => $count,
                'type'       => $k,
                'result'     => $this->checkResultFilePath,
            ];
            $ApiResult->insert($api_result_data);

        }
    }

    /**
     * 保存文件到数据
     * @param $data
     */
    protected function insertToFile($data){
        $path      = $this->getFileSrc();
        try{
            file_put_contents($path, $data);
        }catch (\Exception $e){
            $this->logError('保存检测数据到文件错误：'.$e->getMessage());
        }

    }

    /**
     * 获取文件路径
     * @return string
     * @throws \Exception
     */
    protected function getFileSrc(){
        $root_path = \config('filesystem.disks.local.root');
        $path      = DIRECTORY_SEPARATOR.'checkFile'.$this->checkType.DIRECTORY_SEPARATOR.$this->memberId.DIRECTORY_SEPARATOR.date('Ymd').DIRECTORY_SEPARATOR;
        $file_name = md5(random_int(100000,999999)).'.txt';
        $this->checkResultFilePath      =  $path . $file_name;
        $path      = $root_path.$path;
        if(!is_dir($path)){
            mkdir($path,0777,true);
        }

        return $root_path . $this->checkResultFilePath;
    }

    protected function updateApiLog(){
        $api_log_data = [
            'success_num'       => $this->successNum,
            'fail_num'          => $this->failNum,
            'money'             => $this->money,
            'code'              => $this->apiCode,
        ];
        $ApiLog      = new ApiLog();
        $ApiLog->update($api_log_data,['id' => $this->apiLogId]);

    }

    /**
     * IP白名单
     * @param $member_id
     * @return mixed
     */
    public function getIp($member_id){

        $MemberIp = new MemberIp();
        return $MemberIp->where(['member_id'=>$member_id])->value('ips');
    }

    /**
     * 接口返回信息
     * @param int $code 错误码
     * @param string $message 错误描述
     * @param array $data 返回内容
     * @return array
     */
    public function setError($code, $message = '', $data = [])
    {
        $codeService = new CodeService();
        $codeArray = $codeService->getAllCode();
        $message = isset($codeArray[$code]) ? $codeArray[$code] : $message;
        Log::error('apiId：'.$this->apiId.'，参数：'.json_encode(\request()->param()) . ' 结果：code:' . $code . ' message：' . $message);
        Log::save();
        exit(ajaxReturn($message, $data, $code));
    }

    /**
     * 计算消费金额
     * @param $apiId
     * @param $num
     * @return int|string
     */
    public function coutMoney(){
        $this->money = 0;
        if($this->successNum){
            //查询api接口
            $apiModel      = new Api();
            $apiInfo       = $apiModel->findOrEmpty($this->apiId);
            $this->apiName = $apiInfo['name'];
            $this->money   = $apiInfo['price'];
            //收费类型 1 按条 2按次
            if($apiInfo['charge_type'] == 1){
                $this->money = bcmul($apiInfo['price'] , $this->successNum, 4);//总价格
            }
        }
    }

    /**
     * 钱包消费记录
     * 钱包扣除消费金额
     * @throws \Exception
     */
    protected function updateAmount(){
        $MemberService  = new MemberService();
        $memberInfo     = $MemberService->getUserById($this->memberId);

        if(bccomp($memberInfo['wallet'], $this->money, 4) < 0){
            $this->apiCode = 2;
            $this->logError('api_log：'.'id：'.$this->apiLogId . ' 余额不足以消费，请充值');
            return;
        }

        //钱包日志数据
        $wallet_log_data = [
            'api_log_id'        => $this->apiLogId,
            'api_id'            => $this->apiId,
            'member_id'         => $this->memberId,
            'before_money'      => $memberInfo['wallet'],
            'action_money'      => $this->money,
            'after_money'       => bcsub($memberInfo['wallet'], $this->money, 4),
            'remark'            => '使用('.$this->apiName.')扣除费用',
            'created_at'        => time(),
            'member_charge_id'  => 0,
            'charge_type'       => 3
        ];
        $WalletLog = new WalletLog();
        $WalletLog->insert($wallet_log_data);
        $membermodel = new Member();
        $membermodel->where(['id'=> $this->memberId])->dec('wallet', $this->money)->update();
        $this->apiCode = 1;
    }

    /**
     * 文件  空号检测
     */
    public function uCheckFileCommand(){
        $ApiLog      = new ApiLog();
        $UcheckRedis = new UcheckRedis();
        $list = $ApiLog->getUcheckList();

        if($list){
            //正在检测中
            if($UcheckRedis->getUcheck()){
                $this->logError('空号检测正在检测中');
                return;
            }

            try{
                //设置redis锁
                $UcheckRedis->setUcheck();
            }catch (\Exception $e){
                $this->logError('空号检测' . $e->getMessage());
                return;
            }
            foreach ($list as $v){
                $this->memberId = $v['member_id'];
                $this->apiLogId = $v['id'];
                $this->apiId    = $v['api_id'];

                try{
                    //$start_mem =  round(memory_get_usage()/1024/1024, 2);
                    $result = $this->handleData($v['new_file_name']);
                    if($result){
                        //插入api_result记录
                        $this->insertApiResult($result);
                        //计算消费金额
                        $this->coutMoney();
                        if($this->money){
                            //扣钱
                            $this->updateAmount();
                        }else{
                            $this->apiCode = 1;
                        }
                        //更新api_log数据
                        $this->updateApiLog();

                    }
                    //$end_mem = round(memory_get_usage()/1024/1024, 2);
                    //$mem_num = $end_mem-$start_mem;
                    //$this->logError('空号检测消耗内存：'.$mem_num.'M');
                }catch (\Exception $e){
                    $this->logError('api_log错误：'.'id：'.$this->apiLogId . $e->getMessage());
                    return;
                }

            }
            //删除redis锁
            $UcheckRedis->delUcheck();
        }

    }

    /**
     * 记录错误日志
     * @param $msg
     */
    function logError($msg){
        Log::error($msg);
        Log::save();
    }

    /**
     * 获取手机号信息
     * @param $mobile
     * @param $result
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function getMobileInfo($mobile, &$result, &$return_data)
    {
        //过虑非手机号
        if(!preg_match("/^[1-9]\d{0,19}$/", $mobile)){
            $data = [
                'mobile'  => $mobile,
                'is_use'  =>0,
            ];
            !isset($result[5]) && $result[5] = '';
            $result[5]         .= ','.json_encode($data);
            return;
        }

        $configName = config('app.mobileName');

        //暂时配置一下号码表名
        if(empty($configName)){
            $name = getPhoneServiceByid($mobile,100);
        }else{
            $name = $configName;
        }

        $tablesName = 'wl_pnl_mobile_'.$name;
        $isExits    = existTable($tablesName);
        $info = [];
        if($isExits){
            $info = Db::table($tablesName)->where(['mobile'=>$mobile])->field('mobile,concat(province,"-",city) as area,type,is_use,use_last_time as lastTime')->find();
        }
        //手机号码不存在数据库（如果有手机号码不存在手机号码库请开启--主要是这边不知道你们号码库是不是存在所有手机号码，根据我们需求直接返回异常）
        //用来返回
        if(empty($info)){
            $return_data[] = [
                'mobile'  => $mobile,
                'type'    =>5,//0：未验证，1：实号，2：沉默号，3：危险号，4：空号，5：库无号
                'is_use'  =>0,
                'toMoney' =>0//不需要计入收费
            ];
        }else{
            $return_data[] = array_merge($info,['toMoney' => 1]);
        }

        if(empty($info)){
            $data = [
                'mobile'  => $mobile,
                'is_use'  =>0,
            ];
            !isset($result[5]) && $result[5] = '';
            $result[5]         .= json_encode($data)."\n";
        }else{
            $type = $info['type'];
            unset($info['type']);
            unset($info['area']);
            unset($info['lastTime']);
            !isset($result[$type]) && $result[$type] = '';
            $result[$type]         .= json_encode($info)."\n";
        }

    }

    /**
     * 单点检测 文件检测
     * 获取手机号信息
     * @param $mobile
     * @param $result
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function getNewMobileInfo($mobile, &$result)
    {
        //过虑非手机号
        if(!preg_match("/^[1-9]\d{0,19}$/", $mobile)){
            $data = [
                'mobile'  => $mobile,
                'is_use'  =>0,
            ];
            !isset($result[5]) && $result[5] = '';
            $result[5]         .= ','.json_encode($data);
            return;
        }

        $configName = config('app.mobileName');

        //暂时配置一下号码表名
        if(empty($configName)){
            $name = getPhoneServiceByid($mobile,100);
        }else{
            $name = $configName;
        }

        $tablesName = 'wl_pnl_mobile_'.$name;
        $isExits    = existTable($tablesName);
        $info = [];
        if($isExits){
            //'mobile,concat(province,"-",city) as area,type,is_use,use_last_time as lastTime'
            $info = Db::table($tablesName)->where(['mobile'=>$mobile])->field('mobile,type,is_use')->find();
        }
        //手机号码不存在数据库（如果有手机号码不存在手机号码库请开启--主要是这边不知道你们号码库是不是存在所有手机号码，根据我们需求直接返回异常）
        if(empty($info)){
            $data = [
                'mobile'  => $mobile,
                'is_use'  =>0,
            ];
            !isset($result[5]) && $result[5] = '';
            $result[5]         .= json_encode($data)."\n";
        }else{
            $type = $info['type'];
            unset($info['type']);
            !isset($result[$type]) && $result[$type] = '';
            $result[$type]         .= json_encode($info)."\n";
        }

    }


    public function handleData($path){
        $path = config('filesystem.disks.local.root').$path;
        $glob = readOneFile($path);
        $result=[];

        while ($glob->valid()) {
            // 当前行文本
            $mobile = trim($glob->current());
            //排除空行
            $mobile && $this->getNewMobileInfo($mobile, $result);

            // 指向下一个，不能少
            $glob->next();
        }

        return $result;
    }


    /**
     * @param $path
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function countFileLine($path){
        $path = config('filesystem.disks.local.root').$path;
        $glob = readOneFile($path);
        $line = 0;
        while ($glob->valid()) {
            // 当前行文本
            $line++;
            // 指向下一个，不能少
            $glob->next();
        }
        return $line;
    }



}


















