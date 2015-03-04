<?php
namespace Home\Controller;
use Think\Controller;

class OrderController extends Controller {
    public function checkout($quantity='', $goods_id='', $receivename='', $mobile='',$address='',$coupon=''){
    	$session=session('user_auth'); 	
    	if(IS_POST){   	    		
    		$uid = $session['uid'];  		
    		$order = D('Order');
    		$discount = 0;
    		$order_id = $order->saveOrder($uid,$goods_id,$quantity,$receivename,$mobile,$address,$discount,$coupon);
    		if($order_id){
    			$info = M('Order')->field(true)->find($order_id);    
    			$data['status'] = 1;
             	$data['content'] = '购买成功';
            	$data['url'] = 'index.php?s=order/payfor/id/'.$info['order_no'];
    		}else{
    			$data['status'] = 0;
             	$data['content'] = '购买失败';
    		}
    		$this->ajaxReturn($data);
    	}
    	else{	    
    		$cid=$_GET['cid'];
    		$tid=$_GET['tid'];
    		switch ($cid){
    			case 1: $color='科技白';break;
    			case 2: $color='薄荷蓝';break;
    			case 3: $color='迷彩绿';break;
    			case 4: $color='童趣绿';break;
    			case 5: $color='航天蓝';break;
    		}
    		switch ($tid){
    			case 6: $type='S';break;
    			case 7: $type='M';break;
    			case 8: $type='L';break;
    		}
    		
    		$Goods = D('Goods');
    		$details = $Goods->getInfoByTypeAndColor($type,$color);
    		$details['goods_price']=$details['goods_price'];
    		
    		$this->assign('details', $details);
    		$this->assign('WEB_SITE_TITLE','订单确认 - 航天悟净');
    		$this->display();
    	}
    }
    
    public function checkCoupon($coupon = ''){  	  	
    	if($coupon == ''){
    		$data['status'] = 1;
    	}else{
    		$couponModel = D("Coupon");
    		$list = $couponModel->get_List_By_Number($coupon);
	    	if(!is_array($list)){
	    		$data['status'] = 0;
	          	$data['info'] = '该优惠券不存在，请核对后重新输入！';
	    	}elseif($list['status'] == 1){
	    		$data['status'] = 0;
	          	$data['info'] = '该优惠券已被使用！';
	    	}elseif(strtotime($list['start_time']) > time()){
	    		$data['status'] = 0;
	          	$data['info'] = '该优惠券还没有到使用时间！';
	    	}elseif(strtotime($list['end_time']) < time()){
	    		$data['status'] = 0;
	          	$data['info'] = '该优惠券已过期！';
	    	}else{
	    		$type_id = $list['type_id'];
	    		$couponTypeModel = D("CouponType");
	    		$info = $couponTypeModel->get_info_By_Id($type_id);
	    		
	    		$data['status'] = 1;
	    		if($info['id']==1){
	          		$data['info'] = "该优惠券为{$info['name']},优惠值为{$info['value']}元";
	    		}elseif($info['id']==2){
	    			$data['info'] = "该优惠券为{$info['name']},折扣为{$info['value']}折";
	    		}
	    	}
    	}
    	$this->ajaxReturn($data);
    }
    
    public function payfor($id = ''){
    	$user=session('user_auth');
		$uid=$user['uid'];
		define('UID',$uid);
    	$info = M('OrderGoods')->field(true)->find($id);
    	if (IS_POST) {
			$paytype = I('post.paytype');
			
            $pay = new \Think\Pay($paytype, C('payment.' . $paytype));
            $order_no = $pay->createOrderNo();
            $vo = new \Think\Pay\PayVo();
            $vo->setBody($info['goods_name'])
                    ->setFee($info['total_price'])
                    ->setOrderNo($order_no)
                    ->setTitle("北京航天通信")
                    ->setCallback("Home/Order/pay")
                    ->setUrl(U("Home/User/order"))
                    ->setParam(array("paytype"=>$paytype,"order_id"=>$id,"uid"=>UID, "pay_no"=> $order_no));
            echo $pay->buildRequestForm($vo);
		} else {     
			$order = M('Order')->where(array('order_no' => $id))->find();
	    	$this->assign('order', $order);
	    	$this->assign('info', $info);
	    	$this->assign('WEB_SITE_TITLE', '订单支付 - 航天悟净');
	    	$this->display();
		}
    }
    
    public function pay($money,$param) {
        if (session("pay_verify") == true) {
            session("pay_verify", null);
            $data['status']=1;
            $data['pay_no']=$param['pay_no']; 
            M("Order")->where(array('order_no' => $param['order_id']))->save($data);
        } else {
            E("Access Denied");
        }
    }
}