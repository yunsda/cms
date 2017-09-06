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
use service\GoodsService;
use controller\BasicAdmin;
use PHPExcel_IOFactory;
use PHPExcel;


/**
 * 制卡单据管理
 * Class Goods
 * @package app\admin\controller
 */
class Trade extends BasicAdmin
{
    /**
     * 短信明细列表界面展示
     */
    public function listSendedCoupon() {
		$res = \service\GoodsService::getSubOrgan();
		$list = \service\GoodsService::formartList($res['data'][0]['children']);
        $this->assign('list', $list);
        return $this->fetch('listSendedCoupon');
    }
    
    /**
     * 短信明细初始化数据
     */
    public function listSendedCouponData() {
        $limit  = input('post.limit', 0, 'intval');
		$offset = input('post.offset', 0, 'intval');
		$page   = floor($offset / $limit) + 1;
		
		$data['organId'] = $this->request->post('organId', '', 'strval');
		$data['phone'] = $this->request->post('phone', '', 'strval');
		$data['coupons'] = $this->request->post('coupons', '', 'strval');
		$data['activityName'] = $this->request->post('activityName', '', 'strval');
		$data['goodsName'] = $this->request->post('goodsName', '', 'strval');
		$data['goodsId'] = $this->request->post('goodsId', '', 'strval');
		$data['activityId'] = $this->request->post('activityId', '', 'strval');				
		$data['billId'] = $this->request->post('billId', '', 'strval');
		$data['billNote'] = $this->request->post('billNote', '', 'strval');
		$data['custName'] = $this->request->post('custName', '', 'strval');
		$data['custNote'] = $this->request->post('custNote', '', 'strval');		
		$data['startTime'] = $this->request->post('startTime', '', 'strval');
		$data['endTime'] = $this->request->post('endTime', '', 'strval');
		
		$data['cp'] = $page;        
        $data['pageSize'] = 20;
		foreach( $data as $k=>$v){   
			if( !$v )   
				unset( $data[$k] );   
		}   
		
		$res = \service\TradeService::listSendedCouponData($data);
		$list = $res['list'];
		foreach ($list as $key => $val) {
            $list[$key]['createTime']   = date('Y-m-d', $val['createTime'] / 1000);
			$list[$key]['endTime']   = date('Y-m-d', $val['couponMain']['endTime'] / 1000);
			$list[$key]['toSms']         = $val['toSms'] == '0' ? '未发送' : '已发送';
        }
		$data['rows'] = $list;
        $data['total'] = $res['total'];
        exit(json_encode($data));
    }

    /**
     * 核销统计展示界面
     */
    public function listPosCount() {
		
		$res = \service\GoodsService::getSubOrgan();
		$list = \service\GoodsService::formartList($res['data'][0]['children']);
        $this->assign('list', $list);
        return $this->fetch('listPosCoupon');
    }
    
    /**
     * 交易统计初始化数据
     */
    public function listPosCouponData() {
        $limit  = input('post.limit', 0, 'intval');
		$offset = input('post.offset', 0, 'intval');
		$page   = floor($offset / $limit) + 1;
		
		$data['organId'] = $this->request->post('organId', '', 'strval');
		$data['activityName'] = $this->request->post('activityName', '', 'strval');
		$data['goodsName'] = $this->request->post('goodsName', '', 'strval');
		$data['goodsId'] = $this->request->post('goodsId', '', 'strval');
		$data['activityId'] = $this->request->post('activityId', '', 'strval');				
		$data['custName'] = $this->request->post('custName', '', 'strval');
		$data['custNote'] = $this->request->post('custNote', '', 'strval');
		$data['startTime'] = $this->request->post('startTime', '', 'strval');
		$data['endTime'] = $this->request->post('endTime', '', 'strval');
		
		$data['cp'] = $page;        
        $data['pageSize'] = 20;
		
		$res = \service\TradeService::listPosCouponData($data);
		$list = $res['pageInfo']['list'];
		$data['rows'] = $list;
        $data['total'] = $res['pageInfo']['total'];
        exit(json_encode($data));
    }
    
    
    /**
     * 发码统计界面展示
     */
    public function statCouponSend() {
		$res = \service\GoodsService::getSubOrgan();
		$list = \service\GoodsService::formartList($res['data'][0]['children']);
        $this->assign('list', $list);
        return $this->fetch('statCouponSend');
    }
    
