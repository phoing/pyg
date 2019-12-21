<?php

namespace app\common\model;

use think\Model;

class Footmark extends Model
{
    //
    public function goods()
    {
        return $this->hasOne('Goods','id','goods_id');
    }
}
