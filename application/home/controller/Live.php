<?php

namespace app\home\controller;

use app\common\model\LiveGoods;
use think\Controller;
use think\Image;
use think\Request;
use app\common\model\Live as LiveModel;

class Live extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        if(!session('?user_info')){
            $this->redirect('home/login/login');
        }
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $user_id = session('user_info.id');
        $list = LiveModel::where('user_id',$user_id)->order('id desc')->select();
        return view('index',['list'=>$list]);
    }

    public function create()
    {
        return view();
    }

    public function save()
    {
        $parmas = input();

        $user_id = session("user_info.id");
        $parmas['user_id'] = $user_id;
        # 转换成时间戳
        $parmas['start_time'] = strtotime($parmas['start_time']);
        $urls = \tools\live\Live::getUrl($user_id,$parmas['start_time']);
        $parmas = array_merge($parmas,$urls);
        $parmas['image'] = $this->upload_logo();
        $live = LiveModel::create($parmas,true);

//        dump($parmas);die;
        $live_goods = [];
        foreach($parmas['goods_links'] as $link){
            $goods_id = Request::create($link)->param('id');
            if(empty($goods_id)){
                # 参数1 要搜索的模式，字符串类型，参数2 输入字符串，要匹配的类型，参数3 被填充/赋值为搜索结果。
                preg_match('/\/id\/(\d+)/',$link,$match);
                $goods_id = $match[1];
            }
            $goods = \app\common\model\Goods::find($goods_id);
            if($goods){
                $row['live_id'] = $live->id;
                $row['goods_id'] = $goods->id;
                $row['goods_name'] = $goods->goods_name;
                $row['goods_price'] = $goods->goods_price;
                $row['goods_logo'] = $goods->goods_logo;
                $row['goods_link'] = $link;
                $live_goods[] = $row;
            }
        }
        $live_goods_model = new LiveGoods();
        $live_goods_model->saveAll($live_goods);
        $this->success('操作成功','home/live/index');
    }

    public function upload_logo()
    {
        # 获取文件信息
        $file = request()->file('image');
        if(empty($file)){
            $this->error("必须上传商品logo图片");
        }
        # 将文件移动到指定的目录
        $dir = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'live';
        if(!is_dir($dir)) mkdir($dir);
        $info = $file->validate(['size'=>5*1024*1024,'ext'=>['jpg','png','gif','jpeg']])->move($dir);
        if(empty($info)){
            $this->error($file->getError());
        }

        $logo = DS . "uploads" . DS . "live" . DS . $info->getSaveName();
        Image::open('.' . $logo)->thumb(200,200)->save('.' . $logo);
        return $logo;
    }

    public function read($id)
    {
        $info = \app\common\model\Live::find($id);
        if($info){
            $info['goods'] = \app\common\model\LiveGoods::where('live_id', $id)->select();
        }
        return view('read', ['info' => $info]);
    }
}
