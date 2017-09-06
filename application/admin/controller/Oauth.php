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

use think\Controller;
use service\LogService;
use service\NodeService;

class Oauth extends Controller
{

    private $redirectUri;
    private $clientId;
    private $clientSecret;

    function __construct(){
        parent::__construct();
        $this->redirectUri = 'http://' . $_SERVER['HTTP_HOST'] . url('/admin/oauth/getaccesstoken');
        $this->clientId = config('api_coupon.client_id');
        $this->clientSecret = config('api_coupon.secret');
    }
    
    /*
     * 重定向到oauth2.0的获取code的接口地址
     */
    public function index()
    {
        $url = 'http://' . config('api_coupon.host') . '/coupon/oauth2/authorize?client_id=' . $this->clientId . '&response_type=code&redirect_uri=' . $this->redirectUri;
        $this->redirect($url);
    }

    /**
     * 获取code的回调处理-获取令牌
     */
    public function getAccessToken()
    {
        $code = input('param.code');
        $data = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUri
        ];
        $url = 'http://' . config('api_coupon.host') . '/coupon/oauth2/token';
        $result = json_decode(HttpRequest($url, $data), true);
        \think\Log::write("【apib】请求数据：" . print_r($result, true));
        
        if (!empty($result['error'])) {
            $this->error("{$result['error']}：{$result['error_description']}", url('/admin/oauth/index'));
        }
        
        $user = [
            'username' => '商家管理员',
            'access_token' => $result['access_token'],
            'refresh_token' => $result['refresh_token'],
            'role_name' => 'merchant',
        ];
        session('user', $user);
        
        NodeService::applyAuthNode();
        $this->success('登录成功，正在进入系统...', '@admin');
    }

    /* 查询活动列表 */
    public function activityList()
    {
        $access_token = session('user.access_token');
//         $result = ACoupon('activity/list', $access_token);
        
        
        $result = \Service\ActivityService::pageList();
        print_r($result);
    }

    
}