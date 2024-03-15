<?php

namespace app\common\model;


class WalletLog extends Base
{
    protected $name = 'wallet_log';

    public function getLog($type, $member_id)
    {

        $list = $this->limit(0, 10)->getPageList(10);
        var_dump($list);exit;
        return $list;
    }
}