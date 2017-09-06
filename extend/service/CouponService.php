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


class CouponService
{
    /**
     * 制卡
     *
     * @param String $activityId    活动编号
     * @param String $name  商品名称
     * @param Integer $money    商品金额
     * @param String $status    商品状态（0-草稿、1-待审核）
     * @return
     *  @example {"data":{"activityId":"596c0d229cca5f0d440078d2","createTime":1501031101019,"creator":"595df1df9cca5f52d40055b2","id":"5977eabd3081210c38eb86a7","money":123,"name":"第一个商品","status":"1"},"error":"00","message":"添加成功"}
     *
     * @throws \Exception
     */
    public static function add($goods_id, $number,$note, $startTime, $endTime,$mode) {
        $data = [
            'goodsId'   => strval($goods_id),
            'number'    => intval($number),
			'note'      => strval($note),
            'startTime' => strval($startTime),
            'endTime'   => strval($endTime),
			'mode'      => strval($mode),
        ];
		
        $result = ACoupon('coupon/build', $data);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求失败：{$result['message']}");
        }
        
        return $result['data'];
    }
    
    /**
     * 卡劵作废
     *
     * @param String $couponIds   卡券编号（多个劵号之间使用逗号隔开）
     * @param String $couponType  券类型
     * @return
     *  @example 
     *  1、全部成功：{"error":"00","message":"作废成功"}
     *  2、部分成功：{"data":"03000000000000000064788511980113","error":"10","message":"作废部分成功"}
     *  3、全部失败：{"error":"10","message":"作废失败"}
     * @throws \Exception
     */
    public static function invalid($data) {
        if (is_array($data['coupons'])) {
            $couponIds = implode(',', $data['coupons']);
        } else {
            $couponIds = trim($data['coupons']);
        }
        $dt['coupons'] = strval($couponIds);
		$dt['billId'] = strval($data['billId']);
		foreach( $dt as $k=>$v){   
			if( !$v )   
				unset( $dt[$k] );   
		}		
        $result = ACoupon('coupon/invalid', $dt);
        
        //初始化返回数据
        $ret = ['error'=>'', 'data'=>[], 'message' => ''];
        
        if ($result['error'] != '00') {
            if (empty($result['data'])) {
                // 全部失败
                throw new \Exception("接口请求失败：{$result['message']}");
            } else {
                // 部分成功，返回失败的券码
                $ret = ['error'=>'C01', 'data' => $result['data'], 'message' => '作废卡券部分失败'];
                return $ret;
            }
        }
        
        $ret = ['error'=>'00'];
        return $ret;
    }
    
    /**
     * 卡劵冻结解冻
     *
     * @param String $couponIds   卡券编号（多个劵号之间使用逗号隔开）
     * @param String $couponType  券类型
     * @param String $flag  冻结解冻标志（0-冻结，1-解冻）
     * @return
     *  @example
     *  1、全部成功：{"error":"00","message":"冻结成功"}
     *  2、部分成功：{"data":"03000000000000000064788511980113","error":"10","message":"冻结部分成功"}
     *  3、全部失败：{"error":"10","message":"冻结失败"}
     * @throws \Exception
     */
    public static function freezingorthawing($data) {
        if (is_array($data['coupons'])) {
            $couponIds = implode(',', $data['coupons']);
        } else {
            $couponIds = trim($data['coupons']);
        }
		$dt['coupons'] = strval($data['coupons']);
		$dt['billId']  = strval($data['billId']);
		foreach( $dt as $k=>$v){   
			if( !$v )   
				unset( $dt[$k] );   
		}		
		$dt['flag']    = strval($data['flag']);
        $result = ACoupon('coupon/freezingorthawing', $dt);
        
        //初始化返回数据
        $ret = ['error'=>'', 'data'=>[], 'message' => ''];
        
        if ($result['error'] != '00') {
            if (empty($result['data'])) {
                // 全部失败
                throw new \Exception("接口请求失败：{$result['message']}");
            } else {
                // 部分成功，返回失败的券码
                $ret = ['error'=>'C01', 'data' => $result['data'], 'message' => '冻结卡券部分失败'];
                return $ret;
            }
        }
        
        $ret = ['error'=>'00'];
        return $ret;
    }
    
    /**
     * 通过单据编号，进行卡券分页查询
     *
     * @param number $page
     * @param number $pageSize
     * @throws \Exception
     */
    public static function ListAllByBillid($billId)
    {
        $data = [
            'billId'   => strval($billId),
        ];
        $result = ACoupon('bill/detail', $data);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求：{$result['message']}");
        }
        
        $ret = [
            'rows' => $result['data']['coupons'],
            'total' => count($result['data']['coupons']),
        ];
