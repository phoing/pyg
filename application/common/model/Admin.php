<?php

namespace app\common\model;

use think\Model;

class Admin extends Model
{
    public function profile(){
        return $this->hasOne('Profile','uid','id');
        return $this->hasOne('Profile','uid','id')
            ->bind('idnum');
    }

    public function roleBind()
    {
        return $this->belongsTo('Role','role_id','id')->bind('role_name');
    }

    public function setPasswordAttr($value)
    {
        return encrypt_password($value);
    }
}
