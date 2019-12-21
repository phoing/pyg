<?php

namespace app\adminapi\controller;

use app\common\model\Goods;
use think\Controller;
use think\Request;
use app\common\model\Brand as BrandModel;
use think\Image;

class Brand extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        # 获取参数
        $params = input();
        $where = [];
        # 分类下的品牌列表
        if(isset($params['cate_id'])){
            $where['cate_id'] = $params['cate_id'];
            $list = BrandModel::field('id,name')->where($where)->select();
        }else{
            # 判断是否有搜索
            if(isset($params['keyword']) && !empty($params['keyword'])){
                $keyword = $params['keyword'];
                $where['t1.name'] = ['like',"%{$keyword}%"];
            }
            $list = BrandModel::alias('t1')
                ->join('category t2','t1.cate_id=t2.id','left')
                ->field('t1.id,t1.name,t1.logo,t1.desc,t1.is_hot,t1.sort,t2.cate_name')
                ->where($where)
                ->paginate();
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
        # 获取所有参数
        $params = input();
        # 验证数据
        $validate = $this->validate($params,[
            'name|品牌名称' => 'require',
            'cate_id|分类id' => 'require|integer|gt:0',
            'is_hot|是否推荐' => 'require|in:0,1',
            'sort|排序' => 'require|between:0,9999',
        ]);
        if($validate !== true){
            $this->fail($validate);
        }
        # 生成缩略图
        if(isset($params['logo']) && !empty($params['logo']) && is_file('.' . $params['logo'])){
            Image::open('.' . $params['logo'])->thumb(200,100)->save('.' . $params['logo']);
        }
        # 添加数据
        $brand = BrandModel::create($params,true);
        $info = BrandModel::find($brand['id']);
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
        $info = BrandModel::find($id);
        $this->ok($info);
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
        # 获取所有参数
        $params = input();
        # 验证数据
        $validate = $this->validate($params,[
            'name|品牌名称' => 'require',
            'cate_id|分类id' => 'require|integer|gt:0',
            'is_hot|是否推荐' => 'require|in:0,1',
            'sort|排序' => 'require|between:0,9999',
        ]);
        if($validate !== true){
            $this->fail($validate);
        }
        # 生成缩略图
        if(isset($params['logo']) && !empty($params['logo']) && is_file('.' . $params['logo'])){
            Image::open('.' . $params['logo'])->thumb(200,100)->save('.' . $params['logo']);
        }
        # 添加数据
        $brand = BrandModel::update($params,['id'=>$id],true);
        $info = BrandModel::find($id);
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
        $total = Goods::where('brand_id',$id)->count();
        if($total > 0){
            $this->fail('品牌下有商品，不能删除');
        }
        BrandModel::destroy($id);
        $this->ok();
    }
}
