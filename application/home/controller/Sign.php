<?php

namespace app\home\controller;

use think\Controller;
use think\Request;

class Sign extends Controller
{
    /**
     * 生成签名
     */
    public function getSign($params)
    {
        # 删除签名参数
        unset($params['sign']);
        unset($params['sign_type']);
        # 排序
        ksort($params);
        # 拼接字符串
        $str = '';
        foreach($params as $k=>$v){
            if(!$v){
                continue;
            }
            $str .= $k . '=' . $v . '&';
        }
        $str = trim($str,'&');
        # 生成签名
        $sign = encrypt_password($str);
        return $sign;
    }
    /**
     * 验证签名
     */
    public function checkSign($params)
    {
        $old_sign = $params['sign'];
        # 生成新的签名
        $sign = $this->getSign($params);
        # 和参数中的原签名对比
        return $old_sign == $sign;
    }
    /**
     * 模拟支付宝服务器端
     * 发送请求前生成签名
     */
    public function alipay()
    {
        $url = "http://www.pyg.com/home/sign/notify";
        $params = [
            'out_trade_no' => '19970314520',
            'trade_no' => '19970904520',
            'total_amount' => '1000'
        ];
        # 生成签名
        $sign = $this->getSign($params);
        $params['sign'] = $sign;
        $params['sign_type'] = 'md5';
        # 将参数传递给商城
        $res = curl_request($url,true,$params);
        dump($res);

    }
    /**
     * 模拟商城异步通知地址
     * 验证签名
     */
    public function test_pyg()
    {
        $params = [
            'home/sign/test_pyg' => '',
            'out_trade_no' => '19970314520',
            'trade_no' => '19970904520',
            'total_amount' => '1000',
            'sign' => '1905980c851aa2bb12f77e4296a00b19',
            'sign_type' => 'md5'
        ];
        # 验证签名
        $res = $this->checkSign($params);
        dump($res);die;
    }

    public function notify()
    {
        $params = input();
        $params['total_amount'] = 1000;
        $result = $this->checkSign($params);
        if($result){
            echo 'success';die;
        }else{
            echo 'fail';die;
        }
    }
}
