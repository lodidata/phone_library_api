<?php
namespace app\common\service;
use app\common\model\Member as MemberModel;
use app\common\model\redis\TokenRedis;
use app\common\service\DesEncryptService;
use app\api\validate\Register as vaRegister;
use app\api\validate\Login as vaLogin;
use app\api\validate\ModifyPassword as vaModifyPassword;
use app\common\model\MemberIp;
use catchAdmin\product\model\MemberLoginLog;
use edward\captcha\facade\CaptchaApi;
use think\facade\Cache;
use think\facade\Db;

class MemberService extends BaseService
{
    public function __construct() {
        //数据操作类
        $this->datamodel = new MemberModel();
        //主键
        $this->pk        = $this->datamodel->getPk();
    }

    //查询用户信息
    function getLogin($params){
        $validate    = new vaLogin;
        $first_login = 0;//首次登录 1：是，0：不是
        $result      = $validate->check($params);
        !$result     && exit(ajaxReturn($validate->getError(),[],10001));
        //判断验证码
        $res         = $this->checkCaptcha($params['code'], $params['key']);
        !$res        && exit(ajaxReturn('验证码错误',[],10001));

        $info = $this->datamodel->getUserInfo($params['user_account']);

        if(empty($info) || $info['password'] != Safety::mPassword($params['password'])){
            exit(ajaxReturn('账号或者密码不正确',[],10001));
        }

        $info['status'] == 2      && exit(ajaxReturn('账号已停用，请联系管理员',[],10001));
        !$info['last_login_time'] && $first_login = 1;
        //是否绑定ip
        $bind_ip = 0;//1：绑定ip,0：未绑定
        $ip      = $this->getIp($info['id']);
        $ip      && $bind_ip = 1;

        $token_params = [
            'id'       => $info['id'],
            'type'     => 'member',
            'expire'   => 3600*24,
        ];

        $token = DesEncryptService::getToken($token_params);

        $data = [
            //'token'           => $token,   token字段不用了
            'last_login_ip'   => request()->ip(),
            'last_login_time' => time()
        ];
        $this->datamodel->editInfo($data,['id'=>$info['id']]);
        $new_data = [
            'token'       => $token,
            'first_login' => $first_login,
            'bind_ip'     => $bind_ip,
        ];
        $this->memberLoginLog($params['user_account']);
        return $new_data;

    }

    /**
     * 退出接口
     * @param $params
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    function logout($params){
        //修改成功 删除token
        return TokenRedis::delToken($params['id']);
    }

    //注册用户
    function register($params){

        $validate = new vaRegister;
        $result = $validate->check($params);
        if(!$result)
            exit(ajaxReturn($validate->getError(),[],10001));

        //验证码验证
        unset($params['code']);
        unset($params['repassword']);

        $params['password'] = Safety::mPassword($params['password']);
        $params['created_at'] = time();
        $params['appid'] = Safety::getRandChar(8);
        $params['appkey'] = Safety::getRandChar(8);
        Db::startTrans();
        $memberId = $this->datamodel->insertGetId($params);
        $token = Safety::generateToken($params['user_account']);
        Cache::set($token, ['member_id'=>$memberId]);
        $data = [
            'token'=>$token,
        ];
        $this->datamodel->editInfo($data,['id'=>$memberId]);
        $MemberIp = new MemberIp();
        $MemberIp->data(['member_id' =>$memberId])->save();
        if(!$memberId){
            Db::rollback();
            exit(ajaxReturn('fail',[],10001));
        }
        Db::commit();
        return ['token'=>$token];
    }


    function getUserById($id){
        $field = 'id,user_account,user_name,user_contact,wallet,appid,appkey,status';
        return $this->datamodel->getUserById(['id'=>$id],$field)->toArray();
    }
    function getKeyById($id){
        $field = 'id,appid,appkey';
        return $this->datamodel->getUserById(['id'=>$id],$field)->toArray();
    }
    function getIdBykey($appid,$appkey){
        return $this->datamodel->getIdBykey($appid,$appkey);
    }
    //新建表模型 用户ip
    function UpdateIp($id, $ips){
        $MemberIp = new MemberIp();

        $find = $MemberIp->where(['member_id'=>$id])->find();

        if(empty($find)){
            $result = $MemberIp->insert(['member_id'=>$id,'ips'=>$ips,'time'=>time()]);
        }else{
            $result = $MemberIp->where(['member_id'=>$id])->update(['ips'=>$ips,'time'=>time()]);
        }
        return $result;
    }

    function getIp($id){
        $MemberIp = new MemberIp();
        $find = $MemberIp->where(['member_id'=>$id,'deleted_at'=>0])->find();

        if(empty($find->ips)){
            return [];
        }else{
            $result = explode(',',$find->ips);
        }
        return $result;

    }

    /**
     *
     * @param $code
     * @param $key
     * @return bool true 验证成功
     */
    function checkCaptcha($code, $key)
    {
        return CaptchaApi::check($code, $key);
    }

    /**
     * 修改密码
     * @param $params
     */
    function modifyPassword($params){
        $validate    = new vaModifyPassword;
        $result      = $validate->check($params);
        !$result && exit(ajaxReturn($validate->getError(),[],10001));
        $member_id   = $params['id'];
        $password    = $this->datamodel->getInfoById($member_id, 'password')['password'];
        $password != Safety::mPassword($params['old_password']) && exit(ajaxReturn('老密码不正确',[],10001));
        $data        = [
            'password' => Safety::mPassword($params['password'])
        ];
        $res          = $this->datamodel->editInfo($data, ['id' => $member_id]);
        !$res && exit(ajaxReturn('修改密码失败',[],10001));
        //修改成功 删除token
        TokenRedis::delToken($params['id']);
        return true;
    }

     /** 登录日志
     *
     * @time 2020年09月09日
     * @param $name
     * @param bool $success
     * @return void
     */
    protected function memberLoginLog($login_name)
    {
        $agent = request()->header('user-agent');
        $ip = request()->ip();
        $data = [
            'login_name' => $login_name,
            'login_ip'   => $ip,
            'browser'    => getBrowser($agent),
            'login_os'   => getOs($agent),
            'login_address' => ip2address($ip),
        ];
        app(MemberLoginLog::class)->storeby($data);
    }

}
