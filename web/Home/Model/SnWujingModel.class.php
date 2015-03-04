<?php
namespace Home\Model;
use Think\Model;

class SnWujingModel extends Model{
	
	public function get_List_By_Code($fcode,$vcode)
	{
		return $this->where("fcode='{$fcode}' and vcode='{$vcode}'")->find();
	}
	
	public function get_List_By_Type($type)
	{
		return $this->where("type='{$type}'")->select();
	}
	
	public function get_last()
	{
		return $this->where()->order("id desc")->find();
	}
	
	public function saveInfo($i,$model,$goods_id,$typename,$sizename,$colorname,$fcode,$vcode,$dealer,$creattime,$updatetime,$status,$is_print){
		$Goods = D('SnWujing');

		$data = array(
			'id' => $i,
			'model' => $model,	
			'goods_id' => $goods_id,		
			'type'	=>	$typename,
			'size' => $sizename,
			'color' => $colorname,			
			'fcode' => $fcode,
			'vcode' => $vcode,
			'dealer' => $dealer,
			'createtime' => $creattime,
			'updatetime' => $updatetime,
			'status' => $status,
			'is_print' => $is_print
		);
		if($this->create($data)){
			$id = $this->add();			
			if($id){ 							
				return $id;				
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