<?php


namespace app\api\controller;

use app\common\model\ApiLog;
use app\common\model\ApiResult;
use app\common\service\ApiService;
use app\common\service\CodeService;
use app\common\service\ExportService;
use app\common\service\IndexService;
use app\common\service\MemberService;
use app\common\service\ProductService;
use app\api\validate\Product as vaProduct;
use app\api\validate\Code as vaCode;
use app\api\validate\DayCount as vaDayCount;
use app\api\validate\MonthCount as vaMonthCount;
use app\api\validate\ApiLog as vaApiLog;
use think\facade\Log;

class Product extends Base
{
    /**
     * @var string $exportFilePath 导出文件路径
     */
    protected $exportFilePath;

    /*
     * 获取所有产品列表
     */
    function getProduct()
    {
        $params         = $this->request;
        $productService = new ProductService();
        $list           = $productService->getProductList($params);

        exit(ajaxReturn('success', $list));
    }

    /*
     * 获取指定分类下的产品
     */
    function getProductById()
    {
        $params = $this->request;
        $validate = new vaProduct;
        $result = $validate->check(['id' => $params['id']]);
        if (!$result)
            exit(ajaxReturn($validate->getError(), [], 10001));


        $productService = new ProductService();
        $list = $productService->getProductById($params);
        exit(ajaxReturn('success', $list));
    }

    /*
     * 企业使用产品基本信息
     */
    function getProductDetail()
    {
        $validate = new vaProduct;
        $result   = $validate->check($this->request);
        !$result  && exit(ajaxReturn($validate->getError(), [], 10001));

        $memberService  = new MemberService();
        $productService = new ProductService();
        $info           = $memberService->getInfo($this->member['id']);
        $params = [
            'member_id' => $this->member['id'],
            'id'        => $this->request['id']
        ];
        $count          = $productService->getCountData($params);

        $data = [
                'wallet'  => floatval($info['wallet']),
                'appid'   => $info['appid'],
                'appkey'  => $info['appkey'],
            ];
        $data           = array_merge($data,$count);
        exit(ajaxReturn('success', $data));
    }

    /*
     * 接口列表
     */
    function apiList()
    {
        $validate   = new vaProduct;
        $result     = $validate->check($this->request);
        !$result    && exit(ajaxReturn($validate->getError(), [], 10001));
        $id         = $this->request['id'];

        $apiService = new ApiService();
        $list       = $apiService->apiList($id);
        exit(ajaxReturn('success', $list));
    }

    /*
     * 接口详情
     */
    function apiDetail()
    {
        $id       = $this->request['id'];
        $validate = new vaProduct;
        $result   = $validate->check(['id' => $id]);

        if (!$result){
            exit(ajaxReturn($validate->getError(), [], 10001));
        }
        $memberService  = new MemberService();
        $info           = $memberService->getInfo($this->member['id']);

        $data = [
            'appid'   => $info['appid'],
            'appkey'  => $info['appkey'],
        ];

        $apiService = new ApiService();
        $list       = $apiService->apiDetail($id);
        $list  &&   $list = array_merge($data, $list);
        exit(ajaxReturn('success', $list));
    }



    //获取状态码
    function getCode(){
        $validate    = new vaCode;
        $result      = $validate->check($this->request);
        !$result && exit(ajaxReturn($validate->getError(), [], 10001));
        $page        = $this->request['page'];
        $codeService = new CodeService();
        $list        = $codeService->getLists($page);
        exit(ajaxReturn('success', $list));
    }


    /*
     * api调用记录
     */
    function getApiLog(){
        $validate   = new vaApiLog();
        $result     = $validate->check($this->request);
        !$result    && exit(ajaxReturn($validate->getError(), [], 10001));

        $start      = $this->request['start'];
        $end        = $this->request['end'];
        $id         = $this->request['id'];
        $api_id     = !empty($this->request['api_id']) ? (int)$this->request['api_id'] : 0;
        $member_id  = $this->member['id'];

        $ApiService = new ApiService();
        $list       = $ApiService->getApiLog($id, $member_id, $start, $end, $api_id);

        exit(ajaxReturn('success',$list));
    }

    /*
     * 日消耗统计
     */
    function dayLog(){
        $validate   = new vaDayCount();
        $result     = $validate->check($this->request);
        !$result    && exit(ajaxReturn($validate->getError(), [], 10001));

        $start      = $this->request['start'];
        $end        = $this->request['end'];
        $id         = $this->request['id'];
        $member_id  = $this->member['id'];
        $export     = isset($this->request['export']) ? (int)$this->request['export'] : 0;

        $ApiService = new ApiService();
        $list       = $ApiService->getApiLogDay($id, $member_id, $start, $end, $export);
        //导出数据
        $export && $list && $list = $this->exportDayLog($list['data'], date('YmdHis').'_'.'日消耗统计');

        exit(ajaxReturn('success', $list));
    }

