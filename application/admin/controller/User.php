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
use service\UserService;
use think\Db;

/**
 * 系统用户管理控制器
 * Class User
 * @package app\admin\controller
 * @date 2017/02/15 18:12
 */
class User extends BasicAdmin
{

   

    /**
     * 用户列表
     */
    public function index()
    {  	
		return $this->fetch('index');
    }
	
	public function listData()
	{
		$user = $this->request->post('user', '', 'strval');
		$name = $this->request->post('name', '', 'strval');
		$note     = $this->request->post('note', '', 'strval');
		$status = $this->request->post('status', '', 'strval');
		$limit  = 20;
		$offset = input('post.offset', 0, 'intval');
		$page   = floor($offset / $limit) + 1;
		$where['organ_id'] = session('user.organ_id');
		if(!empty($user)) $where['user'] = array('like','%'.$user.'%');
		if(!empty($name)) $where['name'] = array('like','%'.$name.'%');
		if($status == '0' || $status == '1') $where['status'] = $status;
		if(!empty($note)) $where['note'] = array('like','%'.$note.'%');
		
		$start = ($page-1) * $limit;
		
		$list = Db::name('user')->where($where)->limit($start,$limit)->select();
		$count = Db::name('user')->where($where)->count();
		foreach ($list as $key => $val) {
            $list[$key]['time']         = date('Y-m-d', $val['time']);
			$list[$key]['type']         = $val['type'] == '1' ? '超级管理员' : '普通管理员';
			$list[$key]['status']       = $val['status'] == '1' ? '正常' : '禁用';
        }
		$data['rows'] = $list;
        $data['total'] = $count;
        exit(json_encode($data));
	}

    /**
     * 授权管理
     * @return array|string
     */
    public function auth()
    {		
		if (!$this->request->isPost()){
			$id = $this->request->get('id', '', 'strval');			
			$db = Db::name('node')->where('pid','0')->order('sort asc')->select();
			$myAccess = Db::name('access')->where('manager_id',$id)->select();
			foreach($myAccess as $val){
				$array[] = $val['purviewval'];
			}

            foreach($db as $k=>$v){
				$db[$k]['subdata'] = Db::name('node')->where('pid',$v['id'])->select();
			}			
			$this->assign('myAccess',$array);
			$this->assign('list',$db);
			$this->assign('id',$id);
			return $this->fetch('auth');
		}else{
			
			$access = $this->request->post('purviewval', '', 'strval');
			$access = explode(',',$access);
			$id = $this->request->post('id', '', 'strval');				
			\service\UserService::delete($id);
			foreach($access as $val){
				$data = ['purviewval'=>$val,'manager_id'=>$id];				
				\service\UserService::add($data);								
			}
            $this->success('设置成功');
            			
		}
		
    }

    /**
     * 用户添加
     */
    public function add()
    {
		if (!$this->request->isPost()){
			return $this->fetch('add');
		}else{
			$username = $this->request->post('username', '', 'strval');
            $password = $this->request->post('password', '', 'strval');
            $name     = $this->request->post('name', '', 'strval');			
			$note     = $this->request->post('note', '', 'strval');
			
			if(empty($username) || empty($password)){
				$this->error('参数必填');
			}			
            $data['user']            =       $username;
			$data['pwd']             =       md5($password);
			$data['name']            =       $name;
			$data ['note']           =       $note;
			$data['type']            =       '2';
			$data['organ_id']        =       session('user.organ_id');
			$data['status']          =       '1';
			$data['time']            =       time();
			try {
				$res = \service\UserService::create_user($data);	
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
            $this->success("添加成功");
		}
        
    }
	
	/**
     * 编辑用户
     */
    public function edit()
    {
		if (!$this->request->isPost()){
			$id = $this->request->get('id', '', 'intval');
			if(empty($id)) $this->error('参数错误');
			$info = Db::name('user')->where('id',$id)->find();
			$this->assign('info',$info);
			return $this->fetch('edit');
		}else{
			$id       = $this->request->post('id');			
			$note     = $this->request->post('note', '', 'strval');	
            $name     = $this->request->post('name', '', 'strval');	
            $user     = $this->request->post('username', '', 'strval');			
			
            $data['note']  = $note;
			$data['name']  = $name;
			$data['user']  = $user;
			
			try {
				$where['id'] = $id;
				$res = \service\UserService::editUser($where,$data);	
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
            $this->success("编辑成功");
		}
        
    }

    

    /**
     * 用户密码修改
     */
    public function setPassword()
    {
        if (!$this->request->isPost()) {
			$id = $this->request->get('id', '', 'intval');
			if(empty($id)) $this->error('参数错误');
			$info = Db::name('user')->where('id',$id)->find();
			$this->assign('info',$info);
            return $this->fetch('password');
        }else{
			$id = $this->request->post('id', '', 'intval');
			$password = $this->request->post('password', '', 'strval');
			$repassword = $this->request->post('repassword', '', 'strval');
			if(empty($password) || empty($repassword)) $this->error('密码不能为空');
			if($password !== $repassword){
				$this->error('两次密码输入不一样');
			}
			try {
				$where['id'] = $id;
				$data['pwd'] = md5($password);
				$res = \service\UserService::editUser($where,$data);	
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
			echo json_encode(array('code'=>1,'message'=>'充值成功'));
			
		}
        
    }

    /**
     * 表单数据默认处理
     * @param array $data
     */
    public function _form_filter(&$data)
    {
        if ($this->request->isPost()) {
            if (isset($data['authorize']) && is_array($data['authorize'])) {
                $data['authorize'] = join(',', $data['authorize']);
            }
            if (isset($data['id'])) {
                unset($data['username']);
            } elseif (Db::name($this->table)->where('username', $data['username'])->find()) {
                $this->error('用户账号已经存在，请使用其它账号！');
            }
        } else {
            $data['authorize'] = explode(',', isset($data['authorize']) ? $data['authorize'] : '');
            $this->assign('authorizes', Db::name('SystemAuth')->select());
        }
    }

    

    /**
     * 用户禁用
     */
    public function forbid()
    {
        $id = input('param.UserId');
		try {
			$where['id'] = $id;
			$data['status'] = '0';
			$res = \service\UserService::editUser($where,$data);	
		} catch (\Exception $e) {
			$this->error($e->getMessage());
		}
		echo json_encode(array('code'=>1,'message'=>'操作成功'));
    }

    /**
     * 用户禁用
     */
    public function resume()
    {
        $id = input('param.UserId');
		try {
			$where['id'] = $id;
			$data['status'] = '1';
			$res = \service\UserService::editUser($where,$data);	
		} catch (\Exception $e) {
			$this->error($e->getMessage());
		}
		echo json_encode(array('code'=>1,'message'=>'操作成功'));
    }

}
