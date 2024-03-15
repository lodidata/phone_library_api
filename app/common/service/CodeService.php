<?php


namespace app\common\service;

use app\common\model\Code;
use think\facade\Cache;

class CodeService extends BaseService
{

    public function __construct() {
        //数据操作类
        $this->datamodel = new Code();
        //主键
        $this->pk = $this->datamodel->getPk();
    }
    public function getLists($page){

        $list = $this->datamodel->paginate(request()->param('size'),false,['query'=>request()->param()])->toArray();

        return $list;
    }

    public function getList(){
        $list = $this->datamodel->select()->toArray();

        return $list;
    }

    /**
     * 获取返回code码及说明
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAllCode()
    {
        $cacheKey = 'api_codes';
        if(!$data = Cache::get($cacheKey)){
            $data = [];
            $list = $this->datamodel->field('code,describe')->select()->toArray();
            if($list){
                foreach ($list as $item){
                    $data[$item['code']] = $item['describe'];
                }
            }
            Cache::set($cacheKey, $data, 3600);
        }

        return $data;
    }

}