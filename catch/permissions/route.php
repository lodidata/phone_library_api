<?php

/* @var think\Route $router */

$router->group(function () use ($router){
    // 角色
    $router->resource('roles', '\catchAdmin\permissions\controller\Role');
    // 权限
    $router->resource('permissions', '\catchAdmin\permissions\controller\Permission');
    $router->put('permissions/show/<id>', '\catchAdmin\permissions\controller\Permission@show');
    // 部门
    $router->resource('departments', '\catchAdmin\permissions\controller\Department');
    // 所有职位
    $router->get('jobs/all', '\catchAdmin\permissions\controller\Job@getAll');
    // 岗位
    $router->resource('jobs', '\catchAdmin\permissions\controller\Job');
    // 用户
    $router->resource('users', '\catchAdmin\permissions\controller\User');
    // 切换状态
    $router->put('users/switch/status/<id>', '\catchAdmin\permissions\controller\User@switchStatus');
    $router->put('user/profile', '\catchAdmin\permissions\controller\User@profile');
    $router->get('user/info', '\catchAdmin\permissions\controller\User@info');
    $router->get('user/export', '\catchAdmin\permissions\controller\User@export');

    $router->post('user/editPwd', '\catchAdmin\permissions\controller\User@editpwd');
    $router->post('user/resetPwd', '\catchAdmin\permissions\controller\User@resetPwd');
    $router->put('user/save', '\catchAdmin\permissions\controller\User@save');

})->middleware('auth');
