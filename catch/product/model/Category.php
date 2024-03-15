<?php

namespace catchAdmin\product\model;

use catcher\base\CatchModel as Model;
use catcher\traits\db\BaseOptionsTrait;
use catcher\traits\db\ScopeTrait;

class Category extends Model
{
    use BaseOptionsTrait, ScopeTrait;
    // 表名
    public $name = 'category';
    // 数据库字段映射
    public $field = array(
        'id',
        // 栏目名称
        'name'
    );
}