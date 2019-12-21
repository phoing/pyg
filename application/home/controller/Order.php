<?php

namespace app\home\controller;

use app\common\model\PayLog;
use app\common\model\Address;
use app\common\model\Order as OrderModel;
use app\common\model\OrderGoods;
use app\common\model\Cart;
use app\common\model\Goods;
use app\common\model\SpecGoods;
use app\home\Logic\OrderLogic;
use think\Controller;
use think\Db;
use think\Request;

class Order extends Base
{

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //登录检测
        if(!session('?user_info')){
            session('back_url','home/cart/index');
            $this->redirect('home/login/login');
        }
        $user_id = session('user_info.id');
        $address = Address::where('user_id',$user_id)->select();

        $res = OrderLogic::getCartWithGoods();

        $cart_data = $res['cart_data'];
        $total_price = $res['total_price'];
        $total_number = $res['total_number'];
        return view('create',['address'=>$address,'cart_data'=>$cart_data,'total_price'=>$total_price,'total_number'=>$total_number]);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $params = input();

        $validate = $this->validate($params,[
            'address_id' => 'require|integer|gt:0'
        ]);
        if($validate !== true) $this->error($validate);

        $user_id = session("user_info.id");
        $address = Address::where('user_id',$user_id)->where('id',$params['address_id'])->find();
        if(!$address) $this->error("收货地址数据异常");

