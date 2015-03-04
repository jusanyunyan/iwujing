<?php
namespace Home\Model;
use Think\Model;

class GiftPolicyModel extends Model{
	
	public function get_policy_by_goodsId($goods_id){
		return $this->where("goods_id='{$goods_id}' and status=1 and pretermit=1 ")->find();
	}
	
	public function getListById($policy_id){
		return $this->where("policy_id='{$policy_id}'")->find();
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
