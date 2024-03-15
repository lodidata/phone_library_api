<?php


namespace app\api\controller;
use app\common\service\IndexService;
use app\common\service\ConfigService;
use app\common\service\UploadService;
use app\common\model\CmsNotices as CmsNoticesModel;
use app\common\model\CmsBanners as CmsBannersModel;
use catcher\CatchUpload;
use think\facade\Request;


class Index extends Base
{
    /*
     * 首页-使用统计
     */
    function getUsage(){
        $id           = $this->member['id'];
        $indexService = new IndexService();
        $info         = $indexService->getUsage($id);

        exit(ajaxReturn('success',$info));
    }

    function getContact(){
        $config_service = new ConfigService();
        $info           = $config_service->getContact('contact');

        exit(ajaxReturn('success',$info));
    }

    /**
     * 上传图片
     */
    function uploadImg(){
        $img            = Request::file();
        $upload_service = new UploadService();

        try{
            $url         = $upload_service->image($img);
        }catch (\Exception $e){
            exit(ajaxReturn($e->getMessage()));
        }

        exit(ajaxReturn('success',['url' => $url]));
    }

    /**
     * 上传文件
     */
    function uploadFile(){
        $file            = Request::file();
        $upload_service = new UploadService();

        try{
            $url         = $upload_service->file($file,"check/{$this->member['id']}");
            $url         = str_replace(CatchUpload::getCloudDomain('local'),'',$url);

            if(count($file) < 2){
                $file_name   = current($file)->getOriginalName();
                if(strlen($file_name) > 25){
                    exit(ajaxReturn('文件名不能大于20个字符'));
                }
                $file_name = [$file_name];
                $url       = [$url];
            }else{
                foreach ($file as $v){
                    $filName   = $v->getOriginalName();
                    if(strlen($filName) > 25){
                        exit(ajaxReturn('文件名不能大于20个字符'));
                    }
                    $file_name[]   = $filName;
                }
            }

        }catch (\Exception $e){
            exit(ajaxReturn($e->getMessage()));
        }

        exit(ajaxReturn('success',['file_name' => $file_name,'new_file_name' => $url]));
    }

    /**
     * 公共列表
     */
    function getNoticeList(){
        $notice_model = new CmsNoticesModel();
        $list         = $notice_model->getNoticeList();
        exit(ajaxReturn('success',$list));
    }

    /**
     * 获取网站配置
     */
    function getSite(){
        $config_service = new ConfigService();
        $info           = $config_service->getContact('basic',['site']);
        if($info){
            foreach ($info['site'] as $k => &$v){
                if(strpos($v,'/') !== false){
                    $v = CatchUpload::getCloudDomain('local').$v;
                }
            }
            unset($v);
        }
        exit(ajaxReturn('success',$info));
    }

    /**
     * 获取轮播图
     */
    function getBanner(){
        $banner_model = new CmsBannersModel();
        $list         = $banner_model->getBannersList();
        exit(ajaxReturn('success',$list));
    }

}