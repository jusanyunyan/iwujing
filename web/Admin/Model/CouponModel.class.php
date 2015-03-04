<?php
/**
 * Create by wujianke
 * Date: 2014-9-15
 * 功能： 和coupon表相关的数据处理
 */

namespace Admin\Model;
use Think\Model;

class CouponModel extends Model{
	
	/* 所有MODEL公共方法 */
	public function get_one($where,$field='')
	{
		return $this->field($field)->where($where)->find();
	}

	public function get_all($where,$field='')
	{
		return $this->field($field)->where($where)->select();
	}

	public function get_count($where,$field='')
	{
		return $this->field($field)->where($where)->count();
	}
	
	public function update_one($where,$data){
		return $this->where($where)->save($data);
 	}
}
