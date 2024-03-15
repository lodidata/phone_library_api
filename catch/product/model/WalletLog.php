<?php

namespace catchAdmin\product\model;

use catchAdmin\permissions\model\Users as UserModel;
use catchAdmin\product\model\Member as MemberModel;
use catcher\base\CatchModel;
use catcher\CatchQuery;
use catcher\exceptions\FailedException;

class WalletLog extends CatchModel
{
    // 表名
    public $name = 'wallet_log';

    //1手工充值
    public const CHARGE_TYPE_ADD = 1;
    //2手工扣除
    public const CHARGE_TYPE_SUB = 2;
    //3接口扣除
    public const CHARGE_TYPE_API_SUB = 3;
    //4线下充值
    public const CHARGE_TYPE_OFFLINE_ADD = 4;
    //5注册赠送
    public const CHARGE_TYPE_REG_ADD = 5;

    // 数据库字段映射
    public $field = array(
        'id',
        // 会员id
        'member_id',
        // 操作前钱包余额
        'before_money',
        // 余额操作
        'action_money',
        // 剩余账户
        'after_money',
        // 说明
        'remark',
        // 创建时间
        'created_at',
        //删除时间
        'deleted_at',
        //接口日志ID
        'api_log_id',
        //接口ID
        'api_id',
        //充值记录ID
        'member_charge_id',
        //操作类型 1手工充值 2手工扣除 3接口扣除 4线下充值 5注册赠送
        'charge_type',
        //操作者
        'creator_id'
    );

    /**
     * 添加充值流水记录
     * @param $data 字段参数
     * @return mixed
     */
    public function addCharge(array $data)
    {
        // 启动事务
        $this->startTrans();
        $memberModel = new MemberModel();
        $memberInfo = $memberModel->field('id,wallet')->find($data['member_id']);

        $memberData = [];
        if(in_array($data['charge_type'], [1,4,5])){
            $memberData['wallet'] = bcadd($data['action_money'], $memberInfo['wallet'], 4);
        }else{
            $memberData['wallet'] = bcsub($memberInfo['wallet'], $data['action_money'], 4);
        }
        //余额为负数
        if(bccomp($memberData['wallet'], 0, 4) < 0){
            throw new FailedException('余额不足',10020);
            $this->rollback();
        }
        $data['before_money'] = $memberInfo['wallet'];
        $data['after_money']  = $memberData['wallet'];
        $charge_log_id = $this->storeBy($data);
        if(!$charge_log_id){
            throw new FailedException('新增充值流水记录失败',10020);
            $this->rollback();
        }
        $res = $memberModel->updateBy($data['member_id'], $memberData);
        if(!$res){
            throw new FailedException('更新用户余额失败',10021);
            $this->rollback();
        }
        // 提交事务
        $this->commit();

        return $charge_log_id;
    }

    /**
     * 查询列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList()
    {
        // 分页列表
        return $this->catchSearch()
            ->alias('a')
            ->catchLeftJoin(MemberModel::class, 'id', 'member_id', ['user_name'])
            ->catchLeftJoin(UserModel::class, 'id', 'creator_id', ['username as creator'])
            ->field('a.*')
            ->catchOrder()
            ->paginate();
    }

    /**
     * 搜索操作类型 catchSearch()
     * @param $query \catcher\CatchQuery
     * @param $value 搜索值
     * @param $data params参数
     */
    public function searchChargeTypeAttr($query, $value, $data)
    {
        $query->where([$this->getAlias().'.charge_type' => $value]);
    }

    public function searchApiIdAttr($query, $value, $data)
    {
        $query->where('api_id', $value);
    }
    public function searchMemberChargeIdAttr($query, $value, $data)
    {
        $query->where('member_charge_id', $value);
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
}