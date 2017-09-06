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

use service\CouponService;
use service\GoodsService;
use controller\BasicAdmin;
use PHPExcel_IOFactory;
use PHPExcel;

/**
 * 商品管理
 * Class Goods
 * @package app\admin\controller
 */
class Coupon extends BasicAdmin
{
	
	public function CodeStatus($key){
		$status = [
			'00' => '制券',
			'10' => '已出库',
			'20' => '已激活',
			'30' => '已使用',
			'40' => '已冻结',
			'50' => '已过期',
			'60' => '已作废',
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
     * 列表界面展示
     */
    public function billCoupons() {
        $billId = $this->request->request('billId', '', 'strip_tags');
        $this->assign('billId', $billId);
        return $this->fetch('billCoupons');
    }
    
    /**
     * ajax请求，返回bootstrap table列表数据
     */
    public function listData() {
        $limit  = input('post.limit', 0, 'intval');
        $offset = input('post.offset', 0, 'intval');
        $billId = input('post.billId', '', 'strip_tags');
        $page   = floor($offset / $limit) +1 ;
        $pageSize = $limit;

        empty($billId) && $billId = input('get.billId', '', 'strip_tags');
        try {
            $billId && $queryParams['billId'] = $billId;
            (input('post.activityId')) && $queryParams['activityId'] = input('post.activityId');
            (input('post.activityName')) && $queryParams['activityName'] = input('post.activityName');
            (input('post.goodsId')) && $queryParams['goodsId'] = input('post.goodsId');
            (input('post.goodsName')) && $queryParams['goodsName'] = input('post.goodsName');
            (input('post.billNote')) && $queryParams['billNote'] = input('post.billNote');
//             (input('post.couponId')) && $queryParams['couponId'] = input('post.couponId');
            (input('post.status')) && $queryParams['status'] = input('post.status');
            (input('post.startTime')) && $queryParams['startTime'] = input('post.startTime');
            (input('post.endTime')) && $queryParams['endTime'] = input('post.endTime');
            (input('post.custName')) && $queryParams['custName'] = input('post.custName');
            (input('post.custNote')) && $queryParams['custNote'] = input('post.custNote');
            (input('post.organId')) && $queryParams['organId'] = input('post.organId');
            
            //新增查询参数
            (input('post.coupons')) && $queryParams['coupons'] = input('post.coupons');
            (input('post.billNote')) && $queryParams['billNote'] = input('post.billNote');
            (input('post.isSms')) && $queryParams['isSms'] = input('post.isSms');
            
            $res = CouponService::pageList($queryParams, $page, $pageSize);
            
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
		$list = $res['rows'];
		foreach($list as $key=>$val){
			$list[$key]['startTime'] = date('Y-m-d',$val['startTime']/1000);
			$list[$key]['endTime'] = date('Y-m-d',$val['endTime']/1000);
			//$list[$key]['createTime'] = date('Y-m-d H:i:s',$val['createTime']/1000);
			$list[$key]['endTime'] = date('Y-m-d',$val['endTime']/1000);
 			$list[$key]['status'] = $this->CodeStatus($val['status']);
 			
 			!empty($list[$key]['createTime']) && $list[$key]['createTime'] = date('Y-m-d',$val['createTime']/1000);
		}
		$data['rows'] = $list;
		$data['total'] = $res['total'];
        exit(json_encode($data));
    }

    
    /**
     * 根据查询条件导出所有卡券 到excel文件
     */
    public function dumpData() {
        $billId = input('get.billId', '', 'strip_tags');
        $page   = 1;
        $limit  = 5000;                
        try {
            (input('get.activityId')) && $queryParams['activityId'] = input('get.activityId');
            (input('get.activityName')) && $queryParams['activityName'] = input('get.activityName');
            (input('get.goodsId')) && $queryParams['goodsId'] = input('get.goodsId');
            (input('get.goodsName')) && $queryParams['goodsName'] = input('get.goodsName');
            (input('get.status')) && $queryParams['status'] = input('get.status');
            (input('get.startTime')) && $queryParams['startTime'] = input('get.startTime');
            (input('get.endTime')) && $queryParams['endTime'] = input('get.endTime');
            (input('get.organId')) && $queryParams['organId'] = input('get.organId');
            
            //新增查询参数
            (input('get.coupons')) && $queryParams['coupons'] = input('get.coupons');
            (input('get.billId')) && $queryParams['billId'] = input('get.billId');
            (input('get.billNote')) && $queryParams['billNote'] = input('get.billNote');
            (input('get.isSms')) && $queryParams['isSms'] = input('get.isSms');
            $res = CouponService::pageList($queryParams, $page, $limit);
			
            
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $list = $res['rows'];
		
        
//         vendor("PHPExcel.PHPExcel");
        set_time_limit(0);
        header("Content-type:text/html;charset=utf-8");
        
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '券号')
            ->setCellValue('B1', '券密')
            ->setCellValue('C1', '面额')
            ->setCellValue('D1', '截止时间')
            ->setCellValue('E1', '创建时间')
            ->setCellValue('F1', '可使用次数')
            ->setCellValue('G1', '已使用次数')
            ->setCellValue('H1', '已使用金额')
            ->setCellValue('I1', '商品名')
            ->setCellValue('J1', '商品备注')
            ->setCellValue('K1', '活动名')
            ->setCellValue('L1', '活动备注')
            ->setCellValue('M1', '状态');
         
        $i = 1;
        foreach((array)$list as $val){
            $i++;
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$i, $val['code'])
            ->setCellValue('B'.$i, ' ' .$val['coupon']['password'])
            ->setCellValue('C'.$i, $val['money'])
            ->setCellValue('D'.$i, date('Y-m-d H:i:s',$val['endTime']/1000))
            ->setCellValue('E'.$i, date('Y-m-d H:i:s',$val['createTime']/1000))
            ->setCellValue('F'.$i, $val['times'])
            ->setCellValue('G'.$i, $val['usedTimes'])
            ->setCellValue('H'.$i,  $val['usedMoney'])
            ->setCellValue('I'.$i, $val['bill']['goods']['name'])
            ->setCellValue('J'.$i, $val['bill']['goods']['note'])
            ->setCellValue('K'.$i, $val['bill']['goods']['activity']['name'])
            ->setCellValue('L'.$i, $val['bill']['goods']['activity']['note'])
            ->setCellValue('M'.$i, $this->CodeStatus($val['status']));
        }
        
        $filename = "coupon_{$billId}_" . date('YmdHis');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
    /**
     * 制卡
     */
    public function add() {          
		if (!$this->request->isPost()){
			$goods_id = $this->request->get('goods_id', 0, 'strip_tags');
			if (empty($goods_id)) {
                 $this->error("请在相应的商品下操作");
			}
			$goods = GoodsService::getDetail($goods_id);			
			$this->assign('startTime',date("Y-m-d H:i:s"));
		    $this->assign('endTime',date("Y-m-d H:i:s",$goods['endTime']/1000));
			$this->assign('goods_id', $goods_id);
			return $this->fetch('');
		}else{
			$goods_id    = input('param.goods_id');
			$number      = input('param.number');
			$note        = input('param.note');
			$startTime   = input('param.startTime');
			$endTime     = input('param.endTime');
			$mode        = input('param.mode');
			if (empty($goods_id) || empty($number) || empty($startTime) || empty($endTime)) {
				$this->error("必要参数");
			}			
			try {
			    //成功返回制卡编号
                $res = CouponService::add($goods_id, $number,$note, $startTime, $endTime,$mode);
				
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
			
			//$this->success("成功", null, $res);
			echo json_encode(array('code'=>1,'id'=>$res['id']));
		}
    }
    

    /**
     * 卡券作废
     */
    public function invalid() {
        $couponIds  = $this->request->post('couponIds', '', 'strip_tags');
		$BillId  = $this->request->post('BillId', '', 'strip_tags');
        
		$data['coupons'] = $couponIds;
		$data['billId']  = $BillId;
        try {
            $res = CouponService::invalid($data);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        
        if ('00' === $res['error']) {
            $this->success("成功");
        } elseif ('C01' === $res['error']) {
            $this->result($res['data'], 'C01', "作废卡券部分失败，失败券码：{$res['data']}");
        } else {
            $this->error('未知异常，请重试！');
        }
    }
    
    /**
     * 卡劵冻结解冻
     */
    public function freez() {
        $couponIds  = $this->request->post('couponIds', '', 'strip_tags');
		$BillId     = $this->request->post('BillId', '', 'strip_tags');
        $flag       = $this->request->post('flag', -10, 'intval');        
		
		$data['coupons'] = $couponIds;
		$data['billId']  = $BillId;
		$data['flag']    = $flag;
        try {
            $res = CouponService::freezingorthawing($data);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        
        if ('00' === $res['error']) {
            $this->success("成功");
        } elseif ('C01' === $res['error']) {
            $this->result($res['data'], 'C01', "失败券码：{$res['data']}");
        } else {
            $this->error('未知异常，请重试！');
        }
    }
	
	/*卡券延期*/
	public function couponDelay(){
		if (!$this->request->isPost()){
			$activityId = $this->request->get('activity_id', '', 'strval');
			$goodsId = $this->request->get('goods_id', '', 'strval');
			$billId = $this->request->get('bill_id', '', 'strval');
			$coupons = $this->request->get('coupons', '', 'strval');
			$this->assign('activityId',$activityId);
			$this->assign('goodsId',$goodsId);
			$this->assign('billId',$billId);
			$this->assign('coupons',$coupons);			
			return $this->fetch('coupondelay');
		}else{
			$activityId = $this->request->post('activityId', '', 'strval');
			$goodsId = $this->request->post('goodsId', '', 'strval');
			$billId = $this->request->post('billId', '', 'strval');
			$coupons = $this->request->post('coupons', '', 'strval');
			$data['datetime'] = $this->request->post('datetime', '', 'strval');
			$data['activityId'] = !empty($activityId) ? $activityId : '';
			$data['goodsId'] = !empty($goodsId) ? $goodsId : '';
			$data['billId'] = !empty($billId) ? $billId : '';
			$data['coupons'] = !empty($coupons) ? $coupons : '';
			
			try {
				$res = \service\CouponService::couponDelay($data);			
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
            //$this->success("成功");
			echo json_encode(array('code'=>1,'msg'=>'操作成功'));
		}
	}
    
}
