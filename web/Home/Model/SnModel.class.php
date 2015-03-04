<?php
namespace Home\Model;
use Think\Model;

class SnModel extends Model{
	
	public function get_List_By_Code($fcode,$vcode)
	{
		return $this->where("fcode='{$fcode}' and vcode='{$vcode}'")->find();
	}
	
	public function get_last()
	{
		return $this->where()->order("id desc")->find();
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