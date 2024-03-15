<?php
namespace catchAdmin\product\model;


use catchAdmin\product\model\Member as MemberModel;
use catcher\base\CatchModel;
use catcher\CatchQuery;

/**
 * IP白名单
 */
class MemberIp extends CatchModel
{
    // 表名
    public $name = 'member_ip';

    public $autoWriteTimestamp = false;

    // 数据库字段映射
    public $field = array(
        'id',
        // 账号ID
        'member_id',
        // ips
        'ips',
        // 时间
        'time'
    );

    public function getList()
    {
        // 不分页
        if (property_exists($this, 'paginate') && $this->paginate === false) {
            return $this->catchSearch()
                ->alias('a')
                ->field('a.id,a.member_id,a.ips')
                ->catchLeftJoin(MemberModel::class, 'id', 'member_id', ['user_name','user_account'])
                ->fetchSql(true)
                ->select();
        }

        // 分页列表
        return $this->catchSearch()
            ->alias('a')
            ->field('a.id,a.member_id,a.ips')
            ->catchLeftJoin(MemberModel::class, 'id', 'member_id', ['user_name','user_account'])
            ->paginate();
    }

    /**
     * @param CatchQuery $query
     * @param string $value 搜索值
     * @param arra $data 参数
     */
    public function searchUserAccountAttr($query, $value, $data)
    {
        $memberModel = new MemberModel();
        $query->whereLike($memberModel->getAlias().'.user_account', $value);
    }

    public function searchIpAttr($query, $value, $data)
    {
        $query->whereLike($this->aliasField('ips'), $value);
    }
}