<?php

namespace app\home\controller;

use app\common\model\Category;
use app\common\model\SpecValue;
use app\home\logic\FootmarkLogic;
use think\Controller;
use app\common\model\Goods as GoodsModel;

class Goods extends Base
{
    public function index($id=0)
    {
        //接收参数
        $keywords = input('keywords');
        if(empty($keywords)){
            //获取指定分类下商品列表
            if(!preg_match('/^\d+$/', $id)){
                $this->error('参数错误');
            }
            //查询分类下的商品
            $list = \app\common\model\Goods::where('cate_id', $id)->order('id desc')->paginate(10);
            //查询分类名称
            $category_info = \app\common\model\Category::find($id);
            $cate_name = $category_info['cate_name'];
        }else{
            try{
                //从ES中搜索
                $list = \app\home\logic\GoodsLogic::search();
                $cate_name = $keywords;
            }catch (\Exception $e){
                $this->error('服务器异常');
            }
        }
        return view('index', ['list' => $list, 'cate_name' => $cate_name]);
    }

    public function detail($id)
    {
        $goods = GoodsModel::with('goods_images,spec_goods')->find($id);

        # 足迹
        FootmarkLogic::addFootMark($id);

        if($goods['spec_goods']){
            $goods['goods_price'] = $goods['spec_goods'][0]['price'];
        }
        # 返回数组中指定的一列数据
        $value_ids = array_column($goods['spec_goods'],'value_ids');
        $value_ids = array_unique(explode('_',implode('_',$value_ids)));
        # 查询规格值表
        $spec_values = SpecValue::alias('t1')
            ->join('pyg_spec t2','t1.spec_id = t2.id','left')
            ->field('t1.*,t2.spec_name')
            ->where('t1.id','in',$value_ids)
            ->select();
        # 组装规格名称数组
        $specs = [];
        foreach($spec_values as $k => $v){
            $specs[$v['spec_id']] = [
                'id' => $v['spec_id'],
                'spec_name' => $v['spec_name'],
                'spec_values' => []
            ];
        }
        # 组装规格值
        foreach($spec_values as $k => $v){
            $specs[$v['spec_id']]['spec_values'][] = [
                'id' => $v['id'],
                'spec_value' => $v['spec_value']
            ];
        }
        # 切换规格值改变价格 预期数组结构
        $value_ids_map = [];
        foreach($goods['spec_goods'] as $v){
            $value_ids_map[$v['value_ids']] = [
                'id' => $v['id'],
                'price' => $v['price']
            ];
        }
        $value_ids_map = json_encode($value_ids_map);

        return view('detail',['goods'=>$goods,'specs'=>$specs,'value_ids_map'=>$value_ids_map]);
    }
}
