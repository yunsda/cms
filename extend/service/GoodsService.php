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


class GoodsService
{
    /**
     * 商品分页查询
     *
     * @param number $page
     * @param number $pageSize
     * @throws \Exception
     */
    public static function pageList($data)
    {   
        $status = $data['status'];	
        foreach( $data as $k=>$v){   
			if( !$v )   
				unset( $data[$k] );   
		}
		if($status == '0'){
			$data['status'] = $status;
		}else{
			if(!empty($status)) $data['status'] = $status;
		}
		if($data['money']) $data['money'] = intval($data['money']);
        $result = ACoupon('goods/page', $data);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求：{$result['message']}");
        }
        
        $ret = [
            'rows' => $result['data']['list'],
            'total' => $result['data']['total']
        ];
        return $ret;
    }
	
	
    
    /**
     * 创建商品
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
    public static function add($activityId, $name, $money,$negPrice,$note,$startTime,$endTime) {
        $data = [
            'activityId' => strval($activityId),
            'name' => strval($name),
            'money' => intval($money),
			'note' =>strval($note),
			'startTime'=>strval($startTime),
			'endTime'=>strval($endTime),
			'negotiatedMoney'=>intval($negPrice)
        ];
        $result = ACoupon('goods/add', $data);		
        if ($result['error'] != '00') {
            throw new \Exception("接口请求失败：{$result['message']}");
        }
        return $result['data'];
    }
    
    /**
     * 修改商品状态
     *
     * @param String $goodsId  商品编号
     * @param String $status    商品状态（2-正常、3-停售）
     * @return
     * @example 正常返回：{"error":"00","message":"修改成功"}
     *          错误返回：{"error":"10","message":"非草稿状态不能编辑修改"}
     *
     * @throws \Exception
     */
    public static function setStatus($data) {       
        $dt['goodsId'] = strval($data['goodsId']);
		$dt['status'] = strval($data['status']);
        $result = ACoupon('goods/update/status', $dt);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求失败：{$result['message']}");
        }
        return true;
    }
    
    /**
     * 修改商品
     * 
     * @param String $goodsId  商品编号
     * @param String $name  商品名称
     * @param Integer $money    商品金额
     * @param String $status    商品状态（0-草稿、1-待审核）
     * @return 
     * @example 正常返回：{"error":"00","message":"修改成功"}
     *          错误返回：{"error":"10","message":"非草稿状态不能编辑修改"}
     * 
     * @throws \Exception
     */
    public static function update($goodsId, $name='', $money='',$negPrice='',$note='') {
        $data = [
            'goodsId' => strval($goodsId),
        ];
        !empty($name) && $data['name'] = strval($name);
		!empty($note) && $data['note'] = strval($note);
        !empty($money) && $data['money'] = intval($money);
		!empty($negPrice) && $data['negotiatedMoney'] = intval($negPrice);
		
        $result = ACoupon('goods/update', $data);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求失败：{$result['message']}");
        }
        return true;
    }
    
    /**
     * 商品详情查询
     *
     * @param String $goodsId  商品编号
     * @return
     * @example 正常返回：{"error":"00","message":"修改成功"}
     *          错误返回：{"error":"10","message":"非草稿状态不能编辑修改"}
     *
     * @throws \Exception
     */
    public static function getDetail($goodsId) {
        $data = ['goodsId'=>strval($goodsId)];
        
        $result = ACoupon('goods/detail', $data);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求失败：{$result['message']}");
        }
        return $result['data'];
    }
	
	/**
     * 查询子机构
     * @param \think\db\Query|string $dbQuery 数据查询对象
     * @param array $where 额外查询条件
     * @return bool|null
     */
    public static function getSubOrgan()
    {
		$data = '';
        $result = ACoupon('organ/sub', $data);		
        if ($result['error'] != '00') {
            throw new \Exception("接口请求：{$result['message']}");
        }
		return $result;
    }
	
	/*格式化机构列表*/
	public static function formartList($list)
	{
		$cat = new \app\admin\org\Category(array('id', 'parentOrganId', 'name','cname'));
		$ret=$cat->getTree($list,session('user.organ_id'));
		return $ret;
	}
	
	/**
     * 商品延期
     * @param \think\db\Query|string $dbQuery 数据查询对象
     * @param array $where 额外查询条件
     * @return bool|null
     */
    public static function laterGoods($data)
    {		
        $result = ACoupon('goods/delay', $data);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求：{$result['message']}");
        }
		return $result;
    }
	
	
}
