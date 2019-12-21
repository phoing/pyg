<?php

namespace app\home\controller;

use Elasticsearch\ClientBuilder;
use think\Controller;

class Test extends Controller
{
    public function index()
    {
        $es = ClientBuilder::create()->setHosts(['127.0.0.1:9200'])->build();
        # 创建索引
        /*$params = [
            'index' => 'test_index',
        ];*/
        //$r = $es->indices()->create($params);

        # 添加文档（索引文档）
        /*$params = [
            'index' => 'test_index',
            'type' => 'test_type',
            'id' => 100,
            'body' => ['id'=>100,'title'=>'PHP从入门到精通','author'=>'张三']
        ];
        $r = $es->index($params);*/

        # 修改文档 如果数据没有变动不会修改，_version版本字段不会变动，result结果字段为noop
        /*$params = [
            'index' => 'test_index',
            'type' => 'test_type',
            'id' => 100,
            'body' => [
                'doc' => ['id'=>100,'title'=>'ES从入门到自保','author'=>'hoing1']
            ],
        ];
        $r = $es->update($params);*/

        # 删除文档 删除不存在的数据会报错
        $params = [
            'index' => 'test_index',
            'type' => 'test_type',
            'id' => 100
        ];
        $r = $es->delete($params);
        dump($r);die;
    }
}
