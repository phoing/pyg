<?php

namespace app\adminapi\controller;

use app\common\model\GoodsImages;
use app\common\model\SpecGoods;
use think\Controller;
use think\Image;
use think\Request;
use app\common\model\Goods as GoodsModel;
use Exception;
use think\Db;
use app\common\model\Type;
use app\common\model\Category;

class Goods extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        # 获取数据
        $params = input();
        # 声明where变量
        $where = [];
        if(isset($params['keyword']) && !empty($params['keyword'])){
            $keyword = $params['keyword'];
            $where['goods_name'] = ['like',"%{$keyword}%"];
        }
        $data = GoodsModel::where($where)->with('type_bind,brand_bind,category_bind')->order('id desc')->paginate(10);
        $this->ok($data);

    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        # 获取数据
        $params = input();
        # 验证数据
        $validate = $this->validate($params,[
            'goods_name|商品名称' => 'require|max:100',
            'goods_price|商品价格' => 'require|float|gt:0',
//            'goods_number|商品库存' => 'require|interge|gt:0',
            'goods_logo|商品logo' => 'require',
            'goods_images|商品相册' => 'require|array',
            'item|规格值' => 'require|array',
            'attr|商品logo' => 'require|array',
            'type_id|商品模型' => 'require',
            'brand_id|商品品牌' => 'require',
            'cate_id|商品分类' => 'require',
        ]);
        if($validate !== true){
            $this->fail($validate);
        }
        # 开启事务
        Db::startTrans();
        try{
            # logo图片生成缩略图
            if(is_file('.' . $params['goods_logo'])){
               $goods_logo = dirname($params['goods_logo']) . DS . 'thumb_' . basename($params['goods_logo']);
               Image::open('.' . $params['goods_logo'])->thumb(200,240)->save('.' . $goods_logo);
               $params['goods_logo'] = $goods_logo;
            }else{
                $this->fail('商品logo图片不存在');
            }
            # 商品属性转化为json格式字符串
            $params['goods_attr'] = json_encode($params['attr'],JSON_UNESCAPED_UNICODE);
            # 添加商品数据
            $goods = GoodsModel::create($params,true);
            # 商品相册表数据
            $goods_images_data = [];
            foreach($params['goods_images'] as $k => $v){
                if(!is_file('.' . $v)) continue;

                $pics_big = dirname($v) . DS . 'thumb_800_' . basename($v);
                $pics_sma = dirname($v) . DS . 'thumb_400_' . basename($v);

                $image = Image::open('.' . $v);
                $image->thumb(800,800)->save('.' . $pics_big);
                $image->thumb(400,400)->save('.' . $pics_sma);
                $goods_images_data[] = [
                    'goods_id' => $goods['id'],
                    'pics_big' => $pics_big,
                    'pics_sma' => $pics_sma,
                ];
            }
            $goods_images = new GoodsImages();
            $goods_images->saveAll($goods_images_data);
            # 规格商品表数据
            $spec_goods_data = [];
            foreach ($params['item'] as $k => $v){
                $v['goods_id'] = $goods['id'];
                $spec_goods_data[] = $v;
            }
            $spec_goods_model = new SpecGoods();
            $spec_goods_model->allowField(true)->saveAll($spec_goods_data);
            Db::commit();
            $info = GoodsModel::with('type_bind,brand_bind,category_bind')->find($goods['id']);
            $this->ok($info);
        }catch(Exception $e){
            Db::rollback();
            $msg = $e->getMessage();
            $file = $e->getFile();
            $line = $e->getLine();
            $this->fail('msg:'.$msg.';file:'.$file.';line:'.$line);
        }
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $info = GoodsModel::with('goods_images,spec_goods,category,brand')->find($id);
        $info['type'] = Type::with('attrs,specs,specs.spec_values')->find($info['type_id']);
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
        # 查询相关数据
        # 图片 规格 分类 品牌
        $goods = GoodsModel::with('goods_images,category,brand,spec_goods')->find($id);
        # 查询所属模型及规格属性
        $goods['type'] = Type::with('attrs,specs,specs.spec_values')->find($goods['type_id']);
        # 查询所有模型列表 type
        $type = Type::select();
        $cate_one = Category::where('pid',0)->select();
        $pid_path = $goods['category']['pid_path'];
        $cate_two = Category::where('pid',$pid_path[1])->select();
        $cate_three = Category::where('pid',$pid_path[2])->select();

        $category = compact('cate_one','cate_two','cate_three');
        $data = compact('goods','type','category');
        $this->ok($data);
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
        # 获取数据
        $params = input();
        # 检测数据
        $validate = $this->validate($params,[
            'goods_name|商品名称' => 'require|max:100',
            'goods_price|商品价格' => 'require|float|gt:0',
//            'goods_number|商品库存' => 'require|interge|gt:0',
            'goods_logo|商品logo' => 'require',
            'goods_images|商品相册' => 'require|array',
            'item|规格值' => 'require|array',
            'attr|商品logo' => 'require|array',
            'type_id|商品模型' => 'require',
            'brand_id|商品品牌' => 'require',
            'cate_id|商品分类' => 'require',
        ]);
        if($validate !== true) $this->fail($validate);
        Db::startTrans();
        # 开启事务
        try{
            # logo图片生成缩略图
            if(!empty($params['goods_logo']) && is_file('.' . $params['goods_logo'])){
                $goods_logo = dirname($params['goods_logo']) . DS . 'thumb_' . basename($params['goods_logo']);
                Image::open('.' . $params['goods_logo'])->thumb(200,240)->save('.' . $goods_logo);
                $params['goods_logo'] = $goods_logo;
            }
            # 商品属性转化为json格式字符串
            $params['goods_attr'] = json_encode($params['attr'],JSON_UNESCAPED_UNICODE);
            # 修改表数据
            GoodsModel::update($params,['id'=>$id],true);
            # 商品相册表数据
            if(!empty($params['goods_images'])){
                $goods_images_data = [];
                foreach ($params['goods_images'] as $k => $v){
                    if(!is_file('.' . $v)) continue;
                    $pics_big = dirname('.' . $v) . DS . 'thumb_800_' . basename('.' . $v);
                    $pics_sma = dirname('.' . $v) . DS . 'thumb_400_' . basename('.' . $v);
                    # 生成缩略图
                    $image = Image::open('.' . $v);
                    $image->thumb(800,800)->save($pics_big);
                    $image->thumb(400,400)->save($pics_sma);
                    $goods_images_data = [
                        'goods_id' => $id,
                        'pics_big' => $pics_big,
                        'pics_sma' => $pics_sma,
                    ];
                }
                $goods_images = new GoodsModel();
                $goods_images->saveAll($goods_images_data);
            }
            # 规格商品表数据
            # 先删除原来的数据再添加新数据
            SpecGoods::destroy(['goods_id'=>$id]);
            $spec_goods_data = [];
            foreach($params['item'] as $k => $v){
                $v['goods_id'] = $id;
                $spec_goods_data[] = $v;
            }
            $spec_goods_model = new SpecGoods();
            $spec_goods_model->allowField(true)->saveAll($spec_goods_data);
            Db::commit();
            $info = GoodsModel::with('type_bind,brand_bind,category_bind')->find($id);
            $this->ok($info);
        }catch (Exception $e){
            Db::rollback();
            $msg = $e->getMessage();
            $file = $e->getFile();
            $line = $e->getLine();
            $this->fail('msg:'.$msg.';file:'.$file.';line:'.$line);
        }
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $is_on_sale = GoodsModel::where('id',$id)->value('is_on_sale');
        if($is_on_sale){
            $this->fail('商品已上架，不能删除');
        }
        GoodsModel::destroy($id);
        $goods_images = GoodsImages::where('goods_id',$id)->select();
        GoodsImages::destroy(['goods_id'=>$id]);
        SpecGoods::destroy(['goods_id'=>$id]);

        $images = [];
        foreach ($goods_images as $v){
            $images[] = $v['pics_big'];
            $images[] = $v['pics_sma'];
        }
        foreach ($images as $v){
            if(is_file('.' . $v)){
                unlink('.'.$v);
            }
        }
        $this->ok();
    }

    public function delpics($id){
        $data = GoodsImages::find($id);
        if(!$data){
            $this->ok();
        }
        $data->delete();
        if(is_file('.' . $data['pics_big'])) unlink('.' . $data['pics_big']);
        if(is_file('.' . $data['pics_sma'])) unlink('.' . $data['pics_sma']);
        $this->ok();
    }
}
