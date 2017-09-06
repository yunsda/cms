<?php
// +----------------------------------------------------------------------
// | 应用公共文件
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
use service\DataService;
use service\NodeService;
use think\Db;

error_reporting(0);

/**
 * 凭证底层接口请求 
 * 
 * @param string $methodUri     - 接口请求名的uri,如 goods/page
 * @param array $data           - 应用参数
 * @param number $time          - 缓存时间
 * @return string[]|mixed|NULL
 */
function ACoupon($methodUri, $inData=array(), $time=0, $refreshMode = false) {
    empty($inData) && $inData = array();
    
    $redisName = 'HGT-API-Coupon-'.md5($methodUri.json_encode($inData));
    $return    = $time > 0 ? unserialize(cache($redisName)) : null;
    
    if(!$return){//缓存不存在，读取缓存
        $accessToken = session('user.access_token');
        $url         = 'http://' . config('api_coupon.host') . '/coupon/interface/' . $methodUri . '?access_token=' . $accessToken;
        $clientId    = session('user.client_id');
        //file_put_contents('./1.txt',$url);
        ksort($inData);
        \think\Log::write("【apiCoupon】 sign md5前的值：" . var_export(json_encode($inData, JSON_UNESCAPED_UNICODE) . session('user.client_secret'), true));
        if (empty($inData)) {
            //接口服务端针对空参数，要求必须为对象形式
            $sign = md5('{}' . session('user.client_secret'));
        } else {
            $sign = md5(json_encode($inData, JSON_UNESCAPED_UNICODE) . session('user.client_secret'));
			
        }
		
        $sysData = [
            'client_id' => $clientId,
            'sign'      => $sign,
        ];
        $data = array_merge($sysData, $inData);
        \think\Log::write("【apiCoupon】-{$url} 请求数据：" . var_export($data, true));
//         $rest = DoGet($url . '&' . urldecode(http_build_query($data)));
        $rest = HttpRequest($url, $data);
        $return = json_decode($rest, true);
        \think\Log::write("【apiCoupon】返回数据：{$rest}\r\n" . var_export($return, true));
        
        //如果是token过期，则重新刷新token
        if ($return['error'] != '00' && $return['message'] == 'oauth is invalid'){
            //如果是刷新后的再次调用，则返回失败
            if ($refreshMode) {
                $return = array_merge($return, returnForUnAuth());
            }
            try {
                if (!session('user.refresh_token')) {
                    returnForUnAuth(false);
                }
                // 刷新一次token，置本次刷新标记，并再次请求一次接口
                \service\AuthService::refreshToken();
                return ACoupon($methodUri, $inData, $time, true);
            } catch (Exception $e) {
                $return = array_merge($return, returnForUnAuth());
            }
        }
        
        //查询成功，写入缓存
        if ($return['error'] == '00' && $time>0){
            cache($redisName, serialize($return), ['expire'=>time() + $time]);
        }
    }
    
    return $return;
}

/**
 * 如果api服务端返回token无效，则调用此方法进行用户登出，并做未登录提示或跳转
 * 
 * @param string $redirect
 * @return boolean|string
 */
function returnForUnAuth($redirect=true) {
    //退出登录，重新登录授权
    session('user', null);
    session_destroy();
    if (!$redirect) {
        return true;
    }
    
    if (!\think\Request::instance()->isAjax()) {
        $url = url('@admin/index');
        header("Location: {$url}");
        exit();
    } else {
        $return['error'] = 'outh_err01';
        return $return;
    }
}


function AD($data){//AT接口
    if(!empty($data)){
        $url                =   'http://' . config('api_coupon.appHost');;
        $dt['timestamp']    =   date('Y-m-d H:i:s',time());
        $dt['method']       =   $data['method'];
        $dt['data']         =   json_encode($data['data']);
        $dt['appkey']       =   'AF45T4s@fd%sa*df1V2aSdG45aWdf#ed';
        $dt['sign']         =   md5($dt['method'].$dt['timestamp'].$dt['appkey'].$dt['data']);
        $res                =   DoPost($url,$dt);
        \think\Log::write("【AT】输入数据：".print_r($dt,true));
        \think\Log::write("【AT】输出数据：".print_r(json_decode($res,true),true));
        return json_decode($res,true);
    }
}


