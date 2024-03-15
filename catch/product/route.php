<?php
// you should use `$router`
/* @var think\Route $router */

$router->group('product', function () use ($router) {
    //产品
    $router->post('products/editStatus', '\catchAdmin\product\controller\Index@editStatus');// 切换状态
    $router->get('products/read', '\catchAdmin\product\controller\Index@read');
    $router->post('products/save', '\catchAdmin\product\controller\Index@save');
    $router->post('products/update', '\catchAdmin\product\controller\Index@update');
    $router->post('products/delete', '\catchAdmin\product\controller\Index@delete');
    $router->resource('products', '\catchAdmin\product\controller\Index');  //产品列表


    //产品分类
    /*$router->get('category/index', '\catchAdmin\product\controller\Category@index');      // 分类列表
    $router->post('category/save', '\catchAdmin\product\controller\Category@save');  // 添加分类
    $router->post('category/update', '\catchAdmin\product\controller\Category@update');*/  // 修改分类

    // api
    $router->get('apis', '\catchAdmin\product\controller\Api@index');  //  接口列表
    //$router->post('api/save', '\catchAdmin\product\controller\Api@save');  //  添加接口
    $router->get('api/detail', '\catchAdmin\product\controller\Api@detail');  //  接口详情
    $router->post('api/update', '\catchAdmin\product\controller\Api@update');  // 修改接口
    //$router->post('api/delete', '\catchAdmin\product\controller\Api@delete');  // 删除
    $router->get('code', '\catchAdmin\product\controller\Api@getCode');  //接口状态码

    // 会员管理
    $router->post('member/save', '\catchAdmin\product\controller\Member@save');  //  添加会员
    $router->get('member/read', '\catchAdmin\product\controller\Member@read');  //  会员详情
    $router->post('member/editpwd', '\catchAdmin\product\controller\Member@editpwd');  //  重置密码
    $router->post('member/editwallet', '\catchAdmin\product\controller\Member@editWallet');  //  修改余额
    $router->post('member/editname', '\catchAdmin\product\controller\Member@editname');  // 修改会员
    $router->post('member/delete', '\catchAdmin\product\controller\Member@delete');  // 删除会员
    $router->get('member/index', '\catchAdmin\product\controller\Member@index');  // 会员列表
    $router->put('member/switch/status/<id>', '\catchAdmin\product\controller\Member@switchStatus');// 切换状态

    // 登录日志列表
    $router->resource('memberLoginLog', '\catchAdmin\product\controller\MemberLoginLog');

    // 充值记录
    $router->resource('memberCharge', '\catchAdmin\product\controller\MemberCharge'); //充值记录列表
    $router->post('memberCharge/verify', '\catchAdmin\product\controller\MemberCharge@verify'); //充值审核
    $router->post('memberCharge/editRemark', '\catchAdmin\product\controller\MemberCharge@editRemark'); //修改备注
    //$router->resource('memberCharge/chargeLogList', '\catchAdmin\product\controller\MemberCharge@chargeLogList'); // 充值流水列表


    //银行卡
    $router->post('bank/save', '\catchAdmin\product\controller\Bank@save');  //  添加银行卡
    $router->get('bank/read', '\catchAdmin\product\controller\Bank@read');  //  银行卡详情
    $router->post('bank/update', '\catchAdmin\product\controller\Bank@update');  // 修改银行卡
    $router->post('bank/delete', '\catchAdmin\product\controller\Bank@delete');  // 删除银行卡
    $router->resource('banks', '\catchAdmin\product\controller\Bank');  //  银行卡列表
    $router->post('bank/editStatus', '\catchAdmin\product\controller\Bank@editStatus');// 切换状态
    // apiLog
    $router->get('apiLog', '\catchAdmin\product\controller\ApiLog@index'); // 调用记录
    $router->get('apiLogDay', '\catchAdmin\product\controller\ApiLog@dayLog'); // 日消统计
    $router->get('apiLogMonth', '\catchAdmin\product\controller\ApiLog@monthLog'); // 月消统计

    // 交易记录流水
    $router->get('walletLog', '\catchAdmin\product\controller\WalletLog@index'); //明细
    $router->get('walletLogCount', '\catchAdmin\product\controller\WalletLog@countList'); // 统计

    //IP白名单
    $router->resource('ips', '\catchAdmin\product\controller\MemberIp');
    $router->post('ips/update', '\catchAdmin\product\controller\MemberIp@update');

    //首页统计
    $router->get('dashboard/countData', '\catchAdmin\product\controller\Dashboard@countData');
    $router->get('dashboard/chartData', '\catchAdmin\product\controller\Dashboard@chartData');

})->middleware('auth');
