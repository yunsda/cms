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

use controller\BasicAdmin;
use service\LogService;
use service\NodeService;
use think\Db;

/**
 * 系统登录控制器
 * class Login
 * @package app\admin\controller
 * @date 2017/02/10 13:59
 */
class Login extends BasicAdmin
{

    /**
     * 默认检查用户登录状态
     * @var bool
     */
    public $checkLogin = false;

    /**
     * 默认检查节点访问权限
     * @var bool
     */
    public $checkAuth = false;

    /**
     * 控制器基础方法
     */
    public function _initialize()
    {
        //如果是登陆状态，则直接进入管理员面板
        if (session('user.username') && session('user.access_token') && $this->request->action() !== 'out') {
            $this->success('登录成功，正在进入系统...', '@admin');
        }
    }
    
	
	
    /**
     * 用户登录
     * @return string
     */
    public function index()
    {
        //         $this->redirect('@admin/oauth/index');
        if ($this->request->isGet()) {
            
            return $this->fetch('', ['title' => '用户登录']);
        } else {
            
            // 输入数据效验
            $username = $this->request->post('username', '', 'trim');
            $password = $this->request->post('password', '', 'trim');
           
            // 用户信息验证
            try {
                $res = \service\AuthService::checkLogin($username, $password);
            } catch (\Exception $e) {
                $this->error("登陆失败：{$e->getMessage()}");
            }
            
            empty($res) && $this->error('登录账号不存在，请重新输入!');
            empty($res['status']) && $this->error('账号被禁止登录!');
            
            $this->success('登录成功，正在进入系统...', '@admin');
        }
    }

    /**
     * 退出登录
     */
    public function out()
    {
//         LogService::write('系统管理', '用户退出系统成功');
        session('user', null);
        session_destroy();
        $this->success('退出登录成功！', '@admin/login');
    }
	
	
}