//         $ret = [
//             'rows' => $result['data']['list'],
//             'total' => $result['data']['total']
//         ];
        return $ret;
    }
    
    
    /**
     * 通过单据编号，进行卡券分页查询
     *
     * @param number $page
     * @param number $pageSize
     * @throws \Exception
     */
    public static function pagelistByBillid($billId, $page=1, $pageSize=20)
    {
        $data = [
            'billId'   => strval($billId),
            'cp'         => intval($page),
            'pageSize'   => intval($pageSize)
        ];
        $result = ACoupon('bill/coupon/page', $data);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求：{$result['message']}");
        }
        $rows = [];
        foreach ($result['data']['couponMainPage']['list'] as $value) {
            $value['createTime'] =  $result['data']['createTime'];
            $value['billId']     =  $result['data']['id'];
            $value['billName']   =  $result['data']['name'];
            $rows[] = $value;
        }
        
        $ret = [
            'rows' => $rows,
            'total' => count($rows)
        ];
        return $ret;
    }
    
    /**
     * 商品分页查询
     *
     * @param number $page
     * @param number $pageSize
     * @throws \Exception
     */
    public static function pageList($queryParams, $page=1, $pageSize=20)
    {
        $data = [
//             'billId'    => strval($queryParams['billId']),
            'cp'        => intval($page),
            'pageSize'  => intval($pageSize)
        ];
        isset($queryParams['billId']) && $data['billId'] = $queryParams['billId'];
        isset($queryParams['billNote']) && $data['billNote'] = $queryParams['billNote'];
        isset($queryParams['coupons']) && $data['coupons'] = $queryParams['coupons'];
        isset($queryParams['organId']) && $data['organId'] = $queryParams['organId'];
        
//         isset($queryParams['couponId']) && $data['couponIds'] = $queryParams['couponId'];
        isset($queryParams['isSms']) && $data['isSms'] = $queryParams['isSms'];
        
        isset($queryParams['activityId']) && $data['activityId'] = $queryParams['activityId'];
        isset($queryParams['activityName']) && $data['activityName'] = $queryParams['activityName'];
        isset($queryParams['goodsId']) && $data['goodsId'] = $queryParams['goodsId'];
        isset($queryParams['goodsName']) && $data['goodsName'] = $queryParams['goodsName'];
        isset($queryParams['status']) && $data['status'] = $queryParams['status'];
        isset($queryParams['startTime']) && $data['startTime'] = $queryParams['startTime'];
        isset($queryParams['endTime']) && $data['endTime'] = $queryParams['endTime'];
        
        $result = ACoupon('coupon/page', $data);
		
        if ($result['error'] != '00') {
            throw new \Exception("接口请求：{$result['message']}");
        }
        
        $ret = [
            'rows' => $result['data']['list'],
            'total' => $result['data']['total'],
        ];
        return $ret;
    }
    
    /**
     * 查询多个指定券码的详情
     *
     * @param String $couponIds   卡券编号（多个劵号之间使用逗号隔开）
     * @param String $couponType  券类型
     * @return
     * @example 
     *  {"data":[{"couponTime":1501032438422,"createTime":1501032438559,"creator":"595df1df9cca5f52d40055b2","endTime":1501430400000,"goodsId":"5977eabd3081210c38eb86a7","id":"04000000000000000089812321558381","money":123,"name":"A001","password":"8955710628151283","startTime":1500307200000,"status":"60","times":1,"usedMoney":0,"usedTimes":0},{"couponTime":1501032438422,"createTime":1501032438571,"creator":"595df1df9cca5f52d40055b2","endTime":1501430400000,"goodsId":"5977eabd3081210c38eb86a7","id":"04000000000000000090123801505517","money":123,"name":"A001","password":"1241990099078393","startTime":1500307200000,"status":"40","times":1,"usedMoney":0,"usedTimes":0}],"error":"00","message":"查询成功"}
     *
     * @throws \Exception
     */
    public static function getDetails($couponIds, $couponType) {
        if (is_array($couponIds)) {
            $couponIds = implode(',', $couponIds);
        } else {
            $couponIds = trim($couponIds);
        }
        
        $data = [
            'couponIds'  => strval($couponIds),
            'couponType' => strval($couponType),
            'flag'       => strval($flag),
        ];
        $result = ACoupon('coupon/list', $data);
        
        if ($result['error'] != '00') {
            throw new \Exception("接口请求：{$result['message']}");
        }
        
        $ret = [
            'rows' => $result['data'],
            'total' => count($result['data'])
        ];
        return $ret;
    }
	
	/**
     * 卡券延期
     *
     * @param array $data 
     * @throws \Exception
     */
    public static function couponDelay($data)
    {	
		if(!empty($data['activityId'])) $dt['activityId'] = strval($data['activityId']);
	    if(!empty($data['goodsId'])) $dt['goodsId'] = strval($data['goodsId']);
		if(!empty($data['billId'])) $dt['billId'] = strval($data['billId']);	
        if(!empty($data['coupons'])) $dt['coupons'] = strval($data['coupons']);			
		$dt['datetime'] = strval($data['datetime']);		
        $result = ACoupon('coupon/delay', $dt);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求：{$result['message']}");
        }
		return $result;
    }
	
}
