<?php

namespace app\adminapi\controller;

use think\Controller;

class Upload extends BaseApi
{
    public function logo(){
        # 获取所有参数
        $params = input();

        #参数检测
        if(!isset($params['type']) || empty($params['type'])){
            $this->fail('参数错误');
        }
        if(!in_array($params['type'],['goods','category','brand'])){
            $params['type'] = 'other';
        }
        # 处理数据(图片上传)
        $file = request()->file('logo');
        if(empty($file)){
            $this->fail('必须上传文件');
        }
        $dir = ROOT_PATH . 'public' . DS . 'uploads' . DS . $params['type'];
        if(!is_dir($dir)) mkdir($dir);
        # 检测并移动文件
        $info = $file->validate(['size' => 10*1024*1024, 'ext' => 'jpg,png,gif,jpeg', 'type' => 'image/jpeg,image/png,image/gif'])->move($dir);
        if(!$info){
            # 上传失败
            $this->fail($file->getError());
        }
        # 返回数据
        $logo = DS . 'uploads' . DS . $params['type'] . DS . $info->getSaveName();
        $this->ok($logo);
    }

    public function images(){
        # 接受参数
        $type = input('type','goods');
        # 参数检测
        if(!in_array($type,['goods','brand','category'])){
            $type = 'other';
        }
        # 处理数据（多图片上传）
        $dir = ROOT_PATH . 'public' . DS . 'uploads' . DS . $type;
        if(!is_dir($dir)) mkdir($dir);
        $files = request()->file('images');
        if(empty($files) || !is_array($files)){
            $this->fail('请上传多个图片');
        }
        # 初始化返回结果数组
        $res = [
            'success' => [],
            'error' => []
        ];
        # 遍历上传文件
        foreach ($files as $file){
            $info = $file->validate(['size'=>10*1024*1024,'ext'=>'jpg,png,gif,jpeg','type'=>'image/jpeg,image/png,image/gif'])->move($dir);
            if(!$info){
                # 如果上传文件失败，记录上传失败文件名，上传失败原因
                $res['error'][] = [
                    'name' => $file->getInfo('name'),
                    'msg' => $file->getError()
                ];
            }else{
                # 上传成功记录路径名
                $image = DS . 'uploads' . DS . $type . DS . $info->getSaveName();
                $res['success'][] = $image;
            }
        }
        # 返回数据
        $this->ok($res);

    }
}
