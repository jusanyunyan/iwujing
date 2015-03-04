<?php
/*
 * Create by wujianke
 * Date: 14-8-22
 * 功能: 和Order表相关数据处理
 */
namespace Home\Model;
use Think\Model;

class OrderModel extends Model{
	
	public function saveOrder($uid,$goods_id,$quantity,$receivename,$mobile,$address,$discount,$coupon){
		$Goods = D('Goods');
   		$details = $Goods->getInfoById($goods_id);
   		
   		$lastList = $this->get_last();
   		if(is_array($lastList) && $lastList['order_no']){
   			$order_no = $lastList['order_no']+1;
   		}else{
   			$order_no = 1000000001;
   		}
   		
   		$couponsModel = D('Coupons');
   				
		$data = array(
			'order_no' => $order_no,
			'user_id' => $uid,			
			'receiver_name'	=>	$receivename,
			'receiver_mobile' => $mobile,
			'receiver_address' => $address,			
			'status' => 0,
			'coupon' => $coupon,
			'createtime' =>	NOW_TIME,
		);
		if($this->create($data)){
			$orderid = $this->add();		
			if($orderid){ 			
				$info = $this->get_Order_By_Id($orderid);
				$order_no = $info['order_no'];
				$coupon_price = 0;
				if($coupon){
					$couponModel = D("Coupon");				
	    			$list = $couponModel->get_List_By_Number($coupon);
	    			$couponModel->where("id = $list[id]")->save(array('status' => 1)); 
	    			
	    			$type_id = $list['type_id'];
		    		$couponTypeModel = D("CouponType");
		    		$couponType = $couponTypeModel->get_info_By_Id($type_id);
		    		$coupon_price = $couponType['value'];
				}
				$goods_name = $details['goods_name'];
				$goods_price = $details['goods_price'];
				$total_price = $quantity*$details['goods_price']*(1-$discount)-$coupon_price;
	 			
				$Order_goods=D('OrderGoods');
				$r=$Order_goods->add_model($order_no,$goods_id,$goods_name,$goods_price,$discount,$quantity,$coupon_price,$total_price);
				if($r){
					return $orderid;
				}
			}		
		}
	}
	
	public function get_Order_By_Id($orderid)
	{
		return $this->where("order_id='{$orderid}'")->find();
	}
	
	public function get_Order_By_Uid($uid)
	{
		return $this->where("user_id='{$uid}'")->find();
	}
	
	public function get_last()
	{
		return $this->where()->order("order_id desc")->find();
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