<?php

namespace app\home\controller;

use app\common\model\OpenUser;
use app\common\model\User;
use app\home\logic\CartLogic;
use app\home\logic\CollectLogic;
use app\home\logic\FootmarkLogic;
use think\Controller;

class Login extends Controller
{
    #显示登陆页面
    public function login()
    {
        $this->view->engine->layout(false);
        return view();
    }
    public function dologin()
    {
        $params = input();

        $validate = $this->validate($params,[
            'username|用户名' => 'require',
            'password|密码' => 'require',
        ]);

        if($validate !== true) $this->error($validate);

        $password = encrypt_password($params['password']);

        $user = User::where(function($query)use($params){
            $query->where('phone',$params['username'])->whereOr('email',$params['username']);
        })->where('password',$password)->find();

        if($user){
            session('user_info',$user->toArray());
            CartLogic::cookieToDb();
            FootmarkLogic::cookieToDb();
            CollectLogic::cookieToDb();
            $open_user_id = session('open_user_id');
            # 关联第三方用户
            if($open_user_id){
                OpenUser::update(['user_id'=>$user['id'],'id'=>$open_user_id],true);
                session('open_user_id',null);
            }
            # 同步昵称到用户表
            $nickname = session('open_user_nickname');
            if($nickname){
                User::update(['nickname'=>$nickname],['id'=>$user['id']],true);
                session('open_user_nickname',null);
            }
            $back_url = session('back_url') ?: 'home/index/index';
            $this->redirect($back_url);
        }else{
            $this->error('用户名或密码错误');
        }

    }
    # 退出登录
    public function logout()
    {
        session(null);
        $this->redirect('home/login/login');
    }
    # 显示注册页面
    public function register()
    {
        $this->view->engine->layout(false);
        return view();
    }

    # 简单注册-手机号码
    public function phone()
    {
        $params = input();

        $validate = $this->validate($params,[
            'phone|手机号码' => 'require|regex:1[3-9]\d{9}|unique:user,phone',
            'password|密码' => 'require|length:6,20|confirm:repassword',
            'code|短信验证码' => 'require'
        ]);

        if($validate !== true) $this->error($validate);
        $code = cache('register_code_' . $params['phone']);
        if($code != $params['code']) $this->error('验证码错误');

        cache('register_code_' . $params['phone'],null);
        cache('register_time_' . $params['phone'],null);

        $params['username'] = $params['phone'];
        $params['nickname'] = encrypt_phone($params['phone']);
        $params['password'] = encrypt_password($params['password']);

        User::create($params,true);

        $this->success('注册成功','home/login/login');
    }
    # 发送短信验证码
    public function sendcode()
    {
        $params = input();

        $validate = $this->validate($params,[
            'phone|手机号码' => 'require|regex:1[3-9]\d{9}',
        ]);
        if($validate !== true){
            return json(['code' => 400,'msg' => $validate]);
        }
        $last_time = cache('register_time_' . $params['phone']) ?: 0;
        if(time() - $last_time < 60){
            return json(['code' => 500,'msg' => '发送太频繁']);
        }
        $code = mt_rand(1000,9999);
        $msg = '【创信】你的验证码是：' . $code . '，3分钟内有效！';

        $res = true;
//        $res = send_msg($params['phone'],$msg);
        if($res){
            cache('register_code_' . $params['phone'],$code,180);
            cache('register_time_' . $params['phone'],time(),180);
            return json(['code' => 200, 'msg' => '短信发送成功', 'data' => $code]);
        }else{
            return json(['code' => 401, 'msg' => $res]);
        }
    }

    public function qqcallback()
    {
        require_once("./plugins/qq/API/qqConnectAPI.php");
        $qc = new \QC();
        $access_token =  $qc->qq_callback();
        $openid =  $qc->get_openid();
        $qc = new \QC($access_token,$openid);
        $info = $qc->get_user_info();
//        dump($info);die;
        $open_user = OpenUser::where('open_type','qq')->where('openid',$openid)->find();
        # 如果已经关联过用户直接登录
        if($open_user && $open_user['user_id']){
            # 更新用户名
            $user = User::find($open_user['user_id']);
            $user->nickname = $info['nickname'];
            $user->save();

            session('user_info',$user->toArray());
            CartLogic::cookieToDb();
            FootmarkLogic::cookieToDb();
            CollectLogic::cookieToDb();
            $back_url = session('back_url') ?: 'home/index/index';
            $this->redirect($back_url);
        }
        # 如果查询不到
        if(!$open_user){
            # 第一次登录，没有记录，添加一条记录到open_user表
            $open_user = OpenUser::create(['open_type'=>'qq','openid'=>$openid]);
        }
        session('open_user_id',$open_user['id']);
        $this->redirect('home/login/login');
    }
    # 支付宝第三方登录
    public function alicallback()
    {
        require_once('./plugins/alipay/oauth/service/AlipayOauthService.php');
        require_once('./plugins/alipay/oauth/config.php');
        $AlipayOauthService = new \AlipayOauthService($config);
        # 获取auth_code
        $auth_code = $AlipayOauthService->auth_code();
        # 获取access_token
        $access_token = $AlipayOauthService->get_token($auth_code);
        # 获取用户信息 user_id nick_name
        $info = $AlipayOauthService->get_user_info($access_token);
        $openid = $info['user_id'];
//        dump($info);die;
        $open_user = OpenUser::where('open_type','alipay')->where('openid',$openid)->find();
        if($open_user && $open_user['user_id']){
            # 已经关联
            $user = User::find($open_user['user_id']);
            # 同步信息
            $user->nickname = $info['nick_name'];
            $user->save();
            # 设置登录标识
            session('user_info',$user->toArray());
            CartLogic::cookieToDb();
            FootmarkLogic::cookieToDb();
            CollectLogic::cookieToDb();
            $back_url = session('back_url') ?: 'home/index/index';
            $this->redirect($back_url);
        }
        if(!$open_user){
            # 第一次登录，没有记录 添加一条记录到open_user表
            OpenUser::create(['open_type'=>'alipay','openid'=>$openid]);
        }
        session('open_type','alipay');
        session('open_user_id',$open_user['id']);
        session('open_user_nickname',$info['nick_name']);
        $this->redirect('home/login/login');
    }
}
