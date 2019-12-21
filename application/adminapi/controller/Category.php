<?php

namespace app\adminapi\controller;

use app\common\model\Brand;
use app\common\model\Goods;
use think\Collection;
use think\Image;
use think\Request;
use app\common\model\Category as CategoryModel;

class Category extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        # 接受参数
        $params = input();
        if(empty($params['pid'])){
            # 查询所有数据
            $list = CategoryModel::field('id,cate_name,pid,pid_path_name,level,is_hot,is_show,image_url')->select();
        }else{
            # 查询子分类
            $list = CategoryModel::field('id,cate_name,pid,pid_path_name,level,is_hot,is_show,image_url')
                ->where('pid',$params['pid'])
                ->select();
//            $params['type'] = 'list';
        }
        if(empty($params['type']) || $params['type'] != 'list'){
            $list = (new Collection($list))->toArray();
            $list = get_cate_list($list);
        }
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
        # 接受参数
        $params = input();
        # 参数检测
        $validate = $this->validate($params,[
            'cate_name|分类名称' => 'require',
            'pid|上级分类' => 'require|integer|egt:0',
            'is_show|是否显示' => 'require|in:0,1',
            'is_hot|是否热门' => 'require|in:0,1',
            'sort|排序' => 'require|integer',
        ]);
        if($validate !== true){
            $this->fail($validate,400);
        }
        # 添加数据
        if($params['pid'] == 0){
            # 顶级分类
            $params['pid_path'] = 0;
            $params['pid_path_name'] = '';
            $params['level'] = 0;
        }else{
            # 非顶级分类
            $p_info = CategoryModel::find($params['pid']);
            $params['pid_path'] = $p_info['pid_path'] . '_' . $p_info['id'];
            $params['pid_path_name'] = trim($p_info['pid_path_name'] . '_' . $p_info['cate_name'],'_');
            $params['level'] = $p_info['level'] + 1;
        }
        # 分类logo图片处理
        if(!empty($params['logo']) && is_file('.' . $params['logo'])){
            # 生成缩略图
            $logo = dirname($params['logo']) . DS . 'thumb_' . basename($params['logo']);
            Image::open('.' . $params['logo'])->thumb(50,50)->save('.' . $logo);
            $params['image_url'] = $logo;
        }
        $res = CategoryModel::create($params,true);
        # 返回数据
        $info = CategoryModel::find($res['id']);
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
        # 查询一条数据
        $info = CategoryModel::field('id,cate_name,pid,pid_path_name,level,is_show,is_hot,image_url')->find($id);
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
        # 接受参数
        $params = input();
        # 参数检测
        $validate = $this->validate($params,[
            'cate_name|分类名称' => 'require',
            'pid|上级分类' => 'require|integer|egt:0',
            'is_show|是否显示' => 'require|in:0,1',
            'is_hot|是否热门' => 'require|in:0,1',
            'sort|排序' => 'require|integer',
        ]);
        if($validate !== true){
            $this->fail($validate,400);
        }
        # 添加数据
        if($params['pid'] == 0){
            # 顶级分类
            $params['pid_path'] = 0;
            $params['pid_path_name'] = '';
            $params['level'] = 0;
        }else{
            # 非顶级分类
            $p_info = CategoryModel::find($params['pid']);
            $params['pid_path'] = $p_info['pid_path'] . '_' . $p_info['id'];
            $params['pid_path_name'] = trim($p_info['pid_path_name'] . '_' . $p_info['cate_name'],'_');
            $params['level'] = $p_info['level'] + 1;
        }
        # 不能降级
        $info = CategoryModel::find($id);
        if($info['level'] < $params['level']){
            $this->fail("不能降级");
        }
        # 分类logo图片处理
        if(!empty($params['logo']) && is_file('.' . $params['logo'])){
            # 生成缩略图
            $logo = dirname($params['logo']) . DS . 'thumb_' . basename($params['logo']);
            Image::open('.' . $params['logo'])->thumb(50,50)->save('.' . $logo);
            $params['image_url'] = $logo;
        }
        $res = CategoryModel::update($params,['id'=>$id],true);
        # 返回数据
        $this->ok();
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        # 分类下有子分类，不能删除
        $info = CategoryModel::where('pid',$id)->find();
        if($info){
            $this->fail('分类下有子分类，不能删除');
        }
        # 分类下有品牌，不能删除
        $total = Brand::where('cate_id',$id)->count('id');
        if($total > 0){
            $this->fail('分类下有品牌，不能删除');
        }
        # 分类下有商品，不能删除
        $total = Goods::where('cate_id',$id)->count('id');
        if($total > 0){
            $this->fail("分类下有商品，不能删除");
        }
        # 删除数据
        $list = CategoryModel::get($id);
        $list->delete();
        $this->ok();
    }
}
