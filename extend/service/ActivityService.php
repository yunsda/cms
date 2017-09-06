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


class ActivityService
{

    /**
     * 活动分页查询
     *
     * @param number $page
     * @param number $pageSize
     * @throws \Exception
     */
    public static function pageList($data)
    {   
        $status = $data['status'];	
		foreach( $data as $k=>$v){   
			if( !$v)   
				unset( $data[$k] );   
		}       
		if($status == '0'){
			$data['status'] = $status;
		}else{
			if(!empty($status)) $data['status'] = $status;
		}
		$result = ACoupon('activity/page', $data);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求失败：{$result['message']}");
        }
        return $result['data'];
    }
	
	/**
     * 活动终端查询
     *
     * @param number $activityId 
     * @throws \Exception
     */
    public static function getActivityDetail($activityId)
    {
        $data = [
            'activityId' => $activityId,
        ];
        $result = ACoupon('activity/detail', $data);
        if ($result['error'] != '00') {
            throw new \Exception("终端详情接口请求失败");
        }
        return $result['data'];
    }
	
	/**
     * 创建活动
     *
     * @param array $data 
     * @throws \Exception
     */
    public static function createActivity($data)
    {
		
		$data = array_filter($data);
		$data['status'] = $data['type'] == '1' ? '0' : '1';
        unset($data['type']);		
		
        $result = ACoupon('activity/add', $data);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求失败：{$result['message']}");
        }
        return true;
    }
	
	/**
     * 修改活动
     *
     * @param array $data 
     * @throws \Exception
     */
    public static function updateActivity($data)
    {
		$data = array_filter($data);
        $data['status'] = $data['type'] == '1' ? '0' : '1';
        unset($data['type']);		
        $result = ACoupon('activity/update', $data);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求失败：{$result['message']}");
        }
        return true;
    }
	
	/**
     * 活动延期
     *
     * @param array $data 
     * @throws \Exception
     */
    public static function laterActivity($data)
    {
		$result = ACoupon('activity/delay', $data);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求：{$result['message']}");
        }
		return $result;
    }
	
	 /**
     * 修改活动状态
     * @throws \Exception
     */
    public static function setStatus($data) {       
        $dt['activityId'] = strval($data['activityId']);
		$dt['status'] = strval($data['status']);
        $result = ACoupon('activity/update/status', $dt);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求失败：{$result['message']}");
        }
        return true;
    }
	
	
	
	/**
     * 活动终端查询
     *
     * @param number $idx 
     * @throws \Exception
     */
    public static function CouponTypeList()
    {
        $data['method'] = 'Coupon.getTypeList';	  
	    $result = AD($data);
        if ($result['error'] != '0000') {
            throw new \Exception("券类型列表接口请求失败");
        }
        return $result['result']['list'];
    }
	
	

}
