<?php

namespace app\common\model;

use think\Model;

class Collect extends Model
{
    //
    public function goods()
    {
        return $this->hasOne('Goods','id','goods_id');
    }
}
