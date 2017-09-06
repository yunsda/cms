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
use service\DataService;
use service\NodeService;
use service\ToolsService;
use think\Db;
use think\View;
use service;

/**
 * 后台入口
 * Class Index
 * @package app\admin\controller
 * @date 2017/02/15 10:41
 */
class Index extends BasicAdmin
{

   
    /**
     * 后台框架布局
     * @return View
     */
    public function index()
    {
        $menus = [
            [
                'title' => '活动管理',
                'icon' => 'fa fa-clone',
                'url' => '/admin/activity/index.html',
            ],
            
            [
                'title' => '商品管理',
                'icon' => 'fa fa-clone',
                'url' => '/admin/goods/index.html'
            ],
            
            [
                'title' => '卡券管理',
                'icon' => 'fa fa-clone',
                'url' => '#',
                'sub' => [
                    [
                        'title' => '制卡单管理',
                        'icon' => 'glyphicon glyphicon-menu-hamburger',
                        'url' => '/admin/bill/index.html'
                    ],
                    [
                        'title' => '卡券管理',
                        'icon' => 'glyphicon glyphicon-menu-hamburger',
                        'url' => '/admin/coupon/index.html'
                    ]
                ]
            ],
            
            [
                'title' => '交易明细查询',
                'icon' => 'fa fa-clone',
                'url' => '#',
                'sub' => [
                    [
                        'title' => '短信明细',
                        'icon' => 'glyphicon glyphicon-menu-hamburger',
                        'url' => '/admin/trade/listSendedCoupon.html',
                    ],
                    [
                        'title' => '核销明细',
                        'icon' => 'glyphicon glyphicon-menu-hamburger',
                        'url' => '/admin/trade/transaction.html',
                    ],
                ]
            ],
            
            [
                'title' => '交易统计',
                'icon' => 'fa fa-clone',
                'url' => '#',
                'sub' => [
                    [
                        'title' => '发码统计',
                        'icon' => 'glyphicon glyphicon-menu-hamburger',
                        'url' => '/admin/trade/statCouponSend.html',
                    ],
                    [
                        'title' => '核销统计',
                        'icon' => 'glyphicon glyphicon-menu-hamburger',
                        'url' => '/admin/trade/listPosCount.html',
                    ]
                ]
            ],
            
            [
                'title' => '系统管理',
                'icon' => 'fa fa-envelope',
                'url' => '#',
                'sub' => [
                    [
                        'title' => '后台首页',
                        'icon' => 'glyphicon glyphicon-menu-hamburger',
                        'url' => '/admin/index/main.html'
                    ],
                    [
                        'title' => '用户管理',
                        'icon' => 'glyphicon glyphicon-menu-hamburger',
                        'url' => '/admin/user/index.html'
                    ]
                
                ]
            ],
        ];
        return view('', ['title' => '系统管理', 'menus' => $menus]);
    }

    /**
     * 后台主菜单权限过滤
     * @param array $menus
     * @return array
     */
    private function _filterMenu($menus)
    {
        foreach ($menus as $key => &$menu) {
            if (!empty($menu['sub'])) {
                $menu['sub'] = $this->_filterMenu($menu['sub']);
            }
            if (!empty($menu['sub'])) {
                $menu['url'] = '#';
            } elseif (stripos($menu['url'], 'http') === 0) {
                continue;
            } elseif ($menu['url'] !== '#' && auth(join('/', array_slice(explode('/', $menu['url']), 0, 3)))) {
                $menu['url'] = url($menu['url']);
            } else {
                unset($menus[$key]);
            }
        }
        return $menus;
    }

    /**
     * 主机信息显示
     * @return View
     */
    public function main()
    {
		
        if (session('user.username') === 'admin' && session('user.password') === '21232f297a57a5a743894a0e4a801fc3') {
            $url = url('admin/index/pass') . '?id=' . session('user.id');
            $alert = ['type' => 'danger', 'title' => '安全提示', 'content' => "超级管理员默认密码未修改，建议马上<a href='javascript:void(0)' data-modal='{$url}'>修改</a>！",];
            $this->assign('alert', $alert);
            $this->assign('title', '后台首页');
        }
//         $_version = Db::query('select version() as ver');
        $version = array_pop($_version);
        return view('', ['mysql_ver' => '5.6']);
    }

    /**
     * 修改密码
     */
    public function pass()
    {
        if (intval($this->request->request('id')) !== intval(session('user.id'))) {
            $this->error('访问异常！');
        }
        if ($this->request->isGet()) {
            $this->assign('verify', true);
            return $this->_form('SystemUser', 'user/pass');
        } else {
            $data = $this->request->post();
            if ($data['password'] !== $data['repassword']) {
                $this->error('两次输入的密码不一致，请重新输入！');
            }
            $user = Db::name('SystemUser')->where('id', session('user.id'))->find();
            if (md5($data['oldpassword']) !== $user['password']) {
                $this->error('旧密码验证失败，请重新输入！');
            }
            if (DataService::save('SystemUser', ['id' => session('user.id'), 'password' => md5($data['password'])])) {
                $this->success('密码修改成功，下次请使用新密码登录！', '');
            } else {
                $this->error('密码修改失败，请稍候再试！');
            }
        }
    }

    /**
     * 修改资料
     */
    public function info()
    {
        if (intval($this->request->request('id')) === intval(session('user.id'))) {
            return $this->_form('SystemUser', 'user/form');
        }
        $this->error('访问异常！');
    }
    
    public function test() {
        $res = \service\AuthService::checkLogin('organ1', '123456');
        v($res);
        die;
    }

}
