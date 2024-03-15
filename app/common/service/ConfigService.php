<?php


namespace app\common\service;
use app\common\model\Config as ConfigModel;

class ConfigService extends BaseService
{
    public function __construct()
    {
        //数据操作类
        $this->datamodel = new ConfigModel();
    }

    /**
     * 获取联系人
     * @param $component
     * @param array $contact
     * @return array|mixed
     */
    public function getContact($component, $contact=[]){
        !$contact && $contact = ['sale', 'devops'];
        $list = $this->datamodel->getConfig($component);
        if($list){
            foreach ($list as $k => $v){
                if(!in_array($k,$contact)){
                    unset($list[$k]);
                }
            }
        }

        return $list;
    }


}