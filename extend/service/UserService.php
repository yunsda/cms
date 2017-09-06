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


class UserService
{

     public $table = 'access';
        
	/**
     * 添加信息
     * @param array $where 条件
     * @return array 添加状态
     */
    public function add($data)
	{
        $res = db('access')->insert($data);
		if($res){
			return true;
		}
	}
	
	/**
     * 添加信息
     * @param array $where 条件
     * @return array 添加状态
     */
    public function create_user($data)
	{		
        $res = db('user')->insert($data);
		if($res){
			return true;
		}
	}
	
	/**
     * 添加信息
     * @param array $where 条件
     * @return array 添加状态
     */
    public function editUser($where,$data)
	{	
	    
        $res = db('user')->where($where)->update($data);
		if($res){
			return true;
		}
	}
	
	/*
	 * 删除权限节点
     * @param array $where 条件
     * @return array 添加状态
	*/
	public function delete($manager_id)
	{
		$data = ['manager_id' => $manager_id];
        return db('access')->where($data)->delete();
	}

}