    /**
     * 发码统计初始化数据
     */
    public function statCouponSendData() {
        $limit  = input('post.limit', 0, 'intval');
		$offset = input('post.offset', 0, 'intval');
		$page   = floor($offset / $limit) + 1;
		
		$data['organId'] = $this->request->post('organId', '', 'strval');
		$data['activityName'] = $this->request->post('activityName', '', 'strval');
		$data['goodsName'] = $this->request->post('goodsName', '', 'strval');
		$data['goodsId'] = $this->request->post('goodsId', '', 'strval');
		$data['activityId'] = $this->request->post('activityId', '', 'strval');				
		$data['custName'] = $this->request->post('custName', '', 'strval');
		$data['custNote'] = $this->request->post('custNote', '', 'strval');
		$data['startTime'] = $this->request->post('startTime', '', 'strval');
		$data['endTime'] = $this->request->post('endTime', '', 'strval');
		
		$data['cp'] = $page;        
        $data['pageSize'] = 20;
		
		$res = \service\TradeService::statCouponSendData($data);
		$list = $res['pageInfo']['list'];
		
		$data['rows'] = $list;
        $data['total'] = $res['pageInfo']['total'];
        exit(json_encode($data));
    }
	
	/**
     * 验证明细展示界面
     */
    public function transaction() {
		$res = \service\GoodsService::getSubOrgan();
		$list = \service\GoodsService::formartList($res['data'][0]['children']);
        $this->assign('list', $list);
        return $this->fetch('transaction');
    }
    
    /**
     * 验证明细初始化数据
     */
    public function transactionData() {
        $limit  = input('post.limit', 0, 'intval');
		$offset = input('post.offset', 0, 'intval');
		$page   = floor($offset / $limit) + 1;
		
		$data['organId'] = $this->request->post('organId', '', 'strval');
		$data['coupons'] = $this->request->post('coupons', '', 'strval');
		$data['activityName'] = $this->request->post('activityName', '', 'strval');
		$data['goodsName'] = $this->request->post('goodsName', '', 'strval');
		$data['goodsId'] = $this->request->post('goodsId', '', 'strval');
		$data['activityId'] = $this->request->post('activityId', '', 'strval');				
		$data['custName'] = $this->request->post('custName', '', 'strval');
		$data['custNote'] = $this->request->post('custNote', '', 'strval');	
        $data['billId'] = $this->request->post('billId', '', 'strval');
		$data['billNote'] = $this->request->post('billNote', '', 'strval');
		$data['startTime'] = $this->request->post('startTime', '', 'strval');
		$data['endTime'] = $this->request->post('endTime', '', 'strval');
		
		$data['tranType'] = '2'; 
		$data['cp'] = $page;        
        $data['pageSize'] = 20;
		
		$res = \service\TradeService::transactionData($data);
		
		$list = $res['list'];
		
		foreach ($list as $key => $val) {
			$list[$key]['status']   = 1;
			$list[$key]['createTime']   = date('Y-m-d', $val['createTime'] / 1000);
        }
		$data['rows'] = $list;
        $data['total'] = $res['total'];
        exit(json_encode($data));
    }
	
	
	/*导出凭证*/
   public function dumpListSendData()
   {
        $data['cp']   = 1;
        $data['pageSize']  = 5000;
        
        try {
            (input('get.activityId')) && $data['activityId'] = input('get.activityId');
            (input('get.activityName')) && $data['activityName'] = input('get.activityName');
            (input('get.goodsId')) && $data['goodsId'] = input('get.goodsId');
            (input('get.goodsName')) && $data['goodsName'] = input('get.goodsName');
			(input('get.custName')) && $data['custName'] = input('get.custName');
            (input('get.custNote')) && $data['custNote'] = input('get.custNote');
            (input('get.BillNote')) && $data['BillNote'] = input('get.BillNote');
			(input('get.BillId')) && $data['BillId'] = input('get.BillId');
			(input('get.phone')) && $data['phone'] = input('get.phone');
			(input('get.coupons')) && $data['coupons'] = input('get.coupons');
            (input('get.organId')) && $data['organId'] = input('get.organId');
			(input('get.startTime')) && $data['startTime'] = input('get.startTime');
			(input('get.endTime')) && $data['endTime'] = input('get.endTime');
           	
         
            $res = \service\TradeService::listSendedCouponData($data);
            
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $list = $res['list'];
        
//         vendor("PHPExcel.PHPExcel");
        set_time_limit(0);
        header("Content-type:text/html;charset=utf-8");
        
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '流水号')
            ->setCellValue('B1', '券号')
            ->setCellValue('C1', '手机号码')
            ->setCellValue('D1', '活动名称')
            ->setCellValue('E1', '商品名称')
            ->setCellValue('F1', '客户名称')
            ->setCellValue('G1', '金额')
            ->setCellValue('H1', '有效日期')
			->setCellValue('I1', '发送时间')
			->setCellValue('J1', '发送状态');
         
        $i = 1;
        foreach((array)$list as $val){
            $i++;
			$toSms         = $val['toSms'] == '0' ? '未发送' : '已发送';
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$i, $val['id'])
            ->setCellValue('B'.$i, $val['couponMain']['code'])
            ->setCellValue('C'.$i, $val['phone'])
            ->setCellValue('D'.$i, $val['bill']['goods']['activity']['name'])
            ->setCellValue('E'.$i, $val['bill']['goods']['name'])
            ->setCellValue('F'.$i, $val['bill']['goods']['activity']['custName'])
            ->setCellValue('G'.$i, $val['bill']['goods']['money'])
            ->setCellValue('H'.$i, date('Y-m-d', $val['couponMain']['endTime'] / 1000))
			->setCellValue('I'.$i, date('Y-m-d', $val['createTime'] / 1000))
			->setCellValue('J'.$i, $toSms);
//             if ($billId) { continue; }
            
           
        }
        
