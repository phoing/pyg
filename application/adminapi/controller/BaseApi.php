<?php

namespace app\adminapi\controller;

use app\adminapi\logic\AuthLogic;
use think\Controller;
use think\Request;
use tools\jwt\Token;

class BaseApi extends Controller
{
    protected $no_login = ['login/login','login/captcha','login/verify'];

//    public function __construct(Request $request)
//    {
//        parent::__construct();
//    }
    # 单下划线
    protected function _initialize()
    {
        parent::_initialize();
        # 允许访问的源域名
        header('Access-Control-Allow-Origin:*');
        # 允许的请求头信息
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept,Authorization');
        # 允许的请求方式类型
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
        # 允许携带证书式访问（携带cookie）
        header('Access-Control-Allow-Credentials:true');

//        $this->checkLogin();

        $res = AuthLogic::check();
        if(!$res) $this->fail('无权访问');
    }

    public function checkLogin()
    {
        try{
            # 特殊页面不需要检测
            # request()->controller() 获取当前控制器
            # request()->action() 获取当前方法
            $path = strtolower( request()->controller() . '/' . request()->action());
            if(in_array($path,$this->no_login)){
                return;
            }
            # 进行登录检测 解析token
            $user_id = Token::getUserId();
            if(!$user_id){
                $this->fail($user_id);
            }
            # 将用户id保存到请求信息中
            $this->request->get(['user_id'=>$user_id]);
            $this->request->post(['user_id'=>$user_id]);

        }catch (\Exception $e){
            $this->fail('token解析失败');
//            $msg = $e->getMessage();
//            $file = $e->getFile();
//            $line = $e->getLine();
//            $this->fail($msg . ';file:' . $file . ';line:' . $line);
        }
    }

    /**
     *  快速响应方法
     */
    public function response($code=200,$msg='success',$data=[])
    {
        $res = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];
        echo json_encode($res,JSON_UNESCAPED_UNICODE);die;
//        return json($res)->send();
    }

    public function fail($msg='fail',$code=500)
    {
        return $this->response($code,$msg);
    }

    public function ok($data=[],$code=200,$msg='success')
    {
        return $this->response($code,$msg,$data);
    }
}
