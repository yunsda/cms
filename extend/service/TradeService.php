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


class TradeService
{

   
    
    /**
     * 凭证明细查询
     *
     * @param number $page
     * @param number $pageSize
     * @throws \Exception
     */
    public static function listSendedCouponData($data)
    {   
        foreach( $data as $k=>$v){   
			if( !$v )   
				unset( $data[$k] );   
		}		
		$result = ACoupon('billsms/page', $data);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求失败：{$result['message']}");
        }
        return $result['data'];
    }
	
	 /**
     * 验证明细查询
     *
     * @param number $page
     * @param number $pageSize
     * @throws \Exception
     */
    public static function listPosCouponData($data)
    {   
        foreach( $data as $k=>$v){   
			if( !$v )   
				unset( $data[$k] );   
		}
		
		$result = ACoupon('report/coupon/transaction/page', $data);		
        if ($result['error'] != '00') {
            throw new \Exception("接口请求失败：{$result['message']}");
        }
        return $result['data'];
    }
	
	/**
     * 发码统计
     *
     * @param number $page
     * @param number $pageSize
     * @throws \Exception
     */
    public static function statCouponSendData($data)
    {   
        foreach( $data as $k=>$v){   
			if( !$v )   
				unset( $data[$k] );   
		}
		
		$result = ACoupon('report/coupon/page', $data);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求失败：{$result['message']}");
        }
        return $result['data'];
    }
	
	/**
     * 交易统计
     *
     * @param number $page
     * @param number $pageSize
     * @throws \Exception
     */
    public static function transactionData($data)
    {   
        foreach( $data as $k=>$v){   
			if( !$v )   
				unset( $data[$k] );   
		}
		$result = ACoupon('transaction/page', $data);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求失败：{$result['message']}");
        }
        return $result['data'];
    }
	
	
	
	

}
