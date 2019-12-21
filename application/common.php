<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

# 加密函数
if(!function_exists('encrypt_password')){
    function encrypt_password($password){
        $salt = '!!!hoinglovepoiii!!!';
        return md5(md5(trim($password)) . $salt);
    }
}


if (!function_exists('get_cate_list')) {
    //递归函数 实现无限级分类列表
    function get_cate_list($list,$pid=0,$level=0) {
        static $tree = array();
        foreach($list as $row) {
            if($row['pid']==$pid) {
                $row['level'] = $level;
                $tree[] = $row;
                get_cate_list($list, $row['id'], $level + 1);
            }
        }
        return $tree;
    }
}

if(!function_exists('get_tree_list')){
    //引用方式实现 父子级树状结构
    function get_tree_list($list){
        //将每条数据中的id值作为其下标
        $temp = [];
        foreach($list as $v){
            $v['son'] = [];
            $temp[$v['id']] = $v;
        }
        //获取分类树
        foreach($temp as $k=>$v){
            $temp[$v['pid']]['son'][] = &$temp[$v['id']];
        }
        return isset($temp[0]['son']) ? $temp[0]['son'] : [];
    }
}

if (!function_exists('remove_xss')) {
    //使用htmlpurifier防范xss攻击
    function remove_xss($string){
        //composer安装的，不需要此步骤。相对index.php入口文件，引入HTMLPurifier.auto.php核心文件
//         require_once './plugins/htmlpurifier/HTMLPurifier.auto.php';
        // 生成配置对象
        $cfg = HTMLPurifier_Config::createDefault();
        // 以下就是配置：
        $cfg -> set('Core.Encoding', 'UTF-8');
        // 设置允许使用的HTML标签
        $cfg -> set('HTML.Allowed','div,b,strong,i,em,a[href|title],ul,ol,li,br,p[style],span[style],img[width|height|alt|src]');
        // 设置允许出现的CSS样式属性
        $cfg -> set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align');
        // 设置a标签上是否允许使用target="_blank"
        $cfg -> set('HTML.TargetBlank', TRUE);
        // 使用配置生成过滤用的对象
        $obj = new HTMLPurifier($cfg);
        // 过滤字符串
        return $obj -> purify($string);
    }
}
# 手机号码加密
if(!function_exists('encrypt_phone')){
    function encrypt_phone($phone){
        return substr($phone,0,3) . '****' . substr($phone,7,4);
    }
}

if(!function_exists('curl_request')){
    function curl_request($url,$post=false,$params=[],$https=false)
    {
        # 初始化curl请求
        $ch = curl_init($url);
        # 设置请求方式
        if($post){
            curl_setopt($ch,CURLOPT_POST,true);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$params);
        }
        # 请求协议如果是https，需要禁止从服务器端验证客户端的证书
        if($https){
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        }
        # 设置，让curl_exec获取到的结果以返回值方式返回
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        # 执行请求会话
        $res = curl_exec($ch);
        if(!$res){
            $msg = curl_error($ch);
            $res = [$msg];
        }

        curl_close($ch);
        return $res;
    }
}

#短信接口
if(!function_exists('send_msg')){
    function send_msg($phone,$msg){
        # 请求地址
        $gateway = config('msg.gateway');
        $appkey = config('msg.appkey');
        # 拼接参数在url中
        $url = $gateway . '?mobile=' . $phone . '&content=' .$msg . '&appkey=' . $appkey;
        # 发送请求
        $res = curl_request($url,true,[],true);
        if(is_array($res)){
            return $res[0];
        }
        $arr = json_decode($res,true);
        if(!isset($arr['code']) || $arr['code'] != 10000){
            return isset($arr['msg']) ? $arr['msg'] : '短信接口异常' ;
        }
        if(!isset($arr['result']['ReturnStatus']) || $arr['result']['ReturnStatus'] != 'Success'){
            # 短信发送失败
            return isset($arr['result']['Message']) ? $arr['result']['Message'] : '短信发送失败';
        }
        return true;
    }
}
