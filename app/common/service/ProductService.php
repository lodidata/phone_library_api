<?php


namespace app\common\service;
use app\api\validate\Product as vaProduct;

use app\common\model\ApiLog as ApiLogModel;
use app\common\model\Product as ProductModel;
use catcher\CatchUpload;


class ProductService extends BaseService
{
    public function __construct() {
        //数据操作类
        $this->datamodel = new ProductModel();
        //主键
        $this->pk = $this->datamodel->getPk();
    }
    /*
     * 获取所有产品列表
     */
    function getProductList($params){

        $where = [
                ['p.status','=',1],
                ['p.deleted_at','=',0],
            ];
        !empty($params['keyword']) && $where[] = ["p.name",'like',$params['keyword']."%"];

        if(isset($params['rank']) && !empty($params['rank'])){
            if($params['rank'] == 1){
                $order = 'p.created_at asc';
            }else if($params['rank'] == 2){
                $order = 'p.created_at desc';
            }
        }else{
            $order = 'p.sort asc';
        }


        $url  = CatchUpload::getCloudDomain('local');
        $list = $this->datamodel->getProductList($where,$order)->toArray();
        if($list){
            foreach($list as &$v){
                /*$charge_type = [
                    1 => '元/条',
                    2 => '元/次',
                ];*/
                //$charge_type = !empty($charge_type[$v['charge_type']]) ? $charge_type[$v['charge_type']] : '';
                $v['icon']   = !empty($v['icon'])?$url.$v['icon']:'';
                $v['price']  = floatval($v['price']);
                $v['charge_type']  = (int)$v['charge_type'];
                //unset($v['charge_type']);
            }
            unset($v);
        }

        return $list;
    }
    /*
     * 获取指定分类下的产品列表
     */
    function getProductById($params){
        $where[] = ['status','=',1];
        $where[] = ['category_id','=',$params['id']];
        if(isset($params['keyword']) && !empty($params['keyword'])){
            $where[] = ["name",'like',$params['keyword']."%"];
        }

        if(isset($params['rank']) && !empty($params['rank'])){
            if($params['rank'] == 1){
                $order = 'created_at asc';
            }else if($params['rank'] == 2){
                $order = 'created_at desc';
            }
        }else{
            $order = 'sort asc';
        }

        $url = CatchUpload::getCloudDomain('local');
        $list = $this->datamodel->getProductList($where,$order)->toArray();
        foreach($list as $k=>&$v){
            $v['icon'] = !empty($v['icon'])?$url.$v['icon']:'';
        }
        return $list;
    }

    function getCountData($params){

        $api_log_service = new ApiLogService();
        $index_service   = new IndexService();
        $time            = time();
        $today           = date('Y-m-d', $time);
        $yesterday       = date("Y-m-d",strtotime("-1 day", $time));
        $today_params    = [
                'start' => getStartDate($today),
                'end'   => getEndDate($today),
        ];
        $yesterday_params = [
                'start' => getStartDate($yesterday),
                'end'   => getEndDate($yesterday),
        ];

        $today_count        = $api_log_service->getCountData(array_merge($params,$today_params));
        $yesterday_count    = $api_log_service->getCountData(array_merge($params,$yesterday_params));

        $td_num             = $today_count['num'];
        $td_success_num     = $today_count['success_num'];
        $td_money           = $today_count['money'];
        $ytd_num            = $yesterday_count['num'];
        $ytd_success_num    = $yesterday_count['success_num'];
        $ytd_money          = $yesterday_count['money'];
        $num_per            = $index_service->_getPercent($td_num, $ytd_num);
        $suc_num_per        = $index_service->_getPercent($td_success_num, $ytd_success_num);
        $money_per          = $index_service->_getPercent($td_money, $ytd_money);

        $data = [
            'num' => [
                'value'     => floatNumber($td_num),
                'percent'   => $num_per. '%',
                'type'      => $num_per > 0 ? 1 : 2
            ],
            'success_num' => [
                'value'     => floatNumber($td_success_num),
                'percent'   => $suc_num_per. '%',
                'type'      => $suc_num_per > 0 ? 1 : 2
            ],
            'money' => [
                'value'     => floatNumber($td_money),
                'percent'   => $money_per. '%',
                'type'      => $money_per > 0 ? 1 : 2
            ],
        ];
        return $data;
    }
}














