<?php

namespace app\home\controller;

use app\common\model\Address;
use app\common\model\Collect;
use app\common\model\Footmark;
use app\common\model\User;
use app\common\model\Order;
use app\home\logic\CollectLogic;
use think\Controller;

class Member extends Base
{
    /**
     * 个人中心-基本资料
     * @return \think\response\View
     */
    public function info()
    {
        if(!session("?user_info")){
            session("back_url","home/member/info");
            $this->redirect('home/login/login');
        }
        $user = session("user_info");
        # 获取生日 年 月 日
        $birthday = session("user_info.birthday");
        $bir_arr = explode('-',$birthday);
        $user['year'] = !empty($bir_arr[0]) ? $bir_arr[0] : 1990;
        $user['month'] = !empty($bir_arr[0]) ? $bir_arr[1] : 1;
        $user['day'] = !empty($bir_arr[0]) ? $bir_arr[2] : 1;
        # 获取地区 省 市 区
        $area_arr = explode(' ',session('user_info.area'));
        $user['state'] = $area_arr[0];
        $user['city'] = $area_arr[1];
        $user['district'] = $area_arr[2];
//        dump(session("user_info"));die;
        return view('info',['user'=>$user,'listActive'=>'info']);
    }

    /**
     * 个人中心-设置-地址管理
     * 2019年12月13日20:49:09
     */
    public function address()
    {
        if(!session("?user_info")){
            session("back_url","home/member/address");
            $this->redirect('home/login/login');
        }
        $list = Address::where(['user_id'=>session('user_info.id')])->select();
        foreach ($list as $k=>$v){
            $list[$k]['phone'] = encrypt_phone($list[$k]['phone']);
        }
        return view('address',['list'=>$list,'listActive'=>'address']);
    }

    /**
     * 个人中心-设置-添加地址
     * 2019年12月14日09:26:31
     */
    public function addAddress()
    {
        if(request()->isGet()) $this->error('禁止访问');

        $params = input();
        $validate = $this->validate($params,[
            'consignee|收货人' => 'require',
            'phone|手机号码' => 'require|regex:1[3-9]\d{9}',
            'address|详细地址' => 'require',
        ]);
        if($validate !== true){
            $data['code'] = 400;
            $data['msg'] = $validate;
            return json($data);
        }
        $params['user_id'] = session('user_info.id');
        $params['is_default'] = 0;
        Address::create($params,true);
        $data['code'] = 200;
        $data['msg'] = '添加成功';
        return json($data);
    }

    /**
     * 个人中心-设置-默认地址
     * 2019年12月14日10:03:58
     */
    public function setDefault($id)
    {
        if(!session("?user_info")){
            session("back_url","home/member/address");
            $this->redirect('home/login/login');
        }
        Address::update(['is_default'=>0],['user_id'=>session("user_info.id"),'is_default'=>1]);
        Address::update(['is_default'=>1],['id'=>$id]);
        $this->redirect('home/member/address');
    }

    /**
     * 个人中心-设置-删除地址
     * 2019年12月14日10:14:30
     */
    public function delAddress($id)
    {
        if(!session("?user_info")){
            session("back_url","home/member/address");
            $this->redirect('home/login/login');
        }
        Address::destroy(['id'=>$id,'user_id'=>session('user_info.id')]);
        $this->redirect('home/member/address');
    }

    /**
     * 个人中心-设置-获取信息
     * 2019年12月14日10:29:37
     */
    public function getAddress()
    {
        if(request()->isGet()) $this->error('禁止访问');

        $id = input('id');
        $data = Address::find($id)->toArray();
        $area = explode(' ',$data['area']);
        $data['province'] = $area[0];
        $data['city'] = $area[1];
        $data['district'] = $area[2];
        $list['code'] = 200;
        $list['msg'] = '获取成功';
        $list['data'] = $data;
        return json($list);
    }

    /**
     * 个人中心-设置-修改信息
     * 2019年12月14日11:01:38
     */
    public function editAddress()
    {
        if(request()->isGet()) $this->error('禁止访问');

        $params = input();
        $validate = $this->validate($params,[
            'consignee|收货人' => 'require',
            'phone|手机号码' => 'require|regex:1[3-9]\d{9}',
            'address|详细地址' => 'require',
        ]);
        if($validate !== true){
            $data['code'] = 400;
            $data['msg'] = $validate;
            return json($data);
        }
        $id = $params['id'];
        unset($params['id']);
        Address::update($params,['id'=>$id,'user_id'=>session('user_info.id')],true);
        $data['code'] = 200;
        $data['msg'] = '修改成功';
        return json($data);
    }

