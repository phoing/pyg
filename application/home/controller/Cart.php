<?php

namespace app\home\controller;

use app\home\logic\CartLogic;
use app\home\logic\GoodsLogic;
use think\Controller;

class Cart extends Base
{
    public function addcart()
    {
        if(request()->isGet()) $this->redirect('home/index/index');

        $params = input();

        $validate = $this->validate($params,[
            'goods_id' => 'require|integer|gt:0',
            'spec_goods_id' => 'require|gt:0',
            'number|购买数量' => 'require|integer|gt:0',
        ]);

        if($validate !== true) $this->error($validate);

        CartLogic::addCart($params['goods_id'],$params['spec_goods_id'],$params['number']);

        $goods = GoodsLogic::getGoodsWithSpecGoods($params['goods_id'],$params['spec_goods_id']);

        return view('addcart',['goods' => $goods]);
    }

    public function index()
    {
        $list = CartLogic::getAllCart();
        foreach($list as &$v){
            $v['goods'] = GoodsLogic::getGoodsWithSpecGoods($v['goods_id'],$v['spec_goods_id']);
        }
        unset($v);
        return view('index',['list'=>$list]);
    }

    public function changenum()
    {
        $params = input();
        $validate = $this->validate($params,[
            'id' => 'require',
            'number' => 'require|integer|gt:0'
        ]);
        if($validate !== true){
            $res = ['code' => 400, 'msg' => '参数错误'];
            echo json_encode($res);die;
        }
        CartLogic::changeNum($params['id'],$params['number']);
        $res = ['code'=>200,'msg'=>'success'];
        echo json_encode($res);die;
    }

    public function delcart()
    {
        $params = input();

        if(!isset($params['id']) || empty($params['id'])){
            $res = ['coed' => 400,'msg' => '参数错误'];
            echo json_encode($res);die;
        }

        CartLogic::delCart($params['id']);
        $res = ['code' => 200, 'msg' => 'success'];
        echo json_encode($res);die;
    }

    public function changestatus()
    {
        $params = input();

        $validate = $this->validate($params,[
            'id' => 'require',
            'status' => 'require|in:0,1'
        ]);
        if($validate !== true){
            $res = ['code'=>400,'msg'=>$validate];
            echo json_encode($res);die;
        }
        CartLogic::changeStatus($params['id'],$params['status']);
        $res = ['code'=>200,'msg'=>'success'];
        echo json_encode($res);die;
    }
}
