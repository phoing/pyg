<?php

namespace app\adminapi\controller;

use app\common\model\Attribute;
use app\common\model\Spec;
use app\common\model\SpecValue;
use think\Controller;
use think\Request;
use think\Db;
use Exception;
use app\common\model\Type as TypeModel;

class Type extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $list = TypeModel::select();
        $this->ok($list);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        # 获取所有数据
        $params = input();
        # 检测数据
        $validate = $this->validate($params,[
            'type_name|模型名称' => 'require|max:20',
            'spec|规格' => 'require|array',
            'attr|属性' => 'require|array',
        ]);
        if($validate !== true){
            $this->fail($validate);
        }
        # 开启事务
        Db::startTrans();
        try{
            $type = TypeModel::create(['type_name'=>$params['type_name']],true);
            # 对$params['spec']的数据进行处理
            foreach($params['spec'] as $k=>$v){
                # 如果name为空，清除该行所有数据
                if(empty($v['name'])){
                   unset($params['spec'][$k]);
                   continue;
                }
                # 判断value的值是否是数组
                if(!is_array($v['value'])){
                    unset($params['spec'][$k]);
                    continue;
                }
                # 遍历value的值
                foreach($v['value'] as $key=>$val){
                    # 如果值为空，删除
                    if(empty($val)){
                        unset($params['spec'][$k]['value'][$key]);
                        continue;
                    }
                }
                # 如果规格值为空，将当前整个规格名称信息删除
                if(empty($params['spec'][$k]['value'])){
                    unset($params['spec'][$k]);
                    continue;
                }
            }
            # 添加商品规格名称数据
            $spec_data = [];
            foreach ($params['spec'] as $k => $v){
                $spec_data[] = [
                    'type_id' => $type['id'],
                    'spec_name' => $v['name'],
                    'sort' => $v['sort'],
                ];
            }
            $spec_model = new Spec();
            $spec_res = $spec_model->saveAll($spec_data);
            # 添加规格值数据
            $spec_value_data = [];
            foreach ($params['spec'] as $k => $v){
                # 循环value值
                foreach($v['value'] as $key => $val){
                    $spec_value_data[] = [
                        'spec_id' => $spec_res[$k]['id'],
                        'spec_value' => $val,
                        'type_id' => $type['id'],
                    ];
                }
            }
            $spec_value_model = new SpecValue();
            $spec_value_model->saveAll($spec_value_data);
            # 处理属性参数
            foreach ($params['attr'] as $k => $v){
                # name值不能为空
                if(empty($v['name'])){
                    unset($params['attr'][$k]);
                    continue;
                }
                # value是否是数组
                if(!is_array($v['value'])){
                    $v['value'] = [];
                    $params['attr'][$k]['value'] = [];
                }
                # 检测value值有没有空值
                foreach($v['value'] as $key => $val){
                    if(empty($val)){
                        unset($params['attr'][$k]['value'][$key]);
                    }
                }
            }
            # 添加属性信息到属性表
            $attr_data = [];
            foreach($params['attr'] as $k => $v){
                $attr_data[] = [
                    'attr_name' => $v['name'],
                    'type_id' => $type['id'],
                    'attr_values' => implode(',',$v['value']),
                    'sort' => $v['sort'],
                ];
            }
            $attr_model = new Attribute();
            $attr_model->saveAll($attr_data);
            # 提交事务
            Db::commit();
            $this->ok($type);
        }catch(Exception $e){
            # 回滚事务
            Db::rollback();
            $msg = $e->getMessage();
            $file = $e->getFile();
            $line = $e->getLine();
            $this->fail($msg.';'.$file.';'.$line);
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
        # 查询模型名称等信息，模型下的规格名，规格名下的规格值，模型下的属性信息
        $list = TypeModel::with("specs,specs.spec_values,attrs")->find($id);
        # 转化结果为数组
        $list = $list ? $list->toArray() : [];
        $this->ok($list);
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
        # 获取所有数据
        $params = input();
        # 检测数据
        $validate = $this->validate($params,[
            'type_name|模型名称' => 'require|max:20',
            'spec|规格' => 'require|array',
            'attr|属性' => 'require|array',
        ]);
        if($validate !== true){
            $this->fail($validate);
        }
        # 开启事务
        Db::startTrans();
        try{
            $type = TypeModel::update($params,['id'=>$id],true);
            # 对$params['spec']的数据进行处理
            foreach($params['spec'] as $k=>$v){
                # 如果name为空，清除该行所有数据
                if(empty($v['name'])){
                    unset($params['spec'][$k]);
                    continue;
                }
                # 判断value的值是否是数组
                if(!is_array($v['value'])){
                    unset($params['spec'][$k]);
                    continue;
                }
                # 遍历value的值
                foreach($v['value'] as $key=>$val){
                    # 如果值为空，删除
                    if(empty($val)){
                        unset($params['spec'][$k]['value'][$key]);
                        continue;
                    }
                }
                # 如果规格值为空，将当前整个规格名称信息删除
                if(empty($params['spec'][$k]['value'])){
                    unset($params['spec'][$k]);
                    continue;
                }
            }
            # 添加商品规格名称数据
            # 先删除再增加
            Spec::destroy(['type_id'=>$id]);
            $spec_data = [];
            foreach ($params['spec'] as $k => $v){
                $spec_data[] = [
                    'type_id' => $type['id'],
                    'spec_name' => $v['name'],
                    'sort' => $v['sort'],
                ];
            }
            $spec_model = new Spec();
            $spec_res = $spec_model->saveAll($spec_data);
            # 添加规格值数据
            # 先删除，再增加
            SpecValue::destroy(['type_id'=>$id]);
            $spec_value_data = [];
            foreach ($params['spec'] as $k => $v){
                # 循环value值
                foreach($v['value'] as $key => $val){
                    $spec_value_data[] = [
                        'spec_id' => $spec_res[$k]['id'],
                        'spec_value' => $val,
                        'type_id' => $type['id'],
                    ];
                }
            }
            $spec_value_model = new SpecValue();
            $spec_value_model->saveAll($spec_value_data);
            # 处理属性参数
            foreach ($params['attr'] as $k => $v){
                # name值不能为空
                if(empty($v['name'])){
                    unset($params['attr'][$k]);
                    continue;
                }
                # value是否是数组
                if(!is_array($v['value'])){
                    $v['value'] = [];
                    $params['attr'][$k]['value'] = [];
                }
                # 检测value值有没有空值
                foreach($v['value'] as $key => $val){
                    if(empty($val)){
                        unset($params['attr'][$k]['value'][$key]);
                    }
                }
            }
            # 添加属性信息到属性表
            # 先删除，在新增
            Attribute::destroy(['type_id'=>$id]);
            $attr_data = [];
            foreach($params['attr'] as $k => $v){
                $attr_data[] = [
                    'attr_name' => $v['name'],
                    'type_id' => $type['id'],
                    'attr_values' => implode(',',$v['value']),
                    'sort' => $v['sort'],
                ];
            }
            $attr_model = new Attribute();
            $attr_model->saveAll($attr_data);
            # 提交事务
            Db::commit();
            $this->ok($type);
        }catch(Exception $e){
            # 回滚事务
            Db::rollback();
            $msg = $e->getMessage();
            $file = $e->getFile();
            $line = $e->getLine();
            $this->fail($msg.';'.$file.';'.$line);
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
        # 开启事务，要么全部一起删除，要么不删除
        Db::startTrans();
        try{
            TypeModel::destroy($id);
            Spec::destroy(['type_id'=>$id]);
            SpecValue::destroy(['type_id'=>$id]);
            Attribute::destroy(['type_id'=>$id]);
            Db::commit();
            $this->ok();
        }catch(Exception $e){
            Db::rollback();
            $this->fail('msg:'.$e->getMessage().';file:'.$e->getFile().';line:'.$e->getLine());
        }
    }
}
