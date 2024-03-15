<?php

namespace catchAdmin\product\model;

use app\api\validate\Register as vaRegister;
use app\common\service\Safety;
use catcher\base\CatchModel;

class Member extends CatchModel
{
    // 表名
    public $name = 'member';
    // 数据库字段映射
    public $field = array(
        'id',
        // 账号
        'user_account',
        // 用户名称
        'user_name',
        // 密码
        'password',
        // 联系方式
        'user_contact',
        // 钱包
        'wallet',
        // 1开启2关闭
        'status',
        // appid
        'appid',
        // appkey
        'appkey',
        // token
        'token',
        // 登录IP
        'last_login_ip',
        // 登录时间
        'last_login_time',
        // 注册时间
        'created_at',
        // 更新时间
        'updated_at',
        // 删除时间
        'deleted_at',
    );

    /**
     * @param $params
     * @return mixed
     * TODO 添加会员
     */
    public function register($params)
    {

        $validate = new vaRegister;
        $result = $validate->check($params);
        if (!$result)
            exit(ajaxReturn($validate->getError(), [], 10001));
        $resultInfo = $this->find(['user_account' => $params['user_account']]);

        if ($resultInfo)
            exit(ajaxReturn('fail', [], 10001));

        //验证码验证
        unset($params['code']);
        //删除确认密码
        unset($params['repassword']);

        $params['password'] = Safety::mPassword($params['password']);
        $params['create_at'] = time();
        $params['appid'] = Safety::getRandChar(8);
        $params['appkey'] = Safety::getRandChar(8);
        $this->storeBy($params);
    }

    /**
     * 查询列表
     *
     * @time 2020年04月28日
     * @return mixed
     */
    public function getList()
    {
        // 不分页
        if (property_exists($this, 'paginate') && $this->paginate === false) {
            return $this->catchSearch()
                ->withoutField('token,password,updated_at,deleted_at')
                ->catchOrder()
                ->creator()
                ->select();
        }

        // 分页列表
        return $this->catchSearch()
            ->withoutField('token,password,updated_at,deleted_at')
            ->catchOrder()
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
     * @param $query
     * @param $value
     * @param $data
     * TODO 根据用户名查找
     */
    public function searchNameAttr($query, $value, $data)
    {
        $query->whereLike('user_name' , $value);
    }

    /**
     * @param $query
     * @param $value
     * @param $data
     * TODO 根据公司查找
     */
    public function searchAccountAttr($query, $value, $data)
    {
        $query->whereLike('user_account' , $value);
    }

}
