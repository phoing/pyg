<?php
namespace app\home\logic;



use app\common\model\Footmark;

class FootmarkLogic
{
    public static function addFootMark($id)
    {
        if(session('?user_info')){
            # 已登录
            # 查询数据库是否存在
            $footmark = Footmark::where(['goods_id'=>$id,'user_id'=>session("user_info.id")])->find();
            if(!$footmark){
                $data = [
                    'user_id' => session("user_info.id"),
                    'goods_id' => $id
                ];
                Footmark::create($data);
            }
        }else{
            # 未登录
            $data = cookie('footmark') ?: '';
            # 如果为空，直接加入数组
            if(empty($data)){
                $data = [];
                $data[] = $id;
            }else{
                # 不为空，转化为数组
                $data = json_decode($data);
                # 判断是否已存在
                if(!in_array($id,$data)){
                    $data[] = $id;
                }
            }
            cookie('footmark',json_encode($data));
        }
    }

    public static function cookieToDb()
    {
        $data = cookie('footmark') ?: [];
        if(!is_array($data)) $data = json_decode($data);
        foreach ($data as $v){
            self::addFootMark($v);
        }
        cookie('footmark',null);
    }
}