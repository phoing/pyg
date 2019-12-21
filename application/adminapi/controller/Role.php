<?php

namespace app\adminapi\controller;

use think\Collection;
use think\Controller;
use think\Request;
use app\common\model\Role as RoleModel;
use app\common\model\Auth;
use app\common\model\Admin;

class Role extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        # 获取所有角色数据列表
        $list = RoleModel::select();
//        dump($list);die;
        # 循环所有角色数据
        foreach ($list as $k => $v){
            # 判断是否是超级管理员 超级管理员默认全部权限，其他按照字段查找权限
            if($v['id'] == 1){
                $where = [];
            }else{
                $where = ['id'=>['in',$v['role_auth_ids']]];
            }
            # 查询单个角色下拥有的权限
            $auth = Auth::where($where)->select();
            # 转化数组结构 为 树状结构
            $auth = (new Collection($auth))->toArray();
            $auth = get_tree_list($auth);
            $list[$k]['role_auths'] = $auth;
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
        # 接收参数
        $params = input();
        # 验证数据
        $validate = $this->validate($params,[
            'role_name|角色名称' => 'require',
            'auth_ids|拥有的权限' => 'require',
        ]);
        if($validate !== true) $this->fail($validate);

        if(is_array($params['auth_ids'])){
            $params['auth_ids'] = implode(',',$params['auth_ids']);
        }
        $params['role_auth_ids'] = $params['auth_ids'];
        $res = RoleModel::create($params,true);
        $info = RoleModel::find($res['id']);
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
        $info = RoleModel::field('id,role_name,desc,role_auth_ids')->find($id);
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
        # 接收参数
        $params = input();
        # 验证数据
        $validate = $this->validate($params,[
            'role_name' => 'require',
            'auth_ids' => 'require',
        ]);
        if($validate !== true) $this->fail($validate);

        $params['role_auth_ids'] = $params['auth_ids'];
        $info = RoleModel::update($params,['id'=>$id],true);
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
        if($id == 1) $this->fail('该角色无法删除');

        $total = Admin::where('role_id',$id)->count();
        if($total > 0) $this->fail('角色正在使用中，无法删除');

        RoleModel::destroy($id);
        $this->ok();
    }
}
