<?php
namespace Home\Model;
use Think\Model;

class CouponTypeModel extends Model{
	
	public function get_info_By_Id($id)
	{
		return $this->where("id='{$id}'")->find();
	}
	
	
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
}