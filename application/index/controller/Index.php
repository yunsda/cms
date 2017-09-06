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

namespace app\index\controller;

use service\HttpService;
use service\ToolsService;
use think\Controller;
use think\Db;
use think\Request;
use Wechat\Lib\Tools;

/**
 * 网站入口控制器
 * Class Index
 * @package app\index\controller
 * @date 2017/04/05 10:38
 */
class Index extends Controller
{

    /**
     * 网站入口
     */
    public function index()
    {
        $this->redirect('@admin');
    }

    public function test()
    {
        $r = ACoupon('test/do', ['note'=>'测试']);
        v($r);
    }

    public function wuliu()
    {
        $order = '444500528707';
        dump(ToolsService::express($order));
    }

}