        Db::startTrans();
        try{
            # 查询选择的购物记录，计算商品总价
            $res = OrderLogic::getCartWithGoods();
            # 库存检测
            foreach ($res['cart_data'] as $v){
                if($v['number'] > $v['goods']['goods_number']){
                    throw new \Exception('订单中包含库存不足的商品');
                }
            }
            # 生成订单编号，时间 + 随机6位数
            $order_sn = time() . mt_rand(100000,999999);
            # 组装一条订单数据
            $row = [
                'user_id' => $user_id,
                'order_sn' => $order_sn,
                'order_status' => 0,
                'consignee' => $address['consignee'],
                'phone' => $address['phone'],
                'address' => $address['area'] . $address['address'],
                'goods_price' => $res['total_price'], //商品总价
                'shipping_price' => 0, //邮费
                'coupon_price' => 0, //优惠券抵扣
                'order_amount' => $res['total_price'], // 商品总价+邮费-优惠券抵扣
                'total_amount' => $res['total_price'], // 商品总价+邮费
            ];
            $order = OrderModel::create($row,true);
            $order_goods = [];
            foreach ($res['cart_data'] as $v){
                $order_goods[] = [
                    'order_id' => $order['id'],
                    'goods_id' => $v['goods_id'],
                    'spec_goods_id' => $v['spec_goods_id'],
                    'number' => $v['number'],
                    'goods_name' => $v['goods']['goods_name'],
                    'goods_logo' => $v['goods']['goods_logo'],
                    'goods_price' => $v['goods']['goods_price'],
                    'spec_value_names' => $v['spec_goods']['value_names'],
                ];
            }
            $order_goods_model = new OrderGoods();
            $order_goods_model->saveAll($order_goods);

            $goods = [];
            $spec_goods = [];
            foreach($res['cart_data'] as $v){
                if($v['spec_goods_id']){
                    $spec_goods[] = [
                        'id' => $v['spec_goods_id'],
                        'store_count' => $v['goods']['goods_number'] - $v['number'],
                        'store_frozen' => $v['goods']['frozen_number'] + $v['number'],
                    ];
                }else{
                    $goods[] = [
                        'id' => $v['goods_id'],
                        'goods_number' => $v['goods']['goods_number'] - $v['number'],
                        'frozen_number' => $v['goods']['frozen_number'] + $v['number'],
                    ];
                }
            }
            $goods_model = new Goods();
            $goods_model->saveAll($goods);
            $spec_goods_model = new SpecGoods();
            $spec_goods_model->saveAll($spec_goods);
            # 删除购物车对应的数据
            Cart::destroy(['user_id'=>$user_id,'is_selected'=>1]);

            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            $msg = $e->getMessage();
            $this->error($msg);
        }
        $this->redirect('home/order/pay',['id'=>$order['id']]);

    }

    /**
     * 选择支付方式
     */
    public function pay($id)
    {
        $order = OrderModel::find($id);

        $pay_type = config('pay_type');

        //二维码图片中的支付链接（本地项目自定义链接，传递订单id参数）
        //$url = url('/home/order/qrpay', ['id'=>$order->order_sn], true, true);
        //用于测试的线上项目域名 http://pyg.tbyue.com
        $url = url('/home/order/qrpay', ['id'=>$order->order_sn, 'debug'=>'true'], true, "http://pyg.tbyue.com");
        //生成支付二维码
        $qrCode = new \Endroid\QrCode\QrCode($url);
        //二维码图片保存路径（请先将对应目录结构创建出来，需要具有写权限）
        $qr_path = '/uploads/qrcode/'.uniqid(mt_rand(100000,999999), true).'.png';
        //将二维码图片信息保存到文件中
        $qrCode->writeFile('.' . $qr_path);
        $this->assign('qr_path', $qr_path);

        return view('pay',['order'=>$order,'pay_type'=>$pay_type]);
    }

    /**
     * 扫码支付
     */
    public function qrpay()
    {
        // $_SERVER['HTTP_USER_AGENT']
        $agent = request()->server('HTTP_USER_AGENT');
        //判断扫码支付方式
        if ( strpos($agent, 'MicroMessenger') !== false ) {
            //微信扫码
            $pay_code = 'wx_pub_qr';
        }else if (strpos($agent, 'AlipayClient') !== false) {
            //支付宝扫码
            $pay_code = 'alipay_qr';
        }else{
            //默认为支付宝扫码支付
            $pay_code = 'alipay_qr';
        }
        //接收订单id参数
        $order_sn = input('id');
        //创建支付请求
        $this->pingpp($order_sn,$pay_code);
    }
    /**
     * @param $order_sn
     * @param $pay_code
     */
    //发起ping++支付请求
    public function pingpp($order_sn,$pay_code)
    {
        //查询订单信息
        $order = OrderModel::where('order_sn', $order_sn)->find();
        //ping++聚合支付
        \Pingpp\Pingpp::setApiKey(config('pingpp.api_key'));// 设置 API Key
        \Pingpp\Pingpp::setPrivateKeyPath(config('pingpp.private_key_path'));// 设置私钥
        \Pingpp\Pingpp::setAppId(config('pingpp.app_id'));
        $params = [
            'order_no'  => $order['order_sn'],
            'app'       => ['id' => config('pingpp.app_id')],
            'channel'   => $pay_code,
            'amount'    => $order['order_amount']*100,
            'client_ip' => '127.0.0.1',
            'currency'  => 'cny',
            'subject'   => 'Your Subject',//自定义标题
            'body'      => 'Your Body',//自定义内容
            'extra'     => [],
        ];
        if($pay_code == 'wx_pub_qr'){
            $params['extra']['product_id'] = $order['id'];
        }
        //创建Charge对象
        $ch = \Pingpp\Charge::create($params);
        //跳转到对应第三方支付链接
        $this->redirect($ch->credential->$pay_code);die;
    }

    /**
     * 查询订单状态
     */
    public function status()
    {
        # 接收订单编号
        $order_sn = input('order_sn');
        # 查询订单状态
//        $order_status = OrderModel::where('order_sn',$order_sn)->value('order_status');
//        return json(['code'=>200,'msg'=>'success','data'=>$order_status]);
        $res = curl_request("http://pyg.tbyue.com/home/order/status/order_sn/{$order_sn}");
        echo $res;die;
        dump($res);
    }

    public function payresult()
    {
        $order_sn = input('order_sn');
        $order = OrderModel::where('order_sn',$order_sn)->find();
        if($order){
            return view('paysuccess',['pay_name'=>$order->pay_name,'total_amount'=>$order['total_amount']]);
        }else{
            # 验证失败
            return view('payfail',['msg'=>'订单编号错误']);
        }
    }

    /**
     * 去支付
     */
    public function topay()
    {
        $params = input();
//        dump($params);
        $validate = $this->validate($params,[
            'id' => 'require|integer|gt:0',
            'pay_type|支付方式' => 'require'
        ]);
        if($validate !== true) $this->error($validate);

        # 查询订单信息
        $user_id = session('user_info.id');
        $order = OrderModel::where('id',$params['id'])->where('user_id',$user_id)->where('order_status',0)->find();
        if(!$order) $this->error("订单数据异常");

        $pay_type = config('pay_type');
        $pay_name = $pay_type[$params['pay_type']]['pay_name'];
        # 修改到订单表
        $order->pay_code = $params['pay_type'];
        $order->pay_name = $pay_name;
        $order->save();
//        dump($order);die;
        # 去支付
        switch ($params['pay_type']){
            case 'wecat':
                # 微信支付
                echo '微信支付不会做';
                break;
            case 'unionpay':
                # 银联
                echo '银联也不会';
                break;
            case 'alipay':
            default:
                $html = "<form id='alipayment' action='/plugins/alipay/pagepay/pagepay.php' method='post' style='display: none'>
                    <input type='hidden' id='WIDout_trade_no' name='WIDout_trade_no' value='{$order->order_sn}'/>
                    <input type='hidden' id='WIDsubject' name='WIDsubject' value='品优购商城订单'/>
                    <input type='hidden' id='WIDtotal_amount' name='WIDtotal_amount' value='{$order->order_amount}'/>
                    <input type='hidden' id='WIDbody' name='WIDbody' value='品优购商品，买它买它'/>
                    <script>document.getElementById('alipayment').submit();</script>
                </form>";
                echo $html;
                break;
        }
    }



    public function callback()
    {
        $params = input();

        require_once("./plugins/alipay/config.php");
        require_once("./plugins/alipay/pagepay/service/AlipayTradeService.php");

        $alipayService = new \AlipayTradeService($config);
        $result = $alipayService->check($params);
        if($result){
            # 验证成功
            return view('paysuccess',['pay_name'=>'支付宝','total_amount'=>$params['total_amount']]);
        }else{
            return view('payfail',['msg'=>'参数异常，请重新支付或联系客服']);
        }

    }

    public function notify()
    {
        $params = input();

        require_once("./plugins/alipay/config.php");
        require_once("./plugins/alipay/pagepay/service/AlipayTradeService.php");

        $alipayService = new \AlipayTradeService($config);
        # 记录日志
        trace('/home/order/notify:异步通知开始；参数：' . json_encode($params,JSON_UNESCAPED_UNICODE) , 'debug');
        $result = $alipayService->check($params);
        if(!$result){
            trace('/home/order/notify:验签失败：' . $result,'error');
            echo 'fail';die;
        }
        # 验签成功
        $trade_status = $params['trade_status'];
        if($trade_status == 'TRADE_FINISHED'){
            trace('/home/order/notify:交易已完成：' . $trade_status,'debug');
            echo 'success';die;
        }

        if($trade_status == "TRADE_SUCCESS"){
            $order_sn = $params['out_trade_no'];
            $order = OrderModel::where('order_sn',$order_sn)->find();
            if(!$order){
                trace('/home/order/notify:订单不存在：' . $order_sn, 'error');
                echo 'fail';die;
            }
            # 检测订单支付金额
            if($order['order_amount'] != $params['total_amount']){
                trace('/home/order/notify:订单支付金额不正确：应付款金额：' . $order['order_amount'] . '实付款金额：' . $params['total_amount'],'error');
                echo 'fail';die;
            }
            #检测订单支付状态
            if($order['order_status'] != 0){
                trace('/home/order/notify：订单状态不是待付款：' . $order['order_status'],'debug');
                echo 'success';die;
            }
            # 修改订单状态
            $order->order_status = 1;
            $order->pay_code = 'alipay';
            $order->pay_name = '支付宝';
            $order->save();
            # 记录支付信息
            PayLog::create([
                'order_sn' => $order_sn,
                'json' => json_encode($params,JSON_UNESCAPED_UNICODE)
            ],true);
            #扣减库存
            #查询订单下的商品信息
            $order_goods = OrderGoods::with('goods,spec_goods')->where('order_id',$order['id'])->select();
            $goods_data = [];
            $spec_goods_data = [];
            foreach($order_goods as $v){
                if($v['spec_goods_id']){
                    # 修改sku表
                    $spec_goods_data[] = [
                        'id' => $v['spec_goods_id'],
                        'store_frozen' => $v['spec_goods']['store_frozen'] - $v['number']
                    ];
                }else{
                    # 修改商品表spu表
                    $goods_data[] = [
                        'id' => $v['goods_id'],
                        'frozen_number' => $v['goods']['frozen_number'] - $v['number']
                    ];
                }
            }
            # 批量修改
            $goods_model = new Goods();
            $goods_model->saveAll($goods_data);
            $spec_goods_model = new SpecGoods();
            $spec_goods_model->saveAll($spec_goods_data);

            trace('/home/order/notify:订单状态已修改','debug');
            echo 'success';die;
        }
        trace('home/order/notify:其他交易状态','debug');
        echo 'success';die;
    }
}
