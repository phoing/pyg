<?php

namespace app\common\model;

use think\Model;

class Brand extends Model
{
    public function category(){
        return $this->belongsTo('Category','cate_id','id')
            ->bind('cate_name');
    }
}
