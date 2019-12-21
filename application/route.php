<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;

Route::domain('adminapi',function(){

    Route::get('/','adminapi/index/index');

    # 验证码路由
    # 验证码生成路由
    Route::get('captcha/:id',"\\think\\captcha\\CaptchaController@index");
    # 获取验证码和标识
    Route::get('verify','adminapi/login/verify');
    # 登录
    Route::post('login','adminapi/login/login');
    # 退出登录
    Route::get('logout','adminapi/login/logout');
    # 单文件上传
    Route::post('logo','adminapi/upload/logo');
    # 多文件上传
    Route::post('images','adminapi/upload/images');
    # 商品分类接口
    Route::resource('categorys','adminapi/category');
    # 商品品牌接口
    Route::resource('brands','adminapi/brand');
    # 商品类型
    Route::resource('types','adminapi/type');
    # 商品
    Route::resource('goods','adminapi/goods');
    # 商品图片删除
    Route::get('delpics/:id','adminapi/goods/delpics');
    # 权限接口
    Route::resource('auths','adminapi/auth');
    # 菜单权限
    Route::get('nav','adminapi/auth/nav');
    # 角色接口
    Route::resource('roles','adminapi/role');
    # 管理员接口
    Route::resource('admins','adminapi/admin');
});
