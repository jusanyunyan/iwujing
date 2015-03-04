<?php
namespace Home\Model;
use Think\Model;

class GiftReceiveModel extends Model{
	
	public function saveInfo($uid,$sn,$receivename,$mobile,$email,$province,$city,$zone,$address,$status,$rid,$is_send){  				
		$data = array(
			'user_id' => $uid,			
			'sn'	=>	$sn,
			'username' => $receivename,
			'mobile' => $mobile,
			'email' => $email,
			'province' => $province,
			'city' => $city,
			'zone' => $zone,
			'address' => $address,	
			'status' => $status,
			'times' => $rid,	
			'is_send' => $is_send,	
			'createtime' =>	NOW_TIME,
		);
		if($this->create($data)){
			$id = $this->add();		
			if($id){ 
				$fcode = substr($sn, 0, 8);
				$vcode = substr($sn, 8);
				$arr['status']=1;
				
				$SnModel = D('Sn');
				$snList = $SnModel->get_List_By_Code($fcode,$vcode);	
				$SnWujingModel = D('SnWujing');			
				$snWujingList = $SnWujingModel->get_List_By_Code($fcode,$vcode);
				if(is_array($snList)){
					M("Sn")->where("fcode = $fcode and vcode = $vcode" )->save($arr);
				}elseif(is_array($snWujingList)){
					M("SnWujing")->where("fcode = $fcode and vcode = $vcode" )->save($arr);
				}				
				return $id;
			}else{
				return -1;
			}		
		}
	}

	public function getListBySn($sn){
		return $this->where("sn='{$sn}'")->select();
	}
	
	public function getListBySnAndSeq($sn,$sequence){
		return $this->where("sn='{$sn}' and times='{$sequence}'")->find();
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
