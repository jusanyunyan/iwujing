<?php
namespace Home\Controller;
use Think\Controller;

class MemberController extends Controller {
	public function index(){
		
		$this->assign('WEB_SITE_TITLE','个人中心 - 航天悟净');
    	$this->display();
	}
	
	public function profile(){
	
		$this->assign('WEB_SITE_TITLE','个人资料- 航天悟净');
    	$this->display();
	}
	
	public function address(){
		$this->assign('WEB_SITE_TITLE','个人资料- 航天悟净');
    	$this->display();
	}
	
	
	public function order(){
		$session = session('user_auth');
		$uid = $session['uid'];
		$order = D("Order")->get_all(array("user_id" => $uid));
		foreach ($order as $v){
			$v['createtime']=date("Y年m月d日 H:i", $v['createtime']);
			$orderGoods  = M("OrderGoods")->where(array("order_no" => $v['order_no']))->find();
			$goods = M("Goods")->where(array("goods_id" => $orderGoods['goods_id']))->find();
			$v['pic_url']=$goods['pic_url'];
			$v['info'] = $orderGoods;			
			$list[]=$v;
		}	
		$this->assign('order', $list);
		$this->assign('WEB_SITE_TITLE','我的订单 - 航天悟净');
		$this->display();
	}
	
	public function orderview($orderid){
		$orderInfo = D("Order")->where(array('order_no'=>$orderid))->find();	
		$orderInfo['date']=date("Y年m月d日", $orderInfo['createtime']);	
		$orderInfo['time']=date("H:i:s", $orderInfo['createtime']);
		$orderGoods  = M("OrderGoods")->where(array("order_no" => $orderid))->find();
		$goods = M("Goods")->where(array("goods_id" => $orderGoods['goods_id']))->find();
		$orderGoods['pic']=$goods['pic_url'];

		$result  = file_get_contents("http://api.zto.cn/json.aspx?Userid=iwujing&Pwd=I8WU96JI2KG&SrtjobNo=$orderInfo[ship_no]");
		$result = json_decode($result, true);		
		$express = array_reverse($result['traces']);
		
		switch ($orderInfo['status']){
			case 0: $status='未付款';break;
			case 1: $status='已付款';break;
			case 2: $status='已发货';break;
			case 3: $status='已收货';break;	
		}
		$orderInfo['statusvalue']=$status;
		$this->assign('orderInfo', $orderInfo);
		$this->assign('orderGoods', $orderGoods);
		$this->assign('express', $express);
		$this->assign('WEB_SITE_TITLE','订单详情 - 航天悟净');
		$this->display();
	}
}