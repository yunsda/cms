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
use PHPExcel;
use PHPExcel_IOFactory;
use service\GoodsService;

/**
 * 商品管理
 * Class Goods
 * @package app\admin\controller
 */
class Goods extends BasicAdmin
{
    /**
     * 商品状态码说明
     * 
     * @var array
     */
    public static $goodsStatus = [
        '0' => '停售',
        '1' => '正常',
    ];
    public function goodsStatus($key){
		$status = [
			'0' => '停售',
			'1' => '正常',
		];
		return $status[$key];
	} 
    
    /**
     * 列表界面展示
     */
    public function index()
    {
        $res = \service\GoodsService::getSubOrgan();
		$list = \service\GoodsService::formartList($res['data'][0]['children']);
        $this->assign('list', $list);
        return $this->fetch('index');
    }
	
	
    
    /**
     * ajax请求，返回bootstrap table列表数据
     */
    public function listData() {
        
       
		$limit  = input('post.limit', 0, 'intval');
		$offset = input('post.offset', 0, 'intval');
		$page   = floor($offset / $limit) + 1;
		
		$data['activityName'] = $this->request->post('activityName', '', 'strval');
		$data['goodsName'] = $this->request->post('GoodsName', '', 'strval');
		$data['status'] = $this->request->post('status', '', 'strval');
		$data['money'] = $this->request->post('money', '', 'intval');
		$data['startTime'] = $this->request->post('startTime', '', 'strval');
		$data['endTime'] = $this->request->post('endTime', '', 'strval');
		$data['organId'] = $this->request->post('organId', '', 'strval');
		$data['custName'] = $this->request->post('custName', '', 'strval');
		$data['custNote'] = $this->request->post('custNote', '', 'strval');
		$data['goodsId'] = $this->request->post('goodsId', '', 'strval');
		$data['activityId'] = $this->request->post('activityId', '', 'strval');
		
		$data['cp'] = $page;        
        $data['pageSize'] = 20;
		
        try {			
			$res = GoodsService::pageList($data);  			
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
		$list = $res['rows'];
		
		foreach($list as $key=>$val){
			$list[$key]['createTime'] = date('Y-m-d',$val['createTime']/1000);
			$list[$key]['startTime'] = date('Y-m-d',$val['startTime']/1000);
			$list[$key]['endTime'] = date('Y-m-d',$val['endTime']/1000);
			$list[$key]['statusName'] = $this->goodsStatus($val['status']);
			$list[$key]['activityName'] = $val['activity']['name'];
			$list[$key]['custName'] = $val['activity']['custName'];
			$list[$key]['activityId'] = $val['activity']['id'];
			
		}
		$data['rows'] = $list;
		$data['total'] = $res['total'];
        exit(json_encode($data));
    }
	

    
    /**
     * 添加界面显示
     */
    public function add() {
        $activityId = $this->request->get('activity_id', 0, 'strval');
		$result = \service\ActivityService::getActivityDetail($activityId);
		
        if (empty($activityId)) {
            $this->error("请在相应活动下创建商品");
        }
		
        $this->assign('activity_id', $activityId);
		$this->assign('activityName', $result['name']);
		$this->assign('startTime',date("Y-m-d H:i:s",$result['startTime']/1000));
		$this->assign('endTime',date("Y-m-d H:i:s",$result['endTime']/1000));
        return $this->fetch('');
    }
    
    /**
     * 编辑界面显示
     */
    public function edit() {
        $id = $this->request->get('id', 0, 'strval');
        if (empty($id)) {
            $this->error("请求来源非法");
        }
        //获取详情
        $goods = GoodsService::getDetail($id);
        $this->assign('startTime',date("Y-m-d H:i:s",$goods['startTime']/1000));
		$this->assign('endTime',date("Y-m-d H:i:s",$goods['endTime']/1000));
        $this->assign('goods', $goods);
        return $this->fetch('');
    }

    /**
     * 保存操作
     */
    public function save() {
        $id         = $this->request->post('id', 0, 'strval');
        $activityId = $this->request->post('activity_id', 0, 'strval');
		$note       = $this->request->post('note', 0, 'strval');
        $name       = $this->request->post('name', '', 'trim');
        $money      = $this->request->post('money', '', 'trim');
		$negPrice      = $this->request->post('negPrice', '', 'trim');
		$startTime      = $this->request->post('startTime', '', 'trim');
		$endTime      = $this->request->post('endTime', '', 'trim');
        $oldStatus  = $this->request->post('old_status', 0, 'intval');
        
        if (empty($name) || empty($money)) {
            $this->error("必要参数不能为空");
        }
        
        try {
            if (empty($id)) {
                empty($activityId) && $this->error("必要参数不能为空");
                $res = GoodsService::add($activityId, $name, $money,$negPrice, $note,$startTime,$endTime);
            } else {
                $res = GoodsService::update($id, $name, $money,$negPrice,$note);
                //更新状态
//                 if ($oldStatus !== $status) {
//                     $res = GoodsService::updateStatus($id, $status);
//                 }
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        
        $this->success("成功");
    }
	
	public function setStatus(){
		$goodsId = $this->request->post('goodsId', 0, 'strval');
		$status = $this->request->post('status', 0, 'strval');
		$data['goodsId'] = $goodsId;
		$data['status'] = $status;
		try {
            $res = GoodsService::setStatus($data);  			
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        
        $this->success("成功");
	}
    
	/*商品延期*/
	public function laterGoods(){
		if (!$this->request->isPost()){
			$goodsId = $this->request->get('goods_id', '', 'strval');
			$activityId = $this->request->get('activityId', '', 'strval');
			$this->assign('id',$goodsId);
			$this->assign('activityId',$activityId);
			return $this->fetch('latergoods');
		}else{
			$data['datetime'] = $this->request->post('laterTime', '', 'strval');
			$data['goodsId'] = $this->request->post('id', '', 'strval');
			$data['activityId'] = $this->request->post('activityId', '', 'strval');
			try {
				$res = \service\GoodsService::laterGoods($data);			
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
            $this->success("成功");
		}
	}
}
