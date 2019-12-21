<?php

namespace app\adminapi\controller;

use app\common\model\Admin;
use tools\jwt\Token;

class Login extends BaseApi
{
    # 登录接口
    public function login()
    {
        $param = input();
        $validate = $this->validate($param,[
            'username' => 'require',
            'password' => 'require',
            'code' => 'require',
            'uniqid' => 'require',
        ]);
        if($validate !== true){
            $this->fail($validate);
        }

        # 进行验证码校验
        if(!captcha_check($param['code'],$param['uniqid'])){
            # 验证码错误
            $this->fail('验证码错误');
        }

        # 根据用户名和密码查询管理员表
        $where = [
            'username' =>  $param['username'],
            'password' =>  encrypt_password($param['password'])
        ];
        $info = Admin::where($where)->find();
        if(!$info){
            $this->fail('用户名或密码错误');
        }

        $data['token'] = Token::getToken($info->id);
        $data['user_id'] = $info->id;
        $data['username'] = $info->username;
        $data['nickname'] = $info->nickname;
        $data['email'] = $info->email;

        $this->ok($data);
    }

    public function logout()
    {
            # 清空token 将需要清空的token存入缓存，再次使用时，会读取缓存进行判断
        $token = Token::getRequestToken();
        $delete_token = cache('delete_token') ?: [];
        $delete_token[] = $token;
        cache('delete_token',$delete_token,86400);
        $this->ok();
    }

    # 获取验证码图片地址
    public function verify()
    {
        # 验证码标识
        $uniqid = uniqid(mt_rand(100000,999999),true);
        # 返回数据 验证码图片路径、验证码标识
        $data = [
            'src' => captcha_src($uniqid),
            'uniqid' => $uniqid
        ];
        $this->ok($data);
    }
}
