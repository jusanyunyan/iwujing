<?php
namespace Admin\Controller;
class OrderController extends AdminController {
    public function index(){
        if(is_login()){
        	Cookie('__forward__',$_SERVER['REQUEST_URI']);	
        	$session=session('user_auth');  	
        	$uid = $session['uid'];
        	
	        $condition = array();  
	        $count = 5;	
        	$model = M("Order");
	        if(!isset($_GET['p'])){
				$_GET['p']=1;
			}
        	if($_GET['status'] != ''){
				$condition['status'] = $_GET['status'];				
	        }
	        $stype=I('get.stype');
        	$keywords=I('get.keywords');
			$create_time=I('get.create_time');
			
	        if(isset($stype)&&!empty($stype)&&isset($keywords)&&!empty($keywords)){
				switch($stype){
					case "1": //手机号码                  
	                    $condition['receiver_mobile']=$keywords;
	                    break;
	                case "2"://订单ID
						$condition['order_no']=$keywords;
	                    break;
	                default:
	                    break;
	            }
			}			
	        if(isset($create_time)&&!empty($create_time)){//创建时间
				$dayStr=strtotime($create_time);
				$day=date("d",$dayStr);
				$month=date("m",$dayStr);
				$year=date("Y",$dayStr);			
				$dayBegin=mktime(0,0,0,$month,$day,$year); //一天开始
				$dayOver=mktime(23,59,59,$month,$day,$year); //一天结束	
				$condition['createtime']=array('between',"{$dayBegin},{$dayOver}");
			}
        	$orderList = $model->where($condition)->order('order_id desc')->page($_GET['p'].",{$count}")->select();
                	
        	foreach ($orderList as $v){
        		$order_no = $v['order_no'];
        		$orderGoods = D('Home/OrderGoods');
        		$orderGoodsList = $orderGoods->get_List_By_Id($order_no);
        		$v['order_goods'] = $orderGoodsList;
        		$v['createtime'] = date('Y-m-d H:i:s', $v['createtime']);
        		if($v['updatetime']){
        			$v['updatetime'] = date('Y-m-d H:i:s', $v['updatetime']);
        		}else{
        			$v['updatetime'] = '';
        		}
        		switch ($v['status']){
        			case 0: $v['status']='未付款';break;
        			case 1: $v['status']='已付款未发货';break;
        			case 2: $v['status']='已发货';break;
        			case 3: $v['status']='已收货';break;
        			case 4: $v['status']='已退货';break;
        			case 5: $v['status']='已退款';break;
        			case 6: $v['status']='用户取消订单';break;
        			case 7: $v['status']='过时取消订单';break;      		
        		}
        		$list[]=$v;
        	}
        	$order = D('Home/Order');
        	$count = $order->get_count($condition);
        	$_page = tp_page(5,$count,5);
        	
        	$this->assign('_page',$_page);        	
        	$this->assign('orderList',$list);
            $this->meta_title = '订单管理';
            $this->display();
        } else {
            $this->redirect('Public/login');
        }
    }
    
	public function edit($id = 0){
        if(IS_POST){
            $Order = D('Order');
            $data = $Order->create();
            $data['createtime']=strtotime($data['createtime']);
            $data['updatetime']=strtotime($data['updatetime']);   
            if($data){
                if($Order->save($data)){        
					$orderCheck = D('OrderCheck');
					$check = $orderCheck->create();					
					$check['user_id']=UID;
					$check['create_time']=$data['updatetime'];									
					$orderCheck->add($check);
                	
                    S('DB_ORDER_DATA',null);
                    //记录行为
                    action_log('update_order','order',$data['order_id'],UID);
                    $this->success('更新成功', Cookie('__forward__'));
                } else {
                    $this->error('更新失败');
                }
            } else {
                $this->error($Order->getError());
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = M('Order')->field(true)->find($id);    
			$info['createtime'] = date('Y-m-d H:i:s', $info['createtime']);
    		$info['updatetime'] = date('Y-m-d H:i:s', time());
			
            if(false === $info){
                $this->error('获取配置信息错误');
            }
            $this->assign('info', $info);
            $this->meta_title = '编辑订单';
            $this->display();
        }
    }
    
    public function detail($id=0){
    	$info = M('OrderGoods')->field(true)->find($id);    
       	if(false === $info){
       		$this->error('获取配置信息错误');
       	}
       	//print_R($info);
      	$this->assign('info', $info);  	
    	$this->meta_title = '订单管理';
      	$this->display();
    }	
}
