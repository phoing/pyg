<?php

namespace app\common\model;

use think\Model;

class Order extends Model
{
    //
    public function orderGoods()
    {
        return $this->hasMany('OrderGoods','order_id','id');
    }

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
    public function getPayTimeAttr($value)
    {
        return $value;
    }
}
