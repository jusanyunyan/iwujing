<?php
/**
 * Created by wujianke
 * Date: 14-8-27
 * 功能: 和order_goods表相关的业务逻辑处理和数据处理
 */ 
namespace Home\Model;
use Think\Model;

class OrderGoodsModel extends Model{
	
	public function add_model($order_no,$goods_id,$goods_name,$goods_price,$discount,$quantity,$coupon_price,$total_price)
	{
		$data = array(
			'order_no'	=> $order_no,
			'goods_id'    => $goods_id,
			'goods_name'	=> $goods_name,
			'goods_price' 	   => $goods_price,
			'quantity'   => $quantity,
			'discount'	=> $discount,
			'coupon_price' => $coupon_price,
			'total_price'	=>	$total_price,	
		);
		if($this->create($data))
		{
			$vid=$this->add();			
			return $vid? $vid : 0; 			
		}
		else
		{
			return -1;
		}
	}
	
	public function get_List_By_Id($order_no)
	{
		return $this->where("order_no='{$order_no}'")->find();
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
        
	public function update_status($where,$data){
   		return $this->where($where)->save($data);
 	}
}