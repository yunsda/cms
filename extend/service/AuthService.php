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

namespace service;
use think\Db;

class AuthService
{
    /**
     * 根据用户名密码，验证用户是否能成功登陆
     * 
     * @param string $username
     * @param string $password
     * @throws \Exception
     * @return mixed
     */
    public static function checkLogin($username, $password) {
        //
        //1. 用户名密码登陆验证，并获取用户的clientid 和 clientSecret
        //
        $data['username'] = $username;
        $data['password']  = $password;
        
        $ret = self::checkUser($data);		
        if ('0000' != $ret['error']) {
            throw new \Exception($ret['message']);
        }       
        $organInfo =  Db::name('organ')->where('organ_id',$ret['data']['organ_id'])->find();        		
        //
        //2. 进行oauth2.0登陆授权，获取token来
        //
		$organName = $organInfo['organ_name'] .'-'.$ret['data']['name'];
        $result = self::getAccessToken($organInfo['username'], $organInfo['password'], $organInfo['client_id'], $organInfo['secret']);        
		$node = Db::name('access')->where('manager_id',$ret['data']['id'])->select();
        foreach($node as $val){
			$access[] = $val['purviewval'];
		}				
        //数据存储session
        $user = [
            'username'      => "{$organName}",
            'status'        => $ret['data']['status'],
            'access_token'  => $result['access_token'],
            'refresh_token' => $result['refresh_token'],
            'role_name'     => 'merchant',
            'client_id'     => $organInfo['client_id'],
            'client_secret' => $organInfo['secret'],
			'organ_id'      => $organInfo['organ_id'],
			'nodes'         => $access,
			'role'          => $ret['data']['type'],
        ];
        session('user', $user);       
        return $user;
    }
    
    
    
    /**
     * 获取code的回调处理-获取令牌
     * 
     * @param string $username
     * @param string $password
     * @param string $clientId
     * @param string $clientSecret
     * @throws \Exception
     * @return mixed
     */
    public static function getAccessToken($username, $password, $clientId, $clientSecret) {
        $data = [
            'grant_type'    => 'password',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'username'      => $username,
            'password'      => $password,
        ];
        $url = 'http://' . config('api_coupon.host') . '/coupon/oauth2/token';
        $result = HttpRequest($url, $data);
        \think\Log::write("【apiCoupon】请求数据 {$url}：" . print_r($data, true));
        \think\Log::write("【apiCoupon】返回数据：" . print_r($result, true));
        
        $result = json_decode($result, true);
        if (!empty($result['error'])) {
            throw new \Exception("{$result['error']}：{$result['error_description']}");
        }
        if (empty($result['access_token'])) {
            throw new \Exception("请求token返回为空");
        }
        
        return $result;
    }
    
    /**
     * 刷新接口通信令牌
     * 
     * @throws \Exception
     */
    public static function refreshToken() {
        $data = [
            'grant_type'    => 'refresh_token',
            'client_id'     => session('user.client_id'),
            'client_secret' => session('user.client_secret'),
            'refresh_token' => session('user.refresh_token'),
        ];
        $url = 'http://' . config('api_coupon.host') . '/coupon/oauth2/token';
        $result = HttpRequest($url, $data);
        \think\Log::write("【apiCoupon】请求数据 {$url}：" . print_r($data, true));
        \think\Log::write("【apiCoupon】返回数据：" . print_r($result, true));
        
        $result = json_decode($result, true);
        if (!empty($result['error'])) {
            throw new \Exception("{$result['error']}：{$result['error_description']}");
        }
        
        session('user.access_token', $result['access_token']);
        session('user.refresh_token', $result['refresh_token']);
        
        return ;
    }
	
	public static function checkUser($data){
		$where['user'] = trim($data['username']);
		$where['pwd']  = md5(trim($data['password']));		
		$info = Db::name('user')->where($where)->find();
		if($info){
			return ['error'=>'0000','data'=>$info];
		}
	}
    
}
