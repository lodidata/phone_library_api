<?php

namespace catchAdmin\product\model;

use catchAdmin\permissions\model\Users as UserModel;
use catcher\CatchQuery;
use catcher\CatchUpload;
use catcher\traits\db\BaseOptionsTrait;
use catcher\traits\db\ScopeTrait;
use catcher\base\CatchModel;

use catchAdmin\product\model\Member as MemberModel;

class MemberCharge extends CatchModel
{
    use BaseOptionsTrait, ScopeTrait;

    // 表名
    public $name = 'member_charge';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    protected $autoWriteTimestamp = true;

    // 数据库字段映射
    public $field = array(
        'id',
        // 会员ID
        'member_id',
        // 充值金额
        'amount',
        // 申请时间
        'created_at',
        //操作时间
        'updated_at',
        // 充值凭证
        'images',
        //存入账户
        'charge_account',
        // 充值备注
        'remark',
        //操作者
        'creator_id',
        // 充值状态 0审核中  1 充值成功 2 充值失败
        'status',
        'deleted_at'
    );

    /**
     * 查询充值列表
     * @return mixed
     */
    public function getList()
    {
        // 分页列表
        $res = $this->catchSearch()
            ->alias('a')
            ->catchLeftJoin(MemberModel::class, 'id', 'member_id', ['user_name','user_account'])
            ->catchLeftJoin(UserModel::class, 'id', 'creator_id', ['username as creator'])
            ->field('a.*')
            /*->fetchSql(true)
            ->select();*/
            ->catchOrder()
            ->paginate();
        /*$res->each(function (&$item){
            $item['images'] = CatchUpload::getCloudDomain('local') . $item['images'];
        });*/
        return $res;
    }

    /**
     * 搜索状态字段 catchSearch()
     * @param $query \catcher\CatchQuery
     * @param $value 搜索值
     * @param $data params参数
     */
    public function searchStatusAttr($query, $value, $data)
    {
        $query->where([$this->getAlias().'.status' => $value]);
    }

    /**
     * 搜索名称字段 catchSearch()
     * @param $query \catcher\CatchQuery
     * @param $value 搜索值
     * @param $data params参数
     */
    public function searchUserNameAttr($query, $value, $data)
    {
        $member = new MemberModel();
        $query->where($member->getAlias().'.user_name|'.$member->getAlias().'.user_account', 'like', "%".$value."%");
    }

    public function searchStartTimeAttr($query, $value, $data)
    {
        return $query->where($this->aliasField('created_at'), '>=', strtotime($value));
    }

    public function searchEndTimeAttr($query, $value, $data)
    {
        return $query->where($this->aliasField('created_at'), '<=', strtotime($value)+86400);
    }

    public function getStatusTextAttr($value, $data)
    {
        $status = [0=>'待处理',1=>'已入款',2=>'未通过'];
        return $status[$data['status']];
    }

}
