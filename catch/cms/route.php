<?php
// +----------------------------------------------------------------------
// | Catch-CMS Design On 2020
// +----------------------------------------------------------------------
// | CatchAdmin [Just Like ～ ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2020 http://catchadmin.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://github.com/yanwenwu/catch-admin/blob/master/LICENSE.txt )
// +----------------------------------------------------------------------
// | Author: JaguarJack [ njphper@gmail.com ]
// +----------------------------------------------------------------------

/* @var $router \think\Route */
$router->group('cms', function () use ($router){
    //公告
    $router->resource('notices', '\catchAdmin\cms\controller\Notices');
    // 公告切换状态
    $router->put('notices/switch/status/<id>', '\catchAdmin\cms\controller\Notices@switchStatus');
    //轮播图
    $router->resource('banners', '\catchAdmin\cms\controller\Banners');
    // 轮播图切换状态
    $router->put('banners/switch/status/<id>', '\catchAdmin\cms\controller\Banners@switchStatus');
})->middleware('auth');
