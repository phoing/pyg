<?php
namespace app\adminapi\controller;

use think\Db;
use tools\jwt\Token;
use app\common\model\Profile;
use app\common\model\Admin;
use app\common\model\Category;
use app\common\model\Brand;

class Index extends BaseApi
{
    public function index()
    {
        $info = Brand::with('category')->find(1);
        $this->ok($info);
        $info = Category::with('brands')->find(72);
        $this->ok($info);
        $info = Category::with('brands')->select();
        $this->ok($info);
        $info = Admin::with('profile')->find(1);
        $this->ok($info);
        $info = Profile::with('admin')->find(1);
        $this->ok($info);
//        return 'adminapi index';
//        $goods = Db::table('pyg_goods')->find();
//        dump($goods);
//        $this->response(200,'success',$goods);
//        $this->ok($goods);
//        $this->fail();
        # 生成Token
//        $token = Token::getToken(100);
//        dump($token);
        # 从token获取用户id
//        $user_id = Token::getUserId($token);
//        dump($user_id);
    }
}
