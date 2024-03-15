<?php


namespace app\api\controller;
use app\common\service\CategoryService;

class Category extends Base
{

    /*
     * 获取所有产品分类列表
     */
    function index(){

        $categoryService = new CategoryService();
        $list = $categoryService->getCategoryList();

        exit(ajaxReturn('success',$list));
    }
}