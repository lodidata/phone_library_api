<?php


namespace catchAdmin\product\model;

use catcher\base\CatchModel;

class MemberLoginLog extends CatchModel
{
    // 表名
    public $name = 'member_login_log';

    protected $createTime = 'login_at';

    // 数据库字段映射
    public $field = array(
        'id',
        // 会员ID
        'login_name',
        // 登录IP
        'login_ip',
        // 登录时间
        'login_at',
        //设备
        'login_os',
        // 登录地址
        'login_address',
        //浏览器
        'browser',
        'updated_at',
        'deleted_at'
    );

    /**
     * 列表
     *
     * @throws \think\db\exception\DbException
     */
    public function getList()
    {
        // 分页列表
        return $this->withoutField(['updated_at'])
            ->catchSearch()
            ->catchOrder()
            ->paginate();
    }

    /**
     * 搜索名称字段 catchSearch()
     * @param $query \catcher\CatchQuery
     * @param $value 搜索值
     * @param $data params参数
     */
    public function searchLoginNameAttr($query, $value, $data)
    {
        $query->whereLike('login_name', $value);
    }

    /**
     * @param $query \catcher\CatchQuery
     * @param $value 搜索值
     * @param $data params参数
     */
    public function searchLoginIpAttr($query, $value, $data)
    {
        $query->where('login_ip', $value);
    }

    public function searchStartTimeAttr($query, $value, $data)
    {
        return $query->where($this->aliasField('login_at'), '>=', strtotime($value));
    }

    public function searchEndTimeAttr($query, $value, $data)
    {
        return $query->where($this->aliasField('login_at'), '<=', strtotime($value)+86400);
    }
}
