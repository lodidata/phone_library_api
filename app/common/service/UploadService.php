<?php


namespace app\common\service;


use catcher\base\CatchRequest;
use catcher\CatchUpload;
use catcher\exceptions\FailedException;

class UploadService
{
    public function image($imgs)
    {
        $images = array_values($imgs);
        $upload = new CatchUpload();

        if (!count($images)) {
            throw new FailedException('请选择图片上传');
        }
        $res = $upload->checkImages($images)->setDriver('local')->setPath('charge')->multiUpload(
            count($images) < 2 ? $images[0] : $images
        );
        return $res;
    }

    public function file($files, $path = 'check')
    {
        $files = array_values($files);
        $upload = new CatchUpload();

        if (!count($files)) {
            throw new FailedException('请选择文件上传');
        }
        $res = $upload->checkFiles($files)->setDriver('local')->setPath($path)->multiUpload(
            count($files) < 2 ? $files[0] : $files
        );
        return $res;
    }
}