    /**
     * 个人中心-安全中心
     */
    public function safe()
    {
        if(!session("?user_info")){
            session("back_url","home/member/safe");
            $this->redirect('home/login/login');
        }
        $user = session("user_info");
        $user['phone'] = encrypt_phone($user['phone']);
        return view('safe',['user'=>$user,'listActive'=>'safe']);
    }
    /**
     * 修改密码
     */
    public function editpwd()
    {
        if(!session("?user_info")){
            $this->redirect('home/login/login');
        }
        $params = input();
        $validate = $this->validate($params,[
            'password|密码' => 'require|length:6,20|confirm:confirm_password'
        ]);
        if($validate !== true) $this->error($validate);

        $user = User::where(['id'=>session('user_info.id'),'password'=>$params['oldpassword']])->find();
        if(!$user) $this->error('旧密码错误');

        $data['password'] = encrypt_password($params['password']);
        User::update($data,['id'=>session("user_info.id")],true);
        session(null);
        $this->redirect('home/login/login');
    }

    /**
     * 发送短信验证码
     * 2019年12月13日16:47:06
     */
    public function noteSend()
    {
        # 判断短信验证码发送频率
        $times = cache('member_time_' . session('user_info.phone')) ?: 0;
//        if(time() - $times < 60){
//            $data['code'] = 400;
//            $data['msg'] = '短信发送频率太高！';
//            echo json_encode($data);die;
//        }
        # 判断短信验证码发送频率

        $params = input();
        $validate = $this->validate($params,[
            'captcha' => 'require|captcha'
        ]);
        if($validate !== true){
            $data['code'] = 400;
            $data['msg'] = $validate;
            echo json_encode($data);die;
        }
        $data['code'] = 200;
        $data['msg'] = '短信发送成功，验证码3分钟内有效';
        $data['data'] = mt_rand(1000,9999);

        cache('member_code_' . session('user_info.phone'),$data['data'],180);
        cache('member_time_' . session('user_info.phone'),time(),180);

        return json($data);
    }

    /**
     * 验证短信验证码
     * 2019年12月13日17:06:01
     */
    public function validNote()
    {
        $params = input();

        $code = cache('member_code_' . session('user_info.phone'));
        if($code != $params['note']){
            $data['code'] = 400;
            $data['msg'] = '短信验证码错误！';
            echo json_encode($data);die;
        }
        cache('member_code_' . session('user_info.phone'),null);
        cache('member_time_' . session('user_info.phone'),null);

        $data['code'] = 200;
        $data['msg'] = '短信验证成功！';
        echo json_encode($data);die;
    }

    /**
     * 发送绑定手机号码验证码
     * 2019年12月13日19:43:31
     */
    public function bindPhoneNoteSend()
    {
        $params = input();
        $validate = $this->validate($params,[
            'phone' => 'require|regex:1[3-9]\d{9}|unique:user,phone',
            'code' => 'require|captcha:memberPhone'
        ]);
        if($validate !== true){
            $data['code'] = 400;
            $data['msg'] = $validate;
            return json($data);
        }

        $data['code'] = 200;
        $data['msg'] = '短信发送成功，验证码3分钟内有效';
        $data['data'] = mt_rand(1000,9999);

        cache('member_bind_code_' . session('user_info.phone'),$data['data'],180);
        cache('member_bind_time_' . session('user_info.phone'),time(),180);

        return json($data);
    }

    /**
     * 验证绑定手机短信验证码
     * 2019年12月13日20:13:23
     */
    public function validBindNote()
    {
        $params = input();
        $validate = $this->validate($params,[
            'phone' => 'require|regex:1[3-9]\d{9}|unique:user,phone',
            'note' => 'require'
        ]);
        if($validate !== true){
            $data['code'] = 400;
            $data['msg'] = '短信验证码错误！';
            echo json_encode($data);die;
        }
        cache('member_bind_code_' . session('user_info.phone'),null);
        cache('member_bind_time_' . session('user_info.phone'),null);

        User::update(['phone'=>$params['phone'],'username'=>$params['phone']],['id'=>session('user_info.id')],true);

        $data['code'] = 200;
        $data['msg'] = '短信验证成功！';
        echo json_encode($data);die;
    }

