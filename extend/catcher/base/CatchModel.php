<?php
declare(strict_types=1);

namespace catcher\base;

use catcher\CatchQuery;
use catcher\CatchUpload;
use catcher\traits\db\BaseOptionsTrait;
use catcher\traits\db\RewriteTrait;
use catcher\traits\db\WithTrait;
use think\Model;
use think\model\concern\SoftDelete;
use catcher\traits\db\ScopeTrait;

/**
 *
 * @mixin CatchQuery
 * Class CatchModel
 * @package catcher\base
 */
abstract class CatchModel extends \think\Model
{
    use SoftDelete, BaseOptionsTrait, ScopeTrait, RewriteTrait, WithTrait;

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    protected $deleteTime = 'deleted_at';

    protected $lastLoginTime = 'last_login_time';

    protected $defaultSoftDelete = 0;

    protected $autoWriteTimestamp = true;

    // 分页 Limit
    public const LIMIT = 10;
    // 开启
    public const ENABLE = 1;
    // 禁用
    public const DISABLE = 2;

    /**
     * 是否有 field
     *
     * @time 2020年11月23日
     * @param string $field
     * @return bool
     */
    public function hasField(string $field)
    {
        return property_exists($this, 'field') && in_array($field, $this->field);
    }

    public function __construct(array $data = [])
    {
        parent::__construct($data);


        if (method_exists($this, 'autoWithRelation')) {
            $this->autoWithRelation();
        }
    }

    public static function onAfterRead($data)
    {
        //member_charge
        if(!empty($data['images']) && substr($data['images'], 0,4) != 'http'){
            $data['images'] = CatchUpload::getCloudDomain('local') . $data['images'];
        }
        //cms_banners
        elseif(!empty($data['banner_img']) && substr($data['banner_img'], 0,4) != 'http'){
            $data['banner_img'] = CatchUpload::getCloudDomain('local') . $data['banner_img'];
        }
        //product
        elseif(!empty($data['icon'])
            && substr($data['icon'], 0,4) != 'http'
            && in_array(strtolower(substr($data['icon'], -4)), ['.jpg','jpeg','.gif', '.png'])){
            $data['icon'] = CatchUpload::getCloudDomain('local').$data['icon'];
        }
        //config
        elseif(!empty($data['value'])
            && substr($data['value'], 0,4) != 'http'
            && in_array(strtolower(substr($data['value'], -4)), ['.jpg','jpeg','.gif', '.png'])
        ){
            $data['value'] = CatchUpload::getCloudDomain('local').$data['value'];
        }
        //users
        elseif(!empty($data['avatar']) && substr($data['avatar'], 0,4) != 'http'){
            $data['avatar'] = CatchUpload::getCloudDomain('local').$data['avatar'];
        }
        return $data;
    }

    public static function onBeforeInsert($data)
    {
        //member_charge
        if(!empty($data['images']) && substr($data['images'], 0,4) == 'http'){
            $data['images'] = str_replace(CatchUpload::getCloudDomain('local'),'', $data['images']);
        }
        //cms_banners
        elseif(!empty($data['banner_img']) && substr($data['banner_img'], 0,4) == 'http'){
            $data['banner_img'] = str_replace(CatchUpload::getCloudDomain('local'), '', $data['banner_img']);
        }
        //product
        elseif(!empty($data['icon'])
            && substr($data['icon'], 0,4) == 'http'
            && in_array(strtolower(substr($data['icon'], -4)), ['.jpg','jpeg','.gif', '.png'])
        ){
            $data['icon'] = str_replace(CatchUpload::getCloudDomain('local'), '', $data['icon']);
        }
        //config
        elseif(!empty($data['value'])
            && substr($data['value'], 0,4) == 'http'
            && in_array(strtolower(substr($data['value'], -4)), ['.jpg','jpeg','.gif', '.png'])
        ){
            $data['value'] = str_replace(CatchUpload::getCloudDomain('local'), '', $data['value']);
        }
        //users
        elseif(!empty($data['avatar']) && substr($data['avatar'], 0,4) == 'http'){
            $data['avatar'] = str_replace(CatchUpload::getCloudDomain('local'), '', $data['avatar']);
        }
        return $data;
    }

    public static function onBeforeUpdate($data)
    {
        self::onBeforeInsert($data);
    }
    public static function onBeforeWrite($data)
    {
        self::onBeforeInsert($data);
    }
}
