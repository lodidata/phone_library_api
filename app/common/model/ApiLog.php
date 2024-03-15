<?php

namespace app\common\model;


class ApiLog extends Base
{
    protected $name = 'api_log';
    protected $type = [
        'money'          => 'float',
        'unverified_num' => 'float',
        'real_num'       => 'float',
        'silent_num'     => 'float',
        'risk_num'       => 'float',
        'empty_num'      => 'float',
        'ku_num'         => 'float',
    ];

    function getLog($where)
    {
        return $this->where($where)->count();
    }

    function getMemberId($id){
        return current($this->where('id',$id)->column('member_id'));
    }
//    function getLists($id)
//    {
//        return $this->where(['member_id' => $id])->paginate(20, false, ['query' => request()->param()]);
//    }

    /**
     * TODO 获取统计数据
     */
    function getCountData($where, $field = null)
    {
        !$field && $field = 'sum(success_num) success_num, sum(fail_num) fail_num';

        $data = $this->where($where)->field($field)->find();
        return $data;

    }

    /**
     * 更新信息
     * @param $where
     * @param $data
     * @return ApiLog
     */
    function updateInfo($where,$data){
        return $this->where($where)->update($data);
    }

    function getLists($where, $field = '*'){
        return $this->field($field)->where($where)->order('created_at desc')->paginate(request()->param('size'),false,['query'=>request()->param()]);
    }

    /**
     * 空号文件检测列表
     * @param $where
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getUheckLists($where, $field = '*'){
        $res = $this->field('id')->where($where)->order('id desc')->paginate(request()->param('size'),false,['query'=>request()->param()])->toArray();

        if(!$res['data']){
            return $res;
        }else{
            $ids = array_column($res['data'],'id');
        }
        $lists = $this->alias('al')->field($field)->leftJoin('wl_api_result ar','al.id = ar.api_log_id')->where('al.id','in',$ids)->group('al.id')->order('al.id desc')->select()->toArray();

        $res['data'] = $lists;
        return $res;
    }

    function getListDay($where, $field=null){
        !$field && $field = 'left(created_at,10) date';
        $i = $this->where($where)->field($field)->group('left(created_at,10)')->order('date desc')->paginate(request()->param('size'),false,['query'=>request()->param()]);
        return $i;
    }

    /**
     * 导出
     * @param $where
     * @return mixed
     */
    function exportListDay($where){
        $i = $this->where($where)->field('left(created_at,10) date')->group('left(created_at,10)')->order('date desc')->select()->toArray();
        return $i;
    }

    function getDatas($where){
        $field   = 'al.id,al.success_num,al.fail_num,al.money,al.created_at,  
                    max(case when ar.type=0 then ar.num else 0 end) as unverified_num,
                    max(case when ar.type=1 then ar.num else 0 end) as real_num,
                    max(case when ar.type=2 then ar.num else 0 end) as silent_num,
                    max(case when ar.type=3 then ar.num else 0 end) as risk_num,
                    max(case when ar.type=4 then ar.num else 0 end) as empty_num,
                    max(case when ar.type=5 then ar.num else 0 end) as ku_num';
        return $this->alias('al')->leftJoin('api_result ar','al.id = ar.api_log_id')->where($where)->field($field)->group('al.id')->select()->toArray();
    }

    function getListMonths($where, $field= null){
        !$field && $field = 'left(created_at,7) date';
        return $this->where($where)->field($field)->group('left(created_at,7)')->order('date desc')->paginate(request()->param('size'),false,['query'=>request()->param()]);
    }

    /**
     * 月消耗记录
     * 导出
     * @param $where
     * @return mixed
     */
    function exportListMonths($where){
        return $this->where($where)->field('left(created_at,7) date')->group('left(created_at,7)')->order('date desc')->select()->toArray();
    }

    /**
     * 获取文件 空号检测
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getUcheckList(){
        return $this->field('id,api_id,member_id,new_file_name,money,code')->where(['code' => 0, 'type' => 2])->select()->toArray();
    }
}