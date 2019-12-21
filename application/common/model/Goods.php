<?php

namespace app\common\model;

use think\Model;
use traits\model\SoftDelete;

class Goods extends Model
{
    protected $deleteTime = "delete_time";

    # 商品关联类型 goods - type 一个商品属于一个type模型
    public function typeBind()
    {
        return $this->belongsTo('Type','type_id','id')->bind('type_name');
    }
    public function type()
    {
        return $this->belongsTo('Type','type_id','id');
    }
    # 商品关联品牌 goods - brand 一个商品属于一个品牌
    public function brandBind()
    {
        return $this->belongsTo('Brand','brand_id','id')->bind(['brand_name'=>'name']);
    }
    public function brand()
    {
        return $this->belongsTo('Brand','brand_id','id');
    }
    # 商品关联分类 goods - category 一个商品属于一个分类
    public function categoryBind()
    {
        return $this->belongsTo('Category','cate_id','id')->bind('cate_name');
    }
    public function category()
    {
        return $this->belongsTo('Category','cate_id','id');
    }
    # 定义商品-相册图片关联 一个商品有多个图片
    public function goodsImages()
    {
        return $this->hasMany('GoodsImages','goods_id','id');
    }
    # 定义商品-规格商品SKU的关联 一个商品SPU 有多个商品规格SKU
    public function specGoods()
    {
        return $this->hasMany('SpecGoods','goods_id','id');
    }
    # 获取器 修改goods_attr字段
    public function getGoodsAttrAttr($value)
    {
        return $value ? json_decode($value,true) : [];
    }

    protected static function init()
    {
        try{
            //实例化ES工具类
            $es = new \tools\es\MyElasticsearch();
            //设置新增回调
            self::afterInsert(function($goods)use($es){
                //添加文档
                $doc = $goods->visible(['id', 'goods_name', 'goods_desc', 'goods_price'])->toArray();
                $doc['cate_name'] = $goods->category->cate_name;
                $es->add_doc($goods->id, $doc, 'goods_index', 'goods_type');
            });
            //设置更新回调
            self::afterUpdate(function($goods)use($es){
                //修改文档
                $doc = $goods->visible(['id', 'goods_name', 'goods_desc', 'goods_price', 'cate_name'])->toArray();
                $doc['cate_name'] = $goods->category->cate_name;
                $body = ['doc' => $doc];
                $es->update_doc($goods->id, 'goods_index', 'goods_type', $body);
            });
            //设置删除回调
            self::afterDelete(function($goods)use($es){
                //删除文档
                $es->delete_doc($goods->id, 'goods_index', 'goods_type');
            });
        }catch (\Exception $e){

        }
    }
}
