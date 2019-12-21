<?php

namespace app\common\model;

use think\Model;

class Profile extends Model
{
    public function admin(){
//        return $this->belongsTo('Admin','uid','id')
//            ->bind('username');
//        return $this->belongsTo('Admin','uid','id')
//            ->bind('username,password');
        return $this->belongsTo('Admin','uid','id')
            ->bind(['username','password']);
        return $this->belongsTo('Admin','uid','id')
            ->bind(['a'=>'username','b'=>'password']);
    }
}
