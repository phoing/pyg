<?php

namespace app\home\controller;

use app\common\model\Category;
use think\Collection;
use think\Controller;
use think\Request;

class Base extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $this->getCategory();
    }

    public function getCategory()
    {
        # 尝试获取缓存
        $category = cache('category');
        if(empty($category)){
            # 查询所有的分类
            $category = Category::select();
            # 转换为标准的二维数组
            $category = (new Collection($category))->toArray();
            # 转化为父子树状结构
            $category = get_tree_list($category);
            # 存储到缓存
//            cache('category',$category,86400);
        }
//        dump($category);die;
//        cache('category',null);
        $this->assign('category',$category);
    }
}
