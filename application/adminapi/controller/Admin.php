<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;
use app\common\model\Brand;
use app\common\model\Admin as AdminModel;

class Admin extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $params = input();
        $where = [];
        if(isset($params['keyword']) && !empty($params['keyword'])){
            $keyword = $params['keyword'];
            $where['username'] = ['like',"%{$keyword}%"];
        }
        $size = isset($params['size']) ? (int)$params['size'] : 10;
        $list = AdminModel::with('role_bind')->where($where)->paginate($size);
        $this->ok($list);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $params = input();
        $validate = $this->validate($params,[
            'username|用户名' => 'require|unique:admin',
            'email|邮箱' => 'require|email',
            'role_id|角色' => 'require|integer|gt:0',
            'password|密码' => 'length:6,20',
        ]);
        if($validate !== true) $this->fail($validate);

        if(!isset($params['password']) || empty($params['password'])){
            $params['password'] = '123456';
        }
        $admin = AdminModel::create($params,true);
        $info = AdminModel::find($admin['id']);
        $this->ok($info);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $info = AdminModel::find($id);
        $this->ok($info);
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        if($id == 1) $this->fail('无权修改此管理员');
        $params = input();
        if(isset($params['type']) && $params['type']=='reset_pwd'){
            $params['password'] = '123456';
        }else{
            $validate = $this->validate($params,[
                'email|邮箱' => 'email',
                'role_id|角色' => 'integer|gt:0',
                'nickname|昵称' => 'length:2,20',
            ]);
            if($validate !== true) $this->fail($validate);
            if(isset($params['password'])){
                unset($params['password']);
            }
        }
        if(isset($params['username'])){
            unset($params['username']);
        }

        AdminModel::update($params,['id'=>$id],true);
        $info = AdminModel::find($id);
        $this->ok($info);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if($id == 1) $this->fail('无权删除此管理员');

        $info = AdminModel::find($id);

        if(!$info) $this->ok();

        if($info['role_id'] == 1) $this->fail('无权删除此管理员');

        if($id == input('user_id')) $this->fail('不能删除自己');

        AdminModel::destroy($id);

        $this->ok();
    }
}
