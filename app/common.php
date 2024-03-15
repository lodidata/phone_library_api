<?php
// 应用公共文件1
use think\facade\Db;
use app\common\model\redis\UcheckRedis;

function ajaxReturn($message = 'success', $data = [], $code = 10000)
{
    header('Content-Type:application/json; charset=utf-8');
    if (!is_array($data) || empty($data)){
        return json_encode(['data'=>array(),'code'=>$code,'message'=>$message]);
    }
    return json_encode(['data'=> !array_key_exists(0,$data) ? array($data) : $data,'code'=>$code,'message'=>$message]);
}
/*
 * 获取协议
 */
function get_http_type()
{
    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http';
    return $http_type;
}

/*
 * 获取协议+域名
 */
function getDomain(){
    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';

    $http_type = $http_type.$_SERVER['HTTP_HOST'];
    return $http_type;
}
/*
 * 分表
 */
function getPhoneServiceByid($mobile,$s=10){
    return intval(fmod(sprintf("%u",crc32($mobile)),$s)) +1;
}
/*
* 判断数据库是否存在表名
*/
function existTable($tablesName){
    $UcheckRedis  = new UcheckRedis();
    $allTables    = $UcheckRedis->getAllTables();
    $dataBaseName = config('database.connections.mysql.database');
    if(empty($dataBaseName)){
        return false;
    }
    $dataBaseName = 'Tables_in_'.$dataBaseName;
    if(!$allTables){
        $allTables = Db::query("show tables");
        if(empty($allTables)){
            return false;
        }
        $allTables = array_column($allTables,$dataBaseName);
        $allTables = implode($allTables,',');
        $UcheckRedis->setAllTables($allTables);
    }
    if(strpos($allTables,$tablesName) !== false){
        return true;
    }

    return false;
}
/*
 * 获取服务端ip
 */
function getIp(){
    if (isset ($_SERVER ['HTTP_X_FORWARDED_FOR'])){
        $clientIP = $_SERVER ['HTTP_X_FORWARDED_FOR'];
    }
    elseif (isset ($_SERVER ['HTTP_X_REAL_IP'])){
        $clientIP = $_SERVER ['HTTP_X_REAL_IP'];
    }
    else {
        $clientIP = $_SERVER['REMOTE_ADDR'];
    }
    return $clientIP;
}

/**
 * 获取操作系统
 * @time 2019年12月12日
 * @param $agent
 * @return string
 */
function getOs($agent): string
{
    if (false !== stripos($agent, 'win') && preg_match('/nt 6.1/i', $agent)) {
        return 'Windows 7';
    }
    if (false !== stripos($agent, 'win') && preg_match('/nt 6.2/i', $agent)) {
        return 'Windows 8';
    }
    if(false !== stripos($agent, 'win') && preg_match('/nt 10.0/i', $agent)) {
        return 'Windows 10';#添加win10判断
    }
    if (false !== stripos($agent, 'win') && preg_match('/nt 5.1/i', $agent)) {
        return 'Windows XP';
    }
    if (false !== stripos($agent, 'linux')) {
        return 'Linux';
    }
    if (false !== stripos($agent, 'mac')) {
        return 'mac';
    }

    return '未知';
}

/**
 * 获取浏览器
 * @time 2019年12月12日
 * @param $agent
 * @return string
 */
function getBrowser($agent): string
{
    if (false !== stripos($agent, "MSIE")) {
        return 'MSIE';
    }
    if (false !== stripos($agent, "Firefox")) {
        return 'Firefox';
    }
    if (false !== stripos($agent, "Chrome")) {
        return 'Chrome';
    }
    if (false !== stripos($agent, "Safari")) {
        return 'Safari';
    }
    if (false !== stripos($agent, "Opera")) {
        return 'Opera';
    }

    return '未知';
}

/**
 * 价格校验
 * @param float $value 金额
 * @param int $scale 小数位
 * @return bool
 */
function checkMoney($value, $scale = 2){
    if (!is_numeric($value)) {
        return false;
    }
    if ($value <= 0) {
        return false;
    }

    if (preg_match('/^[0-9]+(\.\d{1,'.$scale.'})?$/', $value)) {
        return true;
    } else {
        return false;
    }
}

/**
 * IP转地址
 * @param $ip
 * @return mixed|string
 */
function ip2address($ip){
    include_once root_path().'extend/ORG/Ip2Region.php';
    $ipcls = new \ORG\Ip2Region();
    $addr = $ipcls->ip2addr($ip);
    return isset($addr['country']) && $addr['country'] ? $addr['country'] : '未知';
}

/**
 * 获取开始日期
 * @param $date
 */
function getStartDate($date){
    return date('Y-m-d',strtotime($date)).' 00:00:00';
}

/**
 * 获取结束日期
 * @param $date
 */
function getEndDate($date, $needDays=0){
    //返回这个月的最后一天
    if($needDays){
        $year  = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        $days  = date('t', strtotime($year . '-' . $month . '-01'));//返回天数
        return "{$year}-{$month}-{$days} 23:59:59";
    }else{
        return date('Y-m-d', strtotime($date)).' 23:59:59';
    }

}

/**
 * 返回格式化后的数字
 * @param $num
 * @return float
 */
function floatNumber($num){
    return floatval(number_format($num,4,'.', ''));
}

/**
 * 获取订单号
 * @return string
 */
function getOrderNum(){
    return date('YmdHis').mt_rand(100000,999999);
}

/**
 * @param $type
 * 获取订单类型
 */
function getOrderType($type){
    switch($type){
        case 1:
            $type = '线下充值';
            break;
        case 2:
            $type = '注册赠送';
            break;
    }
    return $type;
}

/**
 * 判断$ip是否在$ips里  (true:在，false:不在)
 * @param $ip
 * @param $ips
 * @return bool
 */
function checkIp($ip, $ips){
    $ip_list = explode(',', $ips);
    if(in_array($ip, $ip_list)){
        return true;
    }

    //ip带*  例如 127.0.*.* 127.0.0.*
    $ipregexp = implode('|', str_replace( array('*','.'), array('\d+','\.') ,$ip_list));
    $rs       = preg_match("/^(".$ipregexp.")$/", $ip);
    if($rs) return true;

    //ip段 例如127.0.0.2~10
    if(strpos($ips,'~') !== false){
        foreach ($ip_list as $v){
            if(strpos($v,'~') !== false){
                $new_ip   = explode('~', $v);
                $start_ip = $new_ip[0];
                $end_ip   = substr_replace($start_ip, $new_ip[1], strrpos($start_ip,'.')+1);
                $ip       = get_ip2long($ip);
                $start_ip = get_ip2long($start_ip);
                $end_ip   = get_ip2long($end_ip);
                if($ip >= $start_ip && $ip <= $end_ip){
                    return true;
                }
            }
        }
    }

    return false;

}

function get_ip2long($ip){
    return bindec(decbin(ip2long($ip)));
}

function readOneFile($path)
{
    if ($handle = fopen($path, 'r')) {
        while (!feof($handle)) {
            yield trim(fgets($handle));
        }
        fclose($handle);
    }
}