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
use service\DataService;
use think\Db;

/**
 * 系统日志管理
 * Class User
 * @package app\admin\controller
 */
class Log extends BasicAdmin
{

    /**
     * 指定当前数据表
     * @var string
     */
    public $table = 'SystemLog';

    /**
     * 日志列表
     */
    public function index()
    {
        $this->title = '系统操作日志';
        $get = $this->request->get();
        $actions = Db::name($this->table)->group('action')->column('action');
        $db = Db::name($this->table)->order('id desc');
        foreach (['action', 'content', 'username'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%{$get[$key]}%");
            }
        }
        $this->assign('actions', $actions);
        return $this->fetch('');
//         return parent::_list($db);
    }
    
    public function listData()
    {
        $get = $this->request->get();
        $actions = Db::name($this->table)->group('action')->column('action');
        $db = Db::name($this->table)->order('id desc');
        foreach (['action', 'content', 'username'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%{$get[$key]}%");
            }
        }
        $res =  parent::_bsList($db, $isPage = true, $isDisplay = false, $total = true);
        exit(json_encode($res));
    }

    /**
     * 列表数据处理
     * @param $data
     */
    protected function _listData_data_filter(&$data)
    {
		/*
        $ip = new \Ip2Region();
        foreach ($data as &$vo) {
            $result = $ip->btreeSearch($vo['ip']);
            $vo['isp'] = isset($result['region']) ? $result['region'] : '';
            $vo['isp'] = str_replace(['|0|0|0|0', '0', '|'], ['', '', ' '], $vo['isp']);
        }
		*/
    }

    /**
     * 日志删除操作
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("日志删除成功!", '');
        }
        $this->error("日志删除失败, 请稍候再试!");
    }

}