        $filename = "trade_" . date('YmdHis');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
   }
   
   /*导出交易核销统计*/
   public function dumpPosCountData()
   {
        $data['cp']   = 1;
        $data['pageSize']  = 5000;
        
        try {
            (input('get.activityId')) && $data['activityId'] = input('get.activityId');
            (input('get.activityName')) && $data['activityName'] = input('get.activityName');
            (input('get.goodsId')) && $data['goodsId'] = input('get.goodsId');
            (input('get.goodsName')) && $data['goodsName'] = input('get.goodsName');
			(input('get.custName')) && $data['custName'] = input('get.custName');
            (input('get.custNote')) && $data['custNote'] = input('get.custNote');
            (input('get.BillNote')) && $data['BillNote'] = input('get.BillNote');
			(input('get.BillId')) && $data['BillId'] = input('get.BillId');
			(input('get.phone')) && $data['phone'] = input('get.phone');
			(input('get.coupons')) && $data['coupons'] = input('get.coupons');
            (input('get.organId')) && $data['organId'] = input('get.organId');
			(input('get.startTime')) && $data['startTime'] = input('get.startTime');
			(input('get.endTime')) && $data['endTime'] = input('get.endTime');
            $res = \service\TradeService::listPosCouponData($data);
            
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $list = $res['pageInfo']['list'];
        
//         vendor("PHPExcel.PHPExcel");
        set_time_limit(0);
        header("Content-type:text/html;charset=utf-8");
        
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '流水号')
            ->setCellValue('B1', '商品名称')
            ->setCellValue('C1', '活动名称')
            ->setCellValue('D1', '客户名称')
            ->setCellValue('E1', '所属机构')
            ->setCellValue('F1', '商品金额')
			->setCellValue('G1', '已核销数量')
			->setCellValue('H1', '已核销金额');
         
        $i = 1;
        foreach((array)$list as $val){
            $i++;
			$toSms         = $val['toSms'] == '0' ? '未发送' : '已发送';
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$i, $val['id'])
            ->setCellValue('B'.$i, $val['goods']['name'])
            ->setCellValue('C'.$i, $val['goods']['activity']['name'])
            ->setCellValue('D'.$i, $val['goods']['activity']['custName'])
            ->setCellValue('E'.$i, $val['goods']['activity']['organ']['name'])
            ->setCellValue('F'.$i, $val['goods']['money'])
            ->setCellValue('G'.$i, $val['times'])
			->setCellValue('H'.$i, $val['money']);
//             if ($billId) { continue; }
            
           
        }
        
        $filename = "trade_" . date('YmdHis');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
   }
   
   /*导出核销明细数据*/
   public function dumpTransactionData()
   {
        $data['cp']   = 1;
        $data['pageSize']  = 5000;
        $data['tranType'] = '2'; 
		
        try {
            (input('get.activityId')) && $data['activityId'] = input('get.activityId');
            (input('get.activityName')) && $data['activityName'] = input('get.activityName');
            (input('get.goodsId')) && $data['goodsId'] = input('get.goodsId');
            (input('get.goodsName')) && $data['goodsName'] = input('get.goodsName');
			(input('get.custName')) && $data['custName'] = input('get.custName');
            (input('get.custNote')) && $data['custNote'] = input('get.custNote');
            (input('get.BillNote')) && $data['BillNote'] = input('get.BillNote');
			(input('get.BillId')) && $data['BillId'] = input('get.BillId');
			(input('get.coupons')) && $data['coupons'] = input('get.coupons');
            (input('get.organId')) && $data['organId'] = input('get.organId'); 
            (input('get.startTime')) && $data['startTime'] = input('get.startTime');
			(input('get.endTime')) && $data['endTime'] = input('get.endTime');			
             
            $res = \service\TradeService::transactionData($data);
            
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $list = $res['list'];
        
//         vendor("PHPExcel.PHPExcel");
        set_time_limit(0);
        header("Content-type:text/html;charset=utf-8");
        
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '流水号')
            ->setCellValue('B1', '券号')
			->setCellValue('C1', '所属机构')
            ->setCellValue('D1', '活动名称')
            ->setCellValue('E1', '商品名称')
            ->setCellValue('F1', '客户名称')
            ->setCellValue('G1', '金额')
            ->setCellValue('H1', '核销时间');
         
        $i = 1;
        foreach((array)$list as $val){
            $i++;
			$toSms         = $val['toSms'] == '0' ? '未发送' : '已发送';
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$i, $val['id'])
            ->setCellValue('B'.$i, $val['couponMain']['code'])
			->setCellValue('C'.$i, $val['couponMain']['bill']['goods']['activity']['organ']['name'])
            ->setCellValue('D'.$i, $val['couponMain']['bill']['goods']['activity']['name'])
            ->setCellValue('E'.$i, $val['couponMain']['bill']['goods']['name'])
            ->setCellValue('F'.$i, $val['couponMain']['bill']['goods']['activity']['custName'])
            ->setCellValue('G'.$i, $val['couponMain']['bill']['goods']['money'])
            ->setCellValue('H'.$i, date('Y-m-d', $val['createTime'] / 1000));              
        }
        
        $filename = "trade_" . date('YmdHis');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
   }
    
   /*导出发码统计数据*/
   public function dumpStatData()
   {
        $data['cp']   = 1;
        $data['pageSize']  = 5000;
        
        try {
            (input('get.activityId')) && $data['activityId'] = input('get.activityId');
            (input('get.activityName')) && $data['activityName'] = input('get.activityName');
            (input('get.goodsId')) && $data['goodsId'] = input('get.goodsId');
            (input('get.goodsName')) && $data['goodsName'] = input('get.goodsName');
			(input('get.custName')) && $data['custName'] = input('get.custName');
            (input('get.custNote')) && $data['custNote'] = input('get.custNote');
            (input('get.organId')) && $data['organId'] = input('get.organId');      
            (input('get.startTime')) && $data['startTime'] = input('get.startTime');
			(input('get.endTime')) && $data['endTime'] = input('get.endTime');
            $res = \service\TradeService::statCouponSendData($data);
            
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $list = $res['pageInfo']['list'];
        
//         vendor("PHPExcel.PHPExcel");
        set_time_limit(0);
        header("Content-type:text/html;charset=utf-8");
        
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '流水号')
            ->setCellValue('B1', '商品名称')
            ->setCellValue('C1', '活动名称')
            ->setCellValue('D1', '客户名称')
			->setCellValue('E1', '机构名称')
            ->setCellValue('F1', '券码数量')
            ->setCellValue('G1', '商品金额')
            ->setCellValue('H1', '发券总金额')
            ->setCellValue('I1', '已核销数量')
			->setCellValue('J1', '已核销金额')
			->setCellValue('K1', '已发短信数量');
         
        $i = 1;
        foreach((array)$list as $val){
            $i++;
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$i, $val['id'])
            ->setCellValue('B'.$i, $val['goods']['name'])
            ->setCellValue('C'.$i, $val['goods']['activity']['name'])
            ->setCellValue('D'.$i, $val['goods']['activity']['custName'])
			->setCellValue('E'.$i, $val['goods']['activity']['organ']['name'])
            ->setCellValue('F'.$i, $val['couponNum'])
            ->setCellValue('G'.$i, $val['goods']['money'])
            ->setCellValue('H'.$i, $val['money'])
            ->setCellValue('I'.$i, $val['usedTimes'])
			->setCellValue('J'.$i, $val['usedMoney'])
			->setCellValue('K'.$i, $val['smsNum']);
//             if ($billId) { continue; }
            
           
        }
        
        $filename = "trade_" . date('YmdHis');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
   }
   
   
   
}
