<?php

namespace app\common\model;

use think\Model;

class Attribute extends Model
{
    # 隐藏属性
    protected $hidden = ['create_time','update_time','delete_time'];

    # 使用获取器方法，对attr_values字段进行转化，get开头 Attr结尾，中间是字段名
    public function getAttrValuesAttr($value)
    {
        return $value ? explode(',',$value) : [];
    }
}
