<?php

namespace app\common\model;
use think\Model;

class Base extends Model
{
    //默认分页数量
    protected $pageSize = 20;
    //默认排序字段
    protected $ordername = 'id';
    //默认排序方式
    protected $orderway = 'DESC';


    /**
     * 获取所有数据
     */
    public function getList() {
        return $this->order(array($this->ordername => $this->orderway))->select();
    }
    /**
     * 获取纪录总数
     */
    public function getCount() {
        return $this->count();
    }
    /**
     * 获取分页数据
     */
    public function getPageList($page) {
        return $this->order(array($this->ordername => $this->orderway))->limit(($page - 1) * $this->pageSize, $this->pageSize)->select()->toArray();
    }
    /**
     * 获取每页显示的记录数
     */
    public function getPageSize() {
        return $this->pageSize;
    }
    /**
     * 设置每页显示的记录数
     */
    public function setPageSize($pagesize) {
        $this->pageSize = $pagesize;
    }
    /**
     * 获取表信息
     */
    public function getTableInfo() {
        return $this->getQuery()->getTableInfo();
    }
}