    /**
     * 修改用户基本资料
     * 2019年12月13日10:26:30
     */
    public function save()
    {
        if(!session("?user_info")){
            $this->redirect('home/login/login');
        }
        # 获取参数
        $params = input();
        # 验证参数
        $validate = $this->validate($params,[
            'nickname|昵称' => 'require',
        ]);
        if($validate !== true) $this->error($validate);
        $params['birthday'] = $params['year'] . '-' . $params['month'] . '-' . $params['day'];
        $params['area'] = $params['state'] . ' ' . $params['city'] . ' ' . $params['district'];
        # 更新数据
        User::update($params,['id'=>session('user_info.id')],true);
        # 查询最新数据
        $user = User::get(session('user_info.id'))->toArray();
        # 修改session
        session('user_info',$user);

        $this->redirect('home/member/info');
    }

    /**
     * 上传头像
     * 2019年12月13日11:45:54
     */
    public function upimg()
    {
        if(!session("?user_info")){
            $this->redirect('home/login/login');
        }
        # 获取上传文件
        $file = request()->file('figure_url');
        # 判断是否上传
        if(empty($file)){
            $data['code'] = 400;
            $data['msg'] = '没有上传图片';
            echo json_encode($data);die;
        }
        # 拼接文件路径
        $dir = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'member';
        # 判断路径是否存在
        if(!is_dir($dir)) mkdir($dir);
        # 判断类型/大小 保存文件
        $info = $file->validate([
            'size' => 10*1024*1024,
            'ext' => 'jpg,png,gif,jpeg',
            'type' => 'image/jpeg,image/png,image/gif'
        ])->move($dir);
        if(!$info){
            $data['code'] = 401;
            $data['msg'] = $file->getError();
            echo json_encode($data);die;
        }
        $fileUrl = DS . 'uploads' . DS . 'member' . DS . $info->getSaveName();
        User::update(['figure_url'=>$fileUrl],['id'=>session('user_info.id')]);
        # 删除旧头像
        if(is_file('.' . session('user_info.figure_url'))) unlink('.' .session('user_info.figure_url'));
        # 查询最新数据
        $user = User::get(session('user_info.id'))->toArray();
        # 修改session
        session('user_info',$user);

        $data['code'] = 200;
        $data['msg'] = '上传成功';
        $data['file'] = $fileUrl;
        echo json_encode($data);die;
    }

    /**
     * 个人中心-我的订单
     * 2019年12月14日11:37:49
     */
    public function order()
    {
        $userid = session('user_info.id');
        $list = Order::where(['user_id'=>$userid])->with('orderGoods')->paginate(3);
        foreach ($list as $k => $v){
            $list[$k] = $v->toArray();
        }
//        dump($list);die;
        return view('order',['list'=>$list,'listActive'=>'order']);
    }

    /**
     * 个人中心-我的订单-详情
     * 2019年12月14日17:08:28
     */
    public function orderDetail($id)
    {
        $userid = session('user_info.id');
        $list = Order::where(['user_id'=>$userid,'id'=>$id])->with('orderGoods')->find()->toArray();
//        dump($list);die;
        return view('orderDetail',['list'=>$list,'listActive'=>'orderDetail']);
    }

    /**
     * 个人中心-我的足迹
     * 2019年12月14日19:04:38
     */
    public function footmark()
    {
        $user_id = session("user_info.id");
        $list = Footmark::with('goods')->where(['user_id'=>$user_id])->order('create_time desc')->select();
        foreach ($list as $k => $v) {
            $list[$k] = $v->toArray();
        }
//        dump($list);die;
        return view('footmark',['list'=>$list,'listActive'=>'footmark']);
    }

    /**
     * 个人中心-我的收藏
     * 2019年12月14日20:43:50
     */
    public function collect()
    {
        $user_id = session("user_info.id");
        $list = Collect::with('goods')->where(['user_id'=>$user_id])->order('create_time desc')->select();
        foreach ($list as $k => $v) {
            $list[$k] = $v->toArray();
        }
        return view('collect',['list'=>$list,'listActive'=>'collect']);
    }

    /**
     * 添加收藏
     * 2019年12月14日20:59:07
     */
    public function addCollect()
    {
        $id = input('id');

        CollectLogic::addCollect($id);

        $data['code'] = 200;
        $data['msg'] = '收藏成功';
        echo json_encode($data);die;
    }
}
