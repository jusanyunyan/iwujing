<?php
namespace Home\Model;
use Think\Model;

class GiftCmsTmpModel extends Model{
	
	public function saveInfo($mobile,$status){  				
		$data = array(
			'mobile' => $mobile,			
			'status'	=>	$status,
			'createtime' =>	NOW_TIME,
			'updatetime' => NOW_TIME,
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