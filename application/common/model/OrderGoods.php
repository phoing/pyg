<?php

namespace app\common\model;

use think\Model;

class OrderGoods extends Model
{
    //
    public function getCreateTimeAttr($value)
    {
        return $value;
    }
    public function getDeleteTimeAttr($value)
    {
        return $value;
    }
    public function getUpdateTimeAttr($value)
    {
        return $value;
    }
}
