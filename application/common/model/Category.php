<?php

namespace app\common\model;

use think\Model;
use traits\model\SoftDelete;

class Category extends Model
{
    use SoftDelete;
//    protected $table = 'pyg_category';
    protected $deleteTime = "delete_time";

    public function brands(){
        return $this->hasMany('Brand','cate_id','id');
    }

    public function getPidPathAttr($value)
    {
        return $value ? explode('_',$value) : [] ;
    }
}
