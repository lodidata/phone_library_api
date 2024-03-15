<?php


namespace app\common\service;
use app\common\model\Base;


class BaseService
{
    //主键
    protected $pk;
    //数据操作类
    protected $datamodel;

    //缓存名称
    protected $cachename = '';
    //用于树型数组完成递归格式的全局变量
    private $formatTree;

    protected $request;


    public function add($data) {
        //清除缓存
        if(!empty($this->cachename)) {
            cache($this->cachename, null);
        }
        //新增数据,返回主键id
        return $this->datamodel->insertGetId($data);
    }
    /**
     * 编辑数据
     * @param $data 表单数据
     * @param $id   编号
     */
    public function edit($data, $id) {
        //清除缓存
        if(!empty($this->cachename)) {
            cache($this->cachename, null);
        }
        //获取主键更新条件
        if(regUInteger($id))
            $where = array($this->pk => $id);
        else
            $where = array($this->pk => array('in', $id));
        //更新数据
        return $this->datamodel->update($data, $where);
    }
    /**
     * 更新排序
     * @param $data 表单数据
     */
    public function order($datalist) {
        //清除缓存
        if(!empty($this->cachename)) {
            cache($this->cachename, null);
        }
        $this->datamodel->startTrans();
        try {
            foreach($datalist as $data) {
                $this->datamodel->update($data['data'], $data['where']);
            }
            $this->datamodel->commit();
            return true;
        } catch(\Exception $e) {
            cmslog($e, 'error');
            $this->datamodel->rollback();
        }
        return false;
    }
    /**
     * 删除数据
     * @param $id 编号
     */
    public function del($id) {
        //清除缓存
        if(!empty($this->cachename)) {
            cache($this->cachename, null);
        }
        //获取主键删除条件
        if(regUInteger($id))
            $where = array($this->pk => $id);
        else
            $where = array($this->pk => array('in', $id));
        //删除数据
        return $this->datamodel->where($where)->delete();
    }
    /**
     * 获取单条数据
     * @param $id 编号
     */
    public function getInfo($id) {
        if(!empty($this->cachename)) {
            $datalist = $this->getList();
            foreach($datalist as $datainfo) {
                if($datainfo[$this->pk] == $id) {
                    return $datainfo;
                }
            }
        } else {
            $datainfo = $this->datamodel->find($id);
            if(!empty($datainfo))
                return $datainfo->toArray();
        }
        return false;
    }
    /**
     * 获取所有数据
     */
    public function getList() {
        if(!empty($this->cachename)) {
            $datalist = cache($this->cachename);
            if(!empty($datalist)) {
                return $datalist;
            } else {
                $datalist = $this->datamodel->getList();
                if(!empty($datalist)) {
                    cache($this->cachename, $datalist);
                }
                return $datalist;
            }
        } else {
            return $this->datamodel->getList();
        }
    }
    /**
     * 获取纪录总数
     */
    public function getCount() {
        return $this->datamodel->getCount();
    }
    /**
     * 获取分页数据
     * @param $page 页码
     */
    function getPageList($page) {

        return $this->datamodel->getPageList($page);
    }
    /**
     * 获取每页显示的记录数
     */
    public function getPageSize() {
        return $this->datamodel->getPageSize();
    }
    /**
     * 设置每页显示的记录数
     */
    public function setPageSize($pagesize) {
        $this->datamodel->setPageSize($pagesize);
    }
    /**
     * 获取表信息
     */
    public function getTableInfo() {
        return $this->datamodel->getTableInfo();
    }
    /**
     * 获取mysql版本号
     */
    public function version() {
        return $this->datamodel->version();
    }
    /**
     * 获取所有表
     */
    public function getTableList() {
        return $this->datamodel->getTableList();
    }
    /**
     * 优化表
     * @param $tables 数据表集合
     */
    public function optimize($tables) {
        return $this->datamodel->optimize($tables);
    }
    /**
     * 修复表
     * @param $tables 数据表集合
     */
    public function repair($tables) {
        return $this->datamodel->repair($tables);
    }
    /**
     * 从数据库获取信息，并进行树形处理
     */
    public function getTreeData($key, $parentkey) {
        $datalist = $this->datamodel->getList();
        $treelist = array();
        foreach($datalist as $datainfo)
            array_push($treelist, $datainfo);
        return $this->toTree($treelist, $key, $parentkey, 0);
    }
    /**
     * 获取用户ip
     */
    public function getUserIp() {
        //获取请求类
        $request = Request::instance();
        //返回用户ip
        return $request->ip();
    }
    /**
     * 将格式数组转换为树
     *
     * @param $list 数组集合
     * @param $pk   主键
     * @param $pid  父类键值
     * @param $root 层级
     */
    private function toTree($list, $pk = 'id', $pid = 'pid', $root = 0){
        $list = list_to_tree($list, $pk, $pid, '_child', $root);
        $this->formatTree = array();
        $this->toFormatTree($list, $pk, $pid, 0);
        return $this->formatTree;
    }
    private function toFormatTree($list, $pk='id', $pid = 'pid', $level = 0) {
        foreach($list as $key => $val){
            if ($key == count($list) - 1) {
                $islast = true;
            } else {
                $islast = false;
            }
            $val['level'] = $level;
            $val['childlist'] = '';
            $val['islast'] = $islast;
            if(!array_key_exists('_child', $val)){
                $val['childlist'] = $val[$pk];
                if($val[$pid] > 0) {
                    if(!empty($this->formatTree[$val[$pid]]['childlist']))
                        $this->formatTree[$val[$pid]]['childlist'] .= ','.$val[$pk];
                    else
                        $this->formatTree[$val[$pid]]['childlist'] = $val[$pk];
                }
                if($level > 1) {
                    $parentinfo = $this->formatTree[$val[$pid]];
                    while($parentinfo[$pid] > 0) {
                        $this->formatTree[$parentinfo[$pid]]['childlist'] .= ','.$val[$pk];
                        $parentinfo = $this->formatTree[$parentinfo[$pid]];
                    }
                }
                $this->formatTree[$val[$pk]] = $val;
            } else {
                $tmp_ary = $val['_child'];
                unset($val['_child']);
                if(!empty($this->formatTree[$val[$pk]]['childlist']))
                    $val['childlist'] .= ','.$val[$pk];
                else
                    $val['childlist'] = $val[$pk];
                if($val[$pid] > 0) {
                    if(!empty($this->formatTree[$val[$pid]]['childlist']))
                        $this->formatTree[$val[$pid]]['childlist'] .= ','.$val[$pk];
                    else
                        $this->formatTree[$val[$pid]]['childlist'] = $val[$pk];
                }
                if($level > 1) {
                    $parentinfo = $this->formatTree[$val[$pid]];
                    while($parentinfo[$pid] > 0) {
                        $this->formatTree[$parentinfo[$pid]]['childlist'] .= ','.$val[$pk];
                        $parentinfo = $this->formatTree[$parentinfo[$pid]];
                    }
                }
                $this->formatTree[$val[$pk]] = $val;
                $this->toFormatTree($tmp_ary, $pk, $pid, $level+1);
            }
        }
        return;
    }
}