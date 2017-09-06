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
use service\GoodsService;

class Activity extends BasicAdmin
{
    
	private function ActivityStatus($key){
		$status = [
			'0' => '草稿',
			'1' => '待审核',
			'2' => '正常',
			'3' => '停售',
			'4' => '审核失败',
		];
		return $status[$key];
	} 
    /**
     * 活动列表
     */
    public function index()
    {
        $res = \service\GoodsService::getSubOrgan();
		$list = \service\GoodsService::formartList($res['data'][0]['children']);
        $this->assign('list', $list);
        return $this->fetch('index');
    }

	
	/*数据列表*/
	public function listData()
	{
		$type = $this->request->post('type', '', 'strval');
		$limit  = input('post.limit', 0, 'intval');
		$offset = input('post.offset', 0, 'intval');
		$page   = floor($offset / $limit) + 1;
		
        $data['cp'] = $page;        
        $data['pageSize'] = 20;
		
		$data['activityId'] = $this->request->post('activityId', '', 'strval');
		$data['activityName'] = $this->request->post('activityName', '', 'strval');
		$data['couponType'] = $this->request->post('couponType', '', 'strval');
		$data['status'] = $this->request->post('status', '', 'strval');
		$data['organId'] = $this->request->post('organId', '', 'strval');
		$data['custName'] = $this->request->post('custName', '', 'strval');
		$data['custNote'] = $this->request->post('custNote', '', 'strval');
			
        $result = \service\ActivityService::pageList($data);
		$list = $result['list'];
        
		foreach($list as $key=>$val){
			$list[$key]['startTime'] = date('Y-m-d',$val['startTime']/1000);
			$list[$key]['endTime'] = date('Y-m-d',$val['endTime']/1000);
			$list[$key]['createTime'] = date('Y-m-d',$val['createTime']/1000);
			$list[$key]['status'] = $this->ActivityStatus($val['status']);
			$list[$key]['coupon_type'] = $val['couponType']['name'];
		};
		
		$data['rows'] = $list;
		$data['total'] = $result['total'];
		echo json_encode($data);
	}
	
	/*创建活动*/
	public function add()
	{
		if (!$this->request->isPost()){
			$codeTypeList = \service\ActivityService::CouponTypeList();
			$this->assign('codeTypeList',$codeTypeList);
			return $this->fetch('form');
		}else{
			$data['organId'] = strval(session('user.organ_id'));
			$data['name'] = $this->request->post('name', '', 'strval');
			$data['note'] = $this->request->post('note', '', 'strval');
			$data['custName'] = $this->request->post('custName', '', 'strval');
			$data['custNote'] = $this->request->post('custNote', '', 'strval');
			$data['startTime'] = $this->request->post('startTime', '', 'strval');
			$data['endTime'] = $this->request->post('endTime', '', 'strval');
			$data['couponTypeId']  = $this->request->post('coupon_type_id', '', 'strval');
			$data['type']  = input('param.type');			
			
			if(empty($data['organId']) || empty($data['couponTypeId']) || empty($data['name']) || empty($data['startTime']) || empty($data['endTime'])){
				echo json_encode(array('code'=>0,'msg'=>'有必填项'));
				exit();
			}
			try {
				$res = \service\ActivityService::createActivity($data);			
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
            $this->success("成功");
			
			
		}
	}
	
	/*活动延期*/
	public function laterActivity(){
		if (!$this->request->isPost()){
			$activityId = $this->request->get('activity_id', '', 'strval');
			$this->assign('id',$activityId);
			return $this->fetch('lateractivity');
		}else{
			$data['datetime'] = $this->request->post('laterTime', '', 'strval');
			$data['activityId'] = $this->request->post('id', '', 'strval');
			try {
				$res = \service\ActivityService::laterActivity($data);			
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
            $this->success("成功");
		}
	}
	

	
	/*编辑活动*/
	public function edit()
	{
		if (!$this->request->isPost()){
			$activityId = $this->request->get('activity_id', '', 'strval');
			$result = \service\ActivityService::getActivityDetail($activityId);			
			$codeTypeList = \service\ActivityService::CouponTypeList();		            
			$this->assign('stime',date('Y-m-d H:i:s',$result['startTime']/1000));
		    $this->assign('etime',date('Y-m-d H:i:s',$result['endTime']/1000));
			$this->assign('info',$result);
			$this->assign('coupon_type_id',$result['couponType']['id']);
			$this->assign('codeTypeList',$codeTypeList);
			return $this->fetch('form');
		}else{
			$data['activityId'] = $this->request->post('id', '', 'strval');
			$data['name'] = $this->request->post('name', '', 'strval');
			$data['note'] = $this->request->post('note', '', 'strval');
			$data['custName'] = $this->request->post('custName', '', 'strval');
			$data['custNote'] = $this->request->post('custNote', '', 'strval');
			$data['startTime'] = $this->request->post('startTime', '', 'strval');
			$data['endTime'] = $this->request->post('endTime', '', 'strval');
			$data['couponTypeId']  =       $this->request->post('coupon_type_id', '', 'strval');
			$data['type']  = input('param.type');			
			
			if(empty($data['couponTypeId']) || empty($data['name']) || empty($data['startTime']) || empty($data['endTime'])){
				echo json_encode(array('code'=>0,'msg'=>'有必填项'));
				exit();
			}
			try {
				$res = \service\ActivityService::updateActivity($data);                		
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
            $this->success("成功");
			
		}
	}
	
	/*修改活动状态*/
	public function setStatus(){
		$activityId = $this->request->post('activityId', 0, 'strval');
		$status = $this->request->post('status', 0, 'strval');
		$data['activityId'] = $activityId;
		$data['status'] = $status;
		try {
            $res = \service\ActivityService::setStatus($data);  			
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        
        $this->success("成功");
	}
	
	/*终端详情*/
	public function getLastSaleDetail()
	{
		$idx = input('param.idx');
		$assignData = [
		    'title' => "终端详情-{$idx}",
		    'idx' => $idx,
		];
		return view('lastsaleinfo', $assignData);
	}
	
	/*数据列表*/
	public function listLastSaleDetailData()
	{
	    
	    $idx = input('param.idx', '', 'strip_tags');
	    if (!$idx) {
	        $this->error('活动号必须！');
	    }
	    $result = \service\ActivityService::getActivityDetail($idx);
	    $list = $result['sales'];
	    foreach ($list as $value) {
	        break;
	    }
	    
	    echo json_encode($list);
	}
	
}
