<?php
namespace app\home\Logic;

use app\common\model\Cart;
use think\Collection;

class OrderLogic
{
    public static function getCartWithGoods()
    {
        $user_id = session('user_info.id');
        $cart_data = Cart::with('goods,spec_goods')->where('user_id',$user_id)
            ->where('is_selected',1)->select();
        $total_number = 0;
        $total_price = 0;
        $cart_data = (new Collection($cart_data))->toArray();
//        dump($cart_data);die;
        foreach ($cart_data as $k => $v){
            if($v['spec_goods_id']){
                $cart_data[$k]['goods']['goods_price'] = $v['spec_goods']['price'];
                $cart_data[$k]['goods']['cost_price'] = $v['spec_goods']['cost_price'];
                $cart_data[$k]['goods']['goods_number'] = $v['spec_goods']['store_count'];
                $cart_data[$k]['goods']['frozen_number'] = $v['spec_goods']['store_frozen'];
            }

            $total_number += $v['number'];
            $total_price += $v['number'] * $cart_data[$k]['goods']['goods_price'];
        }

        return compact('cart_data','total_number','total_price');
    }
}