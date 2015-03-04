<?php
namespace Home\Model;
use Think\Model;

class GiftModel extends Model{
	
	public function get_gift_by_id($gift_id){
		return $this->where("gift_id='{$gift_id}' ")->find();
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
