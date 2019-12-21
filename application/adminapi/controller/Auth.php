<?php

namespace app\adminapi\controller;

use app\common\model\Role;
use think\Collection;
use think\Controller;
use think\Request;
use app\common\model\Auth as AuthModel;
use app\common\model\Admin;

class Auth extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        # 接收参数
        $params = input();
        # 查询数据
        $list = AuthModel::select();
        $list = (new Collection($list))->toArray();
        if(isset($params['type']) && $params['type'] == 'tree'){
            $list = get_tree_list($list);
        }else{
            $list = get_cate_list($list);
        }
        $this->ok($list);
    }
    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        #接收数据
        $params = input();
        #参数检测
        $validate = $this->validate($params,[
            'auth_name|权限名称' => 'require',
            'pid|上级权限' => 'require|integer|>=:0',
            'is_nav|是否菜单权限' => 'require|in:0,1',
        ]);
        if($validate !== true) $this->fail($validate);
        #添加数据（是否顶级，级别和pid_path处理）
        if($params['pid'] == 0){
            $params['level'] = 0;
            $params['pid_path'] = 0;
        }else{
            $p_info = AuthModel::find($params['pid']);
            $params['level'] = $p_info['level'] + 1;
            $params['pid_path'] = $p_info['pid_path'] . '_' . $p_info['id'];
        }
        $auth = AuthModel::create($params,true);
        #返回数据
        $this->ok($auth);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $auth = AuthModel::field('id,auth_name,pid,pid_path,auth_c,auth_a,is_nav,level')->find($id);
        $this->ok($auth);
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
        #接收数据
        $params = input();
        #参数检测
        $validate = $this->validate($params,[
            'auth_name|权限名称' => 'require',
            'pid|上级权限' => 'require|integer|>=:0',
            'is_nav|是否菜单权限' => 'require|in:0,1',
        ]);
        if($validate !== true) $this->fail($validate);
        #修改数据（是否顶级，级别和pid_path处理）
        if($params['pid'] == 0){
            $params['level'] = 0;
            $params['pid_path'] = 0;
        }else{
            $p_info = AuthModel::find($params['pid']);
            if(!$p_info) $this->fail('数据异常');
            $params['level'] = $p_info['level'] + 1;
            $params['pid_path'] = $p_info['pid_path'] . '_' .$p_info['id'];
            # 不能降级
            $info = AuthModel::find($id);
            if(!$info) $this->fail('数据异常');
            $info['level'] < $params['level'] && ($this->fail('不能降级'));
        }
        AuthModel::update($params,['id'=>$id],true);
        #返回数据
        $auth = AuthModel::find($id);
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
        #权限下是否有子权限
        $total = AuthModel::where('pid',$id)->count('id');
        if($total > 0) $this->fail('权限下有子权限，不能删除');
        #删除数据
        AuthModel::destroy($id);
        #返回数据
        $this->ok();

    }

    /**
     *  菜单权限接口
     */
    public function nav()
    {
        $user_id = input('user_id');
        $info = Admin::find($user_id);
        $role_id = $info['role_id'];
        if($role_id == 1){
            $list = AuthModel::where('is_nav',1)->select();
        }else{
            $role = Role::find($role_id);
            $role_auth_ids = $role['role_auth_ids'];
            $list = AuthModel::where('id','in',$role_auth_ids)->where('is_nav',1)->select();
        }
        $data = (new Collection($list))->toArray();
        $data = get_tree_list($data);
        $this->ok($data);
    }
}