//--------CURL---操作
function GetHttpContent($fsock = null)
{
    $out = null;
    while ($buff = @fgets($fsock, 2048)) {
        $out .= $buff;
    }
    fclose($fsock);
    $pos = strpos($out, "\r\n\r\n");
    $head = substr($out, 0, $pos);    //http head
    $status = substr($head, 0, strpos($head, "\r\n"));    //http status line
    $body = substr($out, $pos + 4, strlen($out) - ($pos + 4));//page body
    if (preg_match("/^HTTP\/\d\.\d\s([\d]+)\s.*$/", $status, $matches)) {
        if (intval($matches[1]) / 100 == 2) {
            return $body;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function DoGet($url)
{
    $url2 = parse_url($url);
    $url2["path"] = ($url2["path"] == "" ? "/" : $url2["path"]);
    $url2["port"] = ($url2["port"] == "" ? 80 : $url2["port"]);
    $host_ip = @gethostbyname($url2["host"]);
    $fsock_timeout = 2;  //2 second
    if (($fsock = fsockopen($host_ip, $url2['port'], $errno, $errstr, $fsock_timeout)) < 0) {
        return false;
    }
    $request = $url2["path"] . ($url2["query"] ? "?" . $url2["query"] : "");
    $in = "GET " . $request . " HTTP/1.0\r\n";
    $in .= "Accept: */*\r\n";
    $in .= "User-Agent: Payb-Agent\r\n";
    $in .= "Host: " . $url2["host"] . "\r\n";
    $in .= "Connection: Close\r\n\r\n";
    if (!@fwrite($fsock, $in, strlen($in))) {
        fclose($fsock);
        return false;
    }
    return GetHttpContent($fsock);
}

function DoPost($url, $post_data = array())
{
	
    $url2 = parse_url($url);
    if (!isset($url2["path"])) {
        $url2["path"] = '';
    }
    if (!isset($url2["port"])) {
        $url2["port"] = '';
    }
    $url2["path"] = ($url2["path"] == "" ? "/" : $url2["path"]);
    $url2["port"] = ($url2["port"] == "" ? 80 : $url2["port"]);
    $host_ip = @gethostbyname($url2["host"]);
    $fsock_timeout = 2; //2 second
    if (($fsock = fsockopen($host_ip, $url2['port'], $errno, $errstr, $fsock_timeout)) < 0) {
        return false;
    }
    if (!isset($url2["query"])) $url2["query"] = '';
    $request = $url2["path"] . ($url2["query"] ? "?" . $url2["query"] : "");
    $post_data2 = http_build_query($post_data);
//     $post_data2 = urldecode($post_data2);
    
    \think\Log::write("【api post】 请求参数：" . var_export($post_data2, true));
    
    $in = "POST " . $request . " HTTP/1.0\r\n";
    $in .= "Accept: */*\r\n";
    $in .= "Host: " . $url2["host"] . "\r\n";
    $in .= "User-Agent: Lowell-Agent\r\n";
    $in .= "Content-type: application/x-www-form-urlencoded;charset=utf-8\r\n";
    $in .= "Content-Length: " . strlen($post_data2) . "\r\n";
    $in .= "Connection: Close\r\n\r\n";
    $in .= $post_data2 . "\r\n\r\n";
    unset($post_data2);
    
//     \think\Log::write("post体：" . var_export($in, true));
    if (!@fwrite($fsock, $in, strlen($in))) {
        fclose($fsock);
        return false;
    }
    return GetHttpContent($fsock);
}

function HttpRequest($url, $data = array(), $abort = false)
{
	
    if (!function_exists('curl_init')) {
        return empty($data) ? DoGet($url) : DoPost($url, $data);
    }
    $timeout = $abort ? 1 : 2;
    $ch = curl_init();
    
    $this_header = array(
        "content-type: application/x-www-form-urlencoded;charset=UTF-8"
    );
    curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
    
    if (is_array($data) && $data) {
        $formdata = http_build_query($data);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formdata);
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $result = curl_exec($ch);
    return (false === $result && false == $abort) ? (empty($data) ?  DoGet($url) : DoPost($url, $data)) : $result;
}

/*
 * 生成交易流水号
 * @param char(2) $type
 */
function doOrderSn($type=''){
    $time	=	time();
    $st		=	rand(10,99);
    $A		=	date('Y',$time);
    $B		=	date('m',$time);
    $C		=	date('d',$time);
    $D		=	date('H',$time);
    $E		=	date('i',$time);
    $F		=	date('s',$time);
    $G		=	$type;
    $H		=	$st;
    $CC		=	($A%15*3+$B%7*2+$C%7*5+$D%9*7+$E%9*5+$F%9*6+$G%3*3+$H%23*7)%100;
    if ($CC <10) $res	= $A.$B.$C.$D.$E.$F.$G.$H.'4'.$CC;
    else $res	= $A.$B.$C.$D.$E.$F.$G.$H.$CC;
    return $res;
}

if (!function_exists('v')) {
    function v($var, $die = 1) {
        var_dump($var);
        $die && die();
    }
}

if (!function_exists('p')) {
    function p($var, $die = 1) {
        print_r($var);
        $die && die();
    }
}

/**
 * 打印输出数据到文件
 * @param mixed $data
 * @param bool $replace
 * @param string|null $pathname
 */
function pf($data, $replace = false, $pathname = null)
{
    is_null($pathname) && $pathname = RUNTIME_PATH . date('Ymd') . '.txt';
    $str = (is_string($data) ? $data : (is_array($data) || is_object($data)) ? print_r($data, true) : var_export($data, true)) . "\n";
    $replace ? file_put_contents($pathname, $str) : file_put_contents($pathname, $str, FILE_APPEND);
}

/**
 * 数组 转 对象
 *
 * @param array $arr 数组
 * @return object
 */
function array_to_object($arr) {
    if (gettype($arr) != 'array') {
        return;
    }
    foreach ($arr as $k => $v) {
        if (gettype($v) == 'array' || getType($v) == 'object') {
            $arr[$k] = (object)array_to_object($v);
        }
    }
    
    return (object)$arr;
}

/**
 * UTF8字符串加密
 * @param string $string
 * @return string
 */
function encode($string)
{
    $chars = '';
    $len = strlen($string = iconv('utf-8', 'gbk', $string));
    for ($i = 0; $i < $len; $i++) {
        $chars .= str_pad(base_convert(ord($string[$i]), 10, 36), 2, 0, 0);
    }
    return strtoupper($chars);
}

/**
 * UTF8字符串解密
 * @param string $string
 * @return string
 */
function decode($string)
{
    $chars = '';
    foreach (str_split($string, 2) as $char) {
        $chars .= chr(intval(base_convert($char, 36, 10)));
    }
    return iconv('gbk', 'utf-8', $chars);
}

/**
 * RBAC节点权限验证
 * @param string $node
 * @return bool
 */
function auth($node)
{
    return NodeService::checkAuthNode($node);
}

/**
 * 设备或配置系统参数
 * @param string $name 参数名称
 * @param bool $value 默认是false为获取值，否则为更新
 * @return string|bool
 */
function sysconf($name, $value = false)
{
    return ;
}

if (!function_exists('array_under_reset')) {
    /**
     * 返回以原数组某个值为下标的新数据
     *
     * @param array $array
     * @param string $key
     * @param int $type 1一维数组2二维数组
     * @return array
     */
    function array_under_reset($array, $key, $type = 1) {
        if (is_array($array)) {
            $tmp = array();
            foreach ($array as $v) {
                if ($type === 1) {
                    $tmp[$v[$key]] = $v;
                } elseif ($type === 2) {
                    $tmp[$v[$key]][] = $v;
                }
            }
            return $tmp;
        } else {
            return $array;
        }
    }
}

/**
 * 随机字符
 * @param int $length 长度
 * @param string $type 类型
 * @param int $convert 转换大小写 1大写 0小写
 * @return string
 */
function random($length=10, $type='letter', $convert=0)
{
    $config = array(
        'number'=>'1234567890',
        'letter'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'string'=>'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
        'all'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
    );

    if(!isset($config[$type])) $type = 'letter';
    $string = $config[$type];

    $code = '';
    $strlen = strlen($string) -1;
    for($i = 0; $i < $length; $i++){
        $code .= $string{mt_rand(0, $strlen)};
    }
    if(!empty($convert)){
        $code = ($convert > 0)? strtoupper($code) : strtolower($code);
    }
    return $code;
}