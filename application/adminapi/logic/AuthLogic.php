<?php
namespace app\adminapi\logic;

use app\admin\model\Role;
use app\common\model\Admin;
use app\common\model\Auth;

class AuthLogic{
    public static function check()
    {
        $controller = request()->controller();

        $action = request()->action();

        $path = strtolower($controller . '/' . $action);

        if(in_array($path,['login/login','login/captcha','login/verify','index/index','login/logout'])) return true;

        $user_id = input('user_id');
        if(!$user_id) return false;
        $admin = Admin::find($user_id);
        $role_id = $admin['role_id'];
        if($role_id == 1) return true;

        $role = Role::find($role_id);
        $role_auth_ids = explode(',',$role['role_auth_ids']);
        $auth = Auth::where('auth_c',$controller)->where('auth_a',$action)->find();
        $auth_id = $auth['id'];
        if(in_array($auth_id,$role_auth_ids)) return true;
        return false;
    }
}