<?php


namespace app\common\model;


class ApiResult extends Base
{
    protected $name = 'api_result';

    public function getExportInfo(){
        $where = [
                'api_log_id' => (int)request()->param('id'),
                'type'       => (int)request()->param('type'),
            ];
        return current($this->where($where)->column('result'));
    }
}