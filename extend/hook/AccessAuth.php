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

namespace hook;

use think\Config;
use think\exception\HttpResponseException;
use think\Request;
use think\View;
load_trait('controller/Jump');


/**
 * 访问权限管理
 * Class AccessAuth
 * @package hook
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/05/12 11:59
 */
class AccessAuth
{
 use \traits\controller\Jump;

    /**
     * 当前请求对象
     * @var Request
     */
    protected $request;

    /**
     * 行为入口
     * @param $params
     */
	 
    public function run(&$params)
    {	
        $this->request = Request::instance();
        list($module, $controller, $action) = [$this->request->module(), $this->request->controller(), $this->request->action()];
		$vars = get_class_vars(config('app_namespace') . "\\{$module}\\controller\\{$controller}");
		if ((!empty($vars['checkAuth']) || !empty($vars['checkLogin'])) && !session('user')) {
            if ($this->request->isAjax()) {
                $result = ['code' => 0, 'msg' => '抱歉, 您还没有登录获取访问权限!', 'data' => '', 'url' => url('@admin/login'), 'wait' => 3];
                throw new HttpResponseException(json($result));
            }
            throw new HttpResponseException(redirect('@admin/login'));
        }
		$url =  "/{$module}/{$controller}/{$action}";
		$nocheck = ['/index/Index/index','/admin/Login/index','/admin/Login/test','/admin/Index/index','/admin/Index/main','/admin/Login/out'];
		if(session('user.role') !== 1){
			if(!in_array($url,$nocheck)){
				if(!in_array($url,session('user.nodes'))){
					$this->error('你没有权限访问',url('/admin/Index/main'));
				}
			}
		}
		
        
    }
	

}
