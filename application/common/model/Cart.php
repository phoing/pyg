<?php

namespace app\common\model;

use think\Model;

class Cart extends Model
{
    public function goods()
    {
        return $this->belongsTo('Goods','goods_id','id');
    }

    public function specGoods()
    {
        return $this->belongsTo('SpecGoods','spec_goods_id','id');
    }
}
