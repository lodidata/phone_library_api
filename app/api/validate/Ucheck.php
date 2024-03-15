<?php


namespace app\api\validate;

use think\Validate;
class Ucheck extends Validate
{
    protected $rule =   [
        'file_name'      => 'require|max:25',
        'new_file_name'  => 'require|max:120',
    ];
    protected $message  =   [
        'file_name.require'     => '文件名不能为空',
        'new_file_name.require' => '新文件名不能为空',
        'file_name.max'         => '文件名最长不能超过25个字符',
        'new_file_name.max'     => '新文件名最长不能超过120个字符',
    ];

}