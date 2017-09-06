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
use PHPExcel_Cell;
use PHPExcel_IOFactory;
use service\BillService;
use service\CouponService;

/**
 * 制卡单据管理
 * Class Goods
 * @package app\admin\controller
 */
class Bill extends BasicAdmin
{
	
	private function billStatus($key){
		$status = [
			'0' => '未激活',
			'1' => '正常',
			'2' => '撤销',
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
    public function listData()
    {
        $offset = $this->request->post('offset', 0, 'intval');
        $type   = $this->request->post('type', '', 'strval');
        $limit  = input('post.limit', 0, 'intval');
        $offset = input('post.offset', 0, 'intval');
        $page   = floor($offset / $limit) + 1;

        try {
            $data['cp']  = $page;
            $data['pageSize'] = 20;
			$data['activityName'] = $this->request->post('activityName', '', 'strval');
			$data['goodsName']    = $this->request->post('goodsName', '', 'strval');
			$data['billId']       = $this->request->post('billId', '', 'strval');
			$data['billNote']         = $this->request->post('note', '', 'strval');
			$data['organId']      = $this->request->post('organId', '', 'strval');
			$data['custName']     = $this->request->post('custName', '', 'strval');
			$data['custNote']     = $this->request->post('custNote', '', 'strval');
			$data['goodsId']      = $this->request->post('goodsId', '', 'strval');
			$data['activityId']   = $this->request->post('activityId', '', 'strval');
            $res = BillService::pageList($data);

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $list = $res['rows'];

        foreach ($list as $key => $val) {
            $list[$key]['createTime']   = date('Y-m-d', $val['createTime'] / 1000);
			$list[$key]['startTime']   = date('Y-m-d H:i:s', $val['startTime'] / 1000);
            $list[$key]['activityName'] = $val['goods']['activity']['name'];
            $list[$key]['goodsName']    = $val['goods']['name'];
            $list[$key]['type_code']    = $val['type'];
            $list[$key]['type']         = $val['type'] == '1' ? '制卡单' : '短信销售单';
            $list[$key]['mode']         = $val['mode'] == '1' ? '自动激活' : '手动激活';
            $list[$key]['custName']     = $val['goods']['activity']['custName'];
			$list[$key]['status']       = $this->billStatus($val['status']);
        }
        $data['rows']  = $list;
        $data['total'] = $res['total'];
        exit(json_encode($data));
    }

    /**
     * 制卡
     */
    public function add()
    {
        if (!$this->request->isPost()) {
            $goods_id = $this->request->get('goods_id', 0, 'strip_tags');
            if (empty($goods_id)) {
                $this->error("请在相应的商品下操作");
            }
            $this->assign('goods_id', $goods_id);
            return $this->fetch('');
        } else {
            $goods_id  = input('param.goods_id');
            $number    = input('param.number');
            $startTime = input('param.startTime');
            $endTime   = input('param.endTime');
            if (empty($goods_id) || empty($number) || empty($startTime) || empty($endTime)) {
                $this->error("必要参数");
            }

            try {
                $res = CouponService::add($goods_id, $number, $startTime, $endTime);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }

            $this->success("成功");
        }
    }
	
	/*制卡单激活*/
	public function setNormal()
	{
		$billId = input('param.billId');
		try {
			$res = BillService::setNormal($billId);
		} catch (\Exception $e) {
			$this->error($e->getMessage());
		}
		echo json_encode(array('code'=>1,'msg'=>'激活成功'));
	}
	
	/*制卡单激活*/
	public function reBillCoupon()
	{
		$billId = input('param.billId');
		try {
			$res = BillService::reBillCoupon($billId);
		} catch (\Exception $e) {
			$this->error($e->getMessage());
		}
		echo json_encode(array('code'=>1,'msg'=>'操作成功'));
	}

    /**
     * 导入手机号(Excel文件：指定模板)
     * @return [type] [description]
     */
    public function importPhone()
    {
        if ($this->request->isPost()) {
            $billId = input('post.billId', '', 'strip_tags');
            if (empty($billId)) {
                $this->error('缺少制卡单据号！', '', 3);
                exit;
            }
            $send_num = input('post.send_num', 1, 'strip_tags');

            $file = explode('.', $_FILES['file_name']['name']);
            if (!in_array(end($file), array('xls', 'xlsx'))) {
                $this->error('请选择Excel文件！', '', 3);
                exit;
            }

            $path = $_FILES['file_name']['tmp_name'];
            if (empty($path)) {
                $this->error('请选择要上传的文件！', '', 3);
                exit;
            }

            set_time_limit(0);

            $ex_arr = $this->importExecl($path);
            if ($ex_arr['error'] == 1) {
                $this->error('导入失败');
                exit;
            }
            $escel_data_length = $ex_arr['data'][0]['Rows'] - 1; // 导入手机号数量
            if ($escel_data_length <= 0) {
                $this->error('导入文件无手机号');
                exit;
            }
            $excel_data = $ex_arr['data'][0]['Content']; // 导入手机号数据
            unset($excel_data[1]); // 去掉excel标题
            $phone_arr = array();
            foreach ($excel_data as $val) {
                if (!empty($val[0]) && strlen($val[0]) == 11 && preg_match("/^1[34578]\d{9}$/", $val[0])) {
                    $phone_arr[] = (string) $val[0];
                }
            }

            if (count($phone_arr) > 0) {
                $result = BillService::sendByEcodeToMsg(1, $billId, $phone_arr);
                if ($result) {
                    $this->success('发送成功');
                }
                $this->error('发送失败');
            } else {
                $this->error('导入文件无手机号，或文件内容格式有误！');
            }
        } else {
            $billId = input('get.billId', '', 'strip_tags');

            $this->assign('billId', $billId);
            return $this->fetch();
        }
    }

    /**
     * @param  $file   upload file $_FILES
     * @return array   array("error","message")
     * desc : excel导入
     */
    private function importExecl($file)
    {
        //初始化变量
        $PHPExcel = '';
        $array    = '';
        if (!file_exists($file)) {
            return array("error" => 1, 'message' => 'file not found!');
        }

        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        try {
            $PHPReader = $objReader->load($file);
        } catch (Exception $e) {

        }
        if (!isset($PHPReader)) {
            return array("error" => 1, 'message' => 'read error!');
        }

        $allWorksheets = $PHPReader->getAllSheets();
        $i             = 0;
        foreach ($allWorksheets as $objWorksheet) {
            $sheetname          = $objWorksheet->getTitle();
            $allRow             = $objWorksheet->getHighestRow(); //how many rows
            $highestColumn      = $objWorksheet->getHighestColumn(); //how many columns
            $allColumn          = PHPExcel_Cell::columnIndexFromString($highestColumn);
            $array[$i]["Title"] = $sheetname;
            $array[$i]["Cols"]  = $allColumn;
            $array[$i]["Rows"]  = $allRow;
            $arr                = array();
            $isMergeCell        = array(); //merge cells
            foreach ($objWorksheet->getMergeCells() as $cells) {
                foreach (PHPExcel_Cell::extractAllCellReferencesInRange($cells) as $cellReference) {
                    $isMergeCell[$cellReference] = true;
                }
            }
            for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
                $row = array();
                for ($currentColumn = 0; $currentColumn < $allColumn; $currentColumn++) {
                    $cell    = $objWorksheet->getCellByColumnAndRow($currentColumn, $currentRow);
                    $afCol   = PHPExcel_Cell::stringFromColumnIndex($currentColumn + 1);
                    $bfCol   = PHPExcel_Cell::stringFromColumnIndex($currentColumn - 1);
                    $col     = PHPExcel_Cell::stringFromColumnIndex($currentColumn);
                    $address = $col . $currentRow;
                    $value   = $objWorksheet->getCell($address)->getValue();
                    if (substr($value, 0, 1) == '=') {
                        return array("error" => 0, 'message' => 'can not use the formula!');
                        exit;
                    }

                    // if ($cell->getDataType() == PHPExcel_Cell_DataType::TYPE_NUMERIC) {
                    //     $cellstyleformat = $cell->getParent()->getStyle($cell->getCoordinate())->getNumberFormat();
                    //     $formatcode      = $cellstyleformat->getFormatCode();
                    //     if (preg_match('/^([$[A-Z]*-[0-9A-F]*])*[hmsdy]/i', $formatcode)) {
                    //         $value = gmdate("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($value));
                    //     } else {
                    //         $value = PHPExcel_Style_NumberFormat::toFormattedString($value, $formatcode);
                    //     }
                    // }
                    if ($isMergeCell[$col . $currentRow] && $isMergeCell[$afCol . $currentRow] && !empty($value)) {
                        $temp = $value;
                    } elseif ($isMergeCell[$col . $currentRow] && $isMergeCell[$col . ($currentRow - 1)] && empty($value)) {
                        $value = $arr[$currentRow - 1][$currentColumn];
                    } elseif ($isMergeCell[$col . $currentRow] && $isMergeCell[$bfCol . $currentRow] && empty($value)) {
                        $value = $temp;
                    }
                    $row[$currentColumn] = $value;
                }
                $arr[$currentRow] = $row;
            }
            $array[$i]["Content"] = $arr;
            $i++;
        }
        //spl_autoload_register(array('Think', 'autoload')); //must, resolve ThinkPHP and PHPExcel conflicts
        unset($objWorksheet);
        unset($PHPReader);
        unset($PHPExcel);
        unlink($file);
        return array("error" => 0, "data" => $array);
    }

    /**
     * 下载导入手机号指定模板
     * desc : excel导出
     */
    public function exportTemplate()
    {
        $expTitle    = '批量导入模板';
        $file_name   = iconv('utf-8', 'gb2312', $expTitle); //文件名称
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '手机号');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $file_name . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
}
