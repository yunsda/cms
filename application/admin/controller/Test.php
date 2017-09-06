<?php

// +----------------------------------------------------------------------
// | Think.Admin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 武汉市金源信企业服务信息系统有限公司 [ http://www.whjyx.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://www.whjyx.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：
// +----------------------------------------------------------------------

namespace app\admin\controller;     

use think\View;
use think\Controller;

/**
 * 后台入口
 * Class Index
 * @package app\admin\controller
 * @date 2017/02/15 10:41
 */
class Test extends Controller
{

    /**
     * 后台框架布局
     * @return View
     */
    public function index()
    {
        $url = 'http://192.168.10.195:8084/coupon/test/do';
        $r = HttpRequest($url, ['note'=>'测试']);
        v($r);
    }

    
    
    public function checkLogin() {
        $res = \service\AuthService::checkLogin('organ1', '123456');
        v($res);
        die;
    }

}
