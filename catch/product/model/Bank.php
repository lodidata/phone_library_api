<?php

namespace catchAdmin\product\model;

use catcher\base\CatchModel;

class Bank extends CatchModel
{
    // 表名
    public $name = 'bank';
    // 数据库字段映射
    public $field = array(
        'id',
        // 账号
        'bank_account',
        // 银行名称
        'bank_name',
        // 银行卡号
        'bank_code',
        // 开户行
        'bank_address',
        // 二维码
        'qr_code',
        // 1开启2关闭
        'status',
        // 排序
        'sort',
        // appkey
        'creator_id',
        // 注册时间
        'created_at',
        // 更新时间
        'updated_at',
        // 删除时间
        'deleted_at',
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
            ->catchOrder('asc')
            ->creator()
            ->paginate();
    }

    /**
     * @param $query
     * @param $value
     * @param $data
     * TODO 根据启用状态查找
     */
    public function searchStatusAttr($query, $value, $data)
    {
        $query->where(['status' => $value]);
    }

    /**
     * 户名模糊查询
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchBankAccountAttr($query, $value, $data)
    {
        $query->whereLike('bank_account', $value);
    }

}
