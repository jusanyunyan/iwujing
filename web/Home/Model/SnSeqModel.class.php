<?php
namespace Home\Model;
use Think\Model;

class SnSeqModel extends Model{
	
	public function saveInfo($sn,$sequence,$gift_name,$icon,$total_time,$cycle_time,$preset_time,$remind_time,$status){  				
		$data = array(		
			'sn'	=>	$sn,
			'sequence' => $sequence,
			'gift_name' => $gift_name,
			'icon' => $icon,
			'total_time' => $total_time,
			'cycle_time' => $cycle_time,
			'preset_time' => $preset_time,
			'remind_time' => $remind_time,	
			'createtime' =>	NOW_TIME,
			'status' => $status,
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
	
	public function get_List_By_sn($sn){
		return $this->where("sn='{$sn}' and status=1")->select();
	}
	
	public function getListBySnAndSeq($sn,$sequence){
		return $this->where("sn='{$sn}' and sequence='{$sequence}'")->find();
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
