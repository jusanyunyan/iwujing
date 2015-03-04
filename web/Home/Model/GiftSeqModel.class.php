<?php
namespace Home\Model;
use Think\Model;

class GiftSeqModel extends Model{
	
	public function get_List_by_id($policy_id){
		return $this->where("policy_id='{$policy_id}'")->select();
	}
	
	public function get_List_By_Seq($policy_id,$sequence){
		return $this->where("policy_id='{$policy_id}' and sequence='{$sequence}'")->find();
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
