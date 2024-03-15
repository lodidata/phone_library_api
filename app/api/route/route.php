<?php

/* @var think\Route $router */
use think\facade\Route;

Route::group(function (){
    # 修改密码
    Route::patch('modify_pwd', 'Login/modifyPassword');
    # ip绑定
    Route::put('bind_ip', 'Member/UpdateIp');
    # ip白名单列表
    Route::get('ip', 'Member/getIp');
    # 退出登录
    Route::post('logout', 'Login/logout');
    # 首页统计数据
    Route::get('index/count', 'index/getUsage');

    # 会员基本信息
    Route::get('member', 'member/index');
    # 联系方式
    Route::get('contact', 'index/getContact');

    # 上传图片
    Route::post('img/upload', 'index/uploadImg');

    # 上传文件
    Route::post('file/upload', 'index/uploadFile');

    # 获取接口状态码
    Route::get('product/get_code', 'product/getCode');
    # 产品列表
    Route::get('product/list', 'Product/getProduct');
    # 产品下的接口列表
    Route::get('product/api_list', 'Product/apiList');
    # 调用产品接口基本信息
    Route::get('product/detail', 'Product/getProductDetail');
    # 接口详情
    Route::get('product/api_detail', 'Product/apiDetail');
    # 空号检测统计
    Route::get('product/ucheck_log', 'product/mobileCheckLog');
    # 删除空号检测记录
    Route::delete('product/ucheck_log/<id>', 'product/delCheckLog');
    # 空号检测统计导出
    Route::get('export/ucheck_log', 'product/exportMobileCheckLog');
    # 日消耗统计(产品)
    Route::get('product/day_count', 'product/dayLog');
    # 月消耗统计(产品)
    Route::get('product/month_count', 'product/monthLog');
    # api调用记录
    Route::get('product/api_log', 'product/getApiLog');
    # 日消耗统计(财务)
    Route::get('finance/day_count', 'finance/dayLog');
    # 月消耗统计(财务)
    Route::get('finance/month_count', 'finance/monthLog');
    # 银行卡列表
    Route::get('bankcard/list', 'finance/bankCardList');
    # 获取财务联系人
    Route::get('finance/contact', 'finance/getContact');
    # 对公充值
    Route::put('finance/deposit', 'finance/deposit');
    # 订单明细
    Route::get('finance/order', 'finance/orderList');
    # 收支明细
    Route::get('finance/count', 'finance/countList');
    # 公共列表
    Route::get('notice', 'index/getNoticeList');

})->middleware(\app\api\middleware\Auth::class);
# 登入
Route::post('login', 'Login/login');
# 获取图片验证码
Route::get('captcha', 'Login/captcha');
# 网站配置
Route::get('index/site', 'index/getSite');
# 轮播图
Route::get('index/banner', 'index/getBanner');

Route::post('open/batch-ucheck', 'open/uCheck')->middleware(\app\api\middleware\OpenAuth::class);
#新 空号单点检测和文件检测
Route::post('open/new-batch-ucheck', 'open/newUCheck')->middleware(\app\api\middleware\OpenAuth::class);
