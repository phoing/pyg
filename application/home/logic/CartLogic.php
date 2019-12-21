<?php

namespace app\home\logic;

use app\common\model\Cart;
use think\Collection;

class CartLogic
{
    public static function addCart($goods_id,$spec_goods_id,$number,$is_selected=1)
    {
        if(session('?user_info')){
            # 已登录，添加到数据表
            $user_id = session('user_info.id');
            $where = compact('user_id','goods_id','spec_goods_id');
            $info = Cart::where($where)->find();
            if($info){
                # 存在累加数量
                $info->number += $number;
                $info->is_selected = $is_selected;
                $info->save();
            }else{
                $where['number'] = $number;
                $where['is_selected'] = $is_selected;
                Cart::create($where,true);
            }
        }else{
            # 未登录，添加到cookie
            $data = cookie('cart') ?: [];

            $key = $goods_id . '_' . $spec_goods_id;

            if(isset($data[$key])){
                $data[$key]["number"] += $number;
                $data[$key]["is_selected"] = $is_selected;
            }else{
                $data[$key] = [
                    'id' => $key,
                    'goods_id' => $goods_id,
                    'spec_goods_id' => $spec_goods_id,
                    'is_selected' => $is_selected,
                    'number' => $number
                ];
            }
            cookie('cart',$data,7*86400);
        }
    }

    public static function getAllCart()
    {
        # 判断登录状态：已登录，查询数据库；未登录 取cookie
        if(session('?user_info')){
            $user_id = session('user_info.id');
            $data = Cart::field('id,user_id,goods_id,spec_goods_id,number,is_selected')->where('user_id',$user_id)->select();
            $data = (new Collection($data))->toArray();
        }else{
            $data = cookie('cart') ?: [];
            $data = array_values($data);
        }

        return $data;
    }

    public static function cookieToDb()
    {
        $data = cookie('cart') ?: [];
        foreach($data as $v){
            self::addCart($v['goods_id'],$v['spec_goods_id'],$v['number']);
        }
        cookie('cart',null);
    }

    public static function changeNum($id,$number)
    {
        if(session('?user_info')){
            $user_id = session('user_info.id');
            Cart::update(['number'=>$number],['id'=>$id,'user_id'=>$user_id]);
        }else{
            $data = cookie('cart') ?: [];
            $data[$id]['number'] = $number;
            cookie('cart',$data,86400*7);
        }
    }

    public static function delCart($id)
    {
        if(session('?user_info')){
            $user_id = session('user_info.id');
            Cart::where(['id'=>$id,'user_id'=>$user_id])->delete();
        }else{
            $data = cookie('cart') ?: [];
            unset($data[$id]);
            cookie('cart',$data,86400*7);
        }
    }

    public static function changeStatus($id,$is_selected)
    {
        if(session('?user_info')){
            $user_id = session('user_info.id');
            $where['user_id'] = $user_id;
            if($id != 'all'){
                $where['id'] = $id;
            }
            Cart::where($where)->update(['is_selected' => $is_selected]);
        }else{
            $data = cookie('cart') ?: [];
            if($id == 'all'){
                foreach($data as &$v){
                    $v['is_selected'] = $is_selected;
                }
                unset($v);
            }else{
                $data[$id]['is_selected'] = $is_selected;
            }

            cookie('cart',$data,86400*7);
        }
    }
}