    /**
     * 删除空号检测记录
     * 
     */
    function delCheckLog(){
        $id     = (int)$this->request['id'];
        $apiLog = new ApiLog();
        $where  = [
            ['id','=', $id],
            ['deleted_at','=', 0],
            ['code','<>', 0]
        ];
        $data   = [
            'deleted_at' => time()
        ];
        $res = $apiLog->updateInfo($where,$data);
        exit(ajaxReturn('success', [$res?true:false]));
    }

    /**
     * 空号检测记录
     */
    function mobileCheckLog(){
        $ApiService = new ApiService();
        $list       = $ApiService->getMobileCheckLog($this->member['id']);
        exit(ajaxReturn('success', $list));
    }
    /*
     * 月消耗统计
     */
    function monthLog(){
        $validate   = new vaMonthCount();
        $result     = $validate->check($this->request);
        !$result    && exit(ajaxReturn($validate->getError(), [], 10001));

        $start      = $this->request['start'];
        $end        = $this->request['end'];
        $id         = $this->request['id'];
        $member_id  = $this->member['id'];
        $export     = isset($this->request['export']) ? (int)$this->request['export'] : 0;

        $ApiService = new ApiService();
        $list       = $ApiService->getApiLogMonth($id, $member_id, $start, $end, $export);
        //导出数据
        $export && $list && $list = $this->exportDayLog($list['data'], date('YmdHis').'_'.'月消耗统计');

        exit(ajaxReturn('success',$list));
    }

    /**
     * 导出日（月）消耗统计
     * @param $data
     */
    function exportDayLog($data, $fileName){
        $title = [
            '日期',
            '总数',
            '成功数',
            '实号数',
            '空号数',
            '风险数',
            '沉默数',
            '库无数',
            '消费金额',
        ];
        $params['filename'] = $fileName;
        $params['title']    = $title;
        $params['data']     = $data;
        $export_service     = new ExportService();
        return $export_service->csv($params);
    }

    function validateExport(){
        $id     = (int)request()->param('id');
        $apiLog = new ApiLog();
        $memberId = $apiLog->getMemberId($id);
        if($this->member['id'] != $memberId){
            exit(ajaxReturn('非法操作',[],10001));
        }
    }

    /**
     * 导出空号检测记录
     */
    function exportMobileCheckLog(){
        //$start_mem =  round(memory_get_usage()/1024/1024, 2);
        $type       = (int)$this->request['type'];
        //0：未验证，1：实号，2：沉默号，3：危险号，4：空号，5：库无号
        $name_list = [
            0 => '未验证号',
            1 => '实号',
            2 => '沉默号',
            3 => '危险号',
            4 => '空号',
            5 => '库无号'
        ];
        //只能导出自己的
        $this->validateExport();

        $apiResult = new ApiResult();
        $path      = $apiResult->getExportInfo();
        if($path){
            $data = $this->handleData($path);

        }else{
            exit(ajaxReturn('success',''));
        }
        $name      = isset($name_list[$type]) ? $name_list[$type] : '';
        $data      = $name."\r\n".$data;
        $this->saveToFile($data);
        //$end_mem = round(memory_get_usage()/1024/1024, 2);
        //$mem_num = $end_mem-$start_mem;
        //$this->logError('空号检测导出消耗内存：'.$mem_num.'M');
        $url = \config('filesystem.disks.local.img_domain').$this->exportFilePath;
        exit(ajaxReturn('success',[$url]));
    }

    /**
     * 保存文件到数据
     * @param $data
     */
    protected function saveToFile($data){
        $path      = $this->getFileSrc();
        try{
            file_put_contents($path, $data);
        }catch (\Exception $e){
            $this->logError('保存导出数据到文件错误：'.$e->getMessage());
        }

    }

    /**
     * 获取文件路径
     * @return string
     * @throws \Exception
     */
    protected function getFileSrc(){
        $root_path = \config('filesystem.disks.local.root');
        $path      = DIRECTORY_SEPARATOR.'export'.DIRECTORY_SEPARATOR.date('Ymd').DIRECTORY_SEPARATOR.$this->member['id'].DIRECTORY_SEPARATOR;
        $file_name = md5(random_int(100000,999999)).'.txt';
        $this->exportFilePath =  $path . $file_name;;
        $path      = $root_path.$path;
        if(!is_dir($path)){
            mkdir($path,0777,true);
        }

        return $path . $file_name;
    }

    public function handleData($path){
        $path = config('filesystem.disks.local.root').$path;
        $glob = readOneFile($path);
        $result = '';

        while ($glob->valid()) {
            // 当前行文本
            $data = trim($glob->current());
            //排除空行
            if($data){
                $data   = json_decode($data, true);
                $result .= $data['mobile']."\r\n";
            }
            // 指向下一个，不能少
            $glob->next();
        }

        return $result;
    }

    function getResultFromFile($path){
        $root_path = \config('filesystem.disks.local.root');
        $file_path = $root_path.$path;
        return file_get_contents($file_path, 'r');
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
     * 获取指定api的调用统计
     */
    function getApiUsage()
    {
        $id = $this->member['id'];
        $api_id = request()->get('id');
        $indexService = new IndexService();
        $info = $indexService->getApiUsage($id, $api_id);
        exit(ajaxReturn('success', $info));
    }

}












