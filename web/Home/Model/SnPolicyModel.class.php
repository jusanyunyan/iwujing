<?php
namespace Home\Model;
use Think\Model;

class SnPolicyModel extends Model{
	
	public function saveInfo($sn,$policy_id,$status){  				
		$data = array(		
			'sn'	=>	$sn,
			'policy_id' => $policy_id,
			'status' => $status,
			'createtime' =>	NOW_TIME,
			'updatetime' =>	NOW_TIME,
		);
		if($this->create($data)){
			$id = $this->add();		
			if($id){ 			
				return $id;
			}else{
				return -1;
			}		
		}
	}
	
	public function get_Info_By_sn($sn){
		return $this->where("sn='{$sn}' and status=1 and is_delete=0")->find();
	}
	
	public function get_Info_By_Id($sid){
		return $this->where("sid='{$sid}' and status=1 and is_delete=0")->find();
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
