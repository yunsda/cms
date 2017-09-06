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

class BillService
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
        foreach( $data as $k=>$v){   
			if( !$v )   
				unset( $data[$k] );   
		}
        $result = ACoupon('bill/page', $data);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求：{$result['message']}");
        }

        $ret = [
            'rows'  => $result['data']['list'],
            'total' => $result['data']['total'],
        ];
        return $ret;
    }
	
	/*单据激活*/
		public static function setNormal($billId) {
        $data = [
            'billId'   => strval($billId),           
        ];		
        $result = ACoupon('bill/normal', $data);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求失败：{$result['message']}");
        }
        
        return $result['data'];
    }
	
	/*单据激活*/
		public static function reBillCoupon($billId) {
        $data = [
            'billId'   => strval($billId),           
        ];
        file_put_contents('./1.txt',print_r($data,true));		
        $result = ACoupon('coupon/build/continue', $data);
        if ($result['error'] != '00') {
            throw new \Exception("接口请求失败：{$result['message']}");
        }
        
        return $result['data'];
    }

    /**
     * 发送券码、券密到手机号
     * @param  int $billId    制卡单据号(批次号)
     * @param  int $send_type    发送类型 1新发送 2重发
     * @param  array  $phone_arr 手机号数组
     * @param  array $param    扩展字段(send_type=2时param内有券号)
     * @return [type]            [description]
     */
    public function sendByEcodeToMsg($send_type, $billId, $phone_arr = array(), $param = array())
    {
        $phone_num = count($phone_arr);
        if ($phone_num < 1) {
            return false;
        }
        $send_num = isset($param['send_num']) ? $param['send_num'] : 1;

        $ecode_arr = array();
        if ($send_type == 1) {
            if (empty($billId)) {
                return false;
            }
            // 批量发送，获取批次下小于等于指定数量的券码、券密(该批次下有效券数量小于指定数量时返回剩余券码即可)
            $data = array(
                'billId'   => strval($billId),
                'cp'       => 1,
                'pageSize' => $phone_num * $send_num,
                'status'   => '20',
            );
            $result = ACoupon('coupon/page', $data);
            if ($result['error'] != '00') {
                return false;
            }
            foreach ($result['data']['list'] as $value) {
                $ecode_arr[] = array(
                    'id'    => $value['id'],
                    'ecode' => $value['code'],
                    'psw'   => $value['coupon']['password'],
                    'money' => $value['coupon']['money'],
                );
            }
        } elseif ($send_type == 2) {
            if (empty($param['couponIds'])) {
                return false;
            }
            // 重发，获取单个券信息
            $data = array(
                'coupons' => strval($param['couponIds']),
            );
            $result = ACoupon('coupon/list', $data);
            if ($result['error'] != '00') {
                return false;
            }

            $ecode_arr[] = array(
                'id'    => $result['data'][0]['id'],
                'ecode' => $result['data'][0]['code'],
                'psw'   => $result['data'][0]['coupon']['password'],
                'money' => $result['data'][0]['coupon']['money'],
            );
        }

        // 获取短信模版
        // $msg_content = '';
        $synchro_data = array();
        $ecode_key    = 0;
        foreach ($phone_arr as $key => $val) {
            for ($i = 0; $i < $send_num; $i++) {
                if (isset($ecode_arr[$ecode_key])) {
                    // 发送短信
                    $dt = array(
                        'mobile'     => $val,
                        'client'     => '12',
                        'content'    => '恭喜您收到价值' . $ecode_arr[$ecode_key]['money'] . '元的卡券，券号' . $ecode_arr[$ecode_key]['ecode'] . '，券密：' . $ecode_arr[$ecode_key]['psw'],
                        'templateId' => '3052503',
                        // 'params'     => array($ecode_arr[$key]['money'], '', $ecode_arr[$key]['ecode'], $ecode_arr[$key]['psw']),
                        'params'     => array($ecode_arr[$ecode_key]['money'], '', '1234', '5678'),
                    );
                    // $ret = self::sendSms($dt);

                    // 发送短信结果关联券码
                    $synchro_data[] = array(
                        'phone'      => strval($val),
                        'toSms'      => $ret['error'] == '00' ? '1' : '0',
                        'template'   => strval($dt['templateId']),
                        'content'    => strval($dt['content']),
                        'couponMain' => array('id' => strval($ecode_arr[$ecode_key]['id'])),
                    );

                    $ecode_key++;
                } else {
                    break 2;
                }
            }
        }
        
        // 同步到短信销售单
        $result = ACoupon('billsms/addlist', array('data' => json_encode($synchro_data), 'billId' => $billId));

        return true;
    }

    private function sendSms($data = array())
    {
        $dat = self::_getToken();
        // log::write("短信平台返回ret：" . print_r($dat, true));
        $timestamp             = date('Y-m-d H:i:s', time());
        $sendData['appid']     = 'ABCDE1';
        $sendData['timestamp'] = $timestamp;

        $sendData['client_idx'] = 10;
        $sendData['mobile']     = $data['mobile'];
        $sendData['content']    = $data['content'];
        $sendData['platformId'] = '3';
        $sendData['templateId'] = '3052503';
        $sendData['params']     = json_encode($data['params']);
        $sendData['appkey']     = 'AF45a4sdfdfsasdf1v2asdf45asdfB22';
        $sendData['sign']       = md5('ABCDE1' . $timestamp . $sendData['client_idx'] . $sendData['mobile'] . $sendData['content'] . self::_getToken());
        $sendData['method']     = 'Sms.sendSms';
        // log::write("请求短信平台：" . print_r($sendData, true));
        $res = DoPost('http://sms.ukeban.net/', $sendData);
        $ret = json_decode($res, true);
        // log::write("短信平台返回：" . print_r($ret, true));
        return $ret;
    }

    //获取验签
    private function _getToken()
    {
        $timestamp             = date('Y-m-d H:i:s', time());
        $sendData['appid']     = 'ABCDE1';
        $sendData['timestamp'] = $timestamp;
        $sendData['method']    = 'Token.getToken';
        $sendData['appkey']    = 'AF45a4sdfdfsasdf1v2asdf45asdfB22';
        $sendData['sign']      = md5('ABCDE1' . $timestamp . 'AF45a4sdfdfsasdf1v2asdf45asdfB22' . 'Token.getToken');
        $res                   = DoPost('http://sms.ukeban.net/', $sendData);
        $ret                   = json_decode($res, true);

        if ($ret['error'] == '00') {
            return $ret['result']['access_token'];
        } else {
            return '';
        }
    }
}
