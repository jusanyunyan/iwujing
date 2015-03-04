<?php
namespace Admin\Controller;
class CouponController extends AdminController {
    public function index(){
        if(is_login()){
	        Cookie('__forward__',$_SERVER['REQUEST_URI']);	
	        
	        $map = array();
	        if(isset($_GET['number'])){
	            $map['number']    =   array('like', '%'.(string)I('number').'%');
	        }		
	        $couponList = $this->lists('Coupon', $map, 'id asc');
	    	foreach ($couponList as &$v){
	        	$type_id = $v['type_id'];
	        	$coupon_type = M("CouponType");
	        	$type = $coupon_type->where(array('id' => $type_id))->find();
	        	$v['type']=$type['name'];
	        	$v['value']=$type['value'];
	        	switch ($v['status']){
	        		case 0: $v['status']='未发出';break;
	        		case 1: $v['status']='已发出';break;
	        		case 2: $v['status']='已使用';break;
	        	}        	
	        }    	
	        $this->assign('couponList',$couponList);
	     	$this->meta_title = '优惠券管理';
	    	$this->display();            
        } else {
            $this->redirect('Public/login');
        }
    }
    
	public function add(){
        if(IS_POST){
            $Coupon = D('Coupon');
            $data = $Coupon->create();
            if($data){
                if($Coupon->add()){
                    S('DB_COUPON_DATA',null);
                    $this->success('新增成功', U('index'));
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($Coupon->getError());
            }
        } else {
            $this->meta_title = '新增配置';
            $this->assign('info',null);
            $this->display('edit');
        }
    }
    
	public function edit($id = 0){
        if(IS_POST){
            $Coupon = D('Coupon');
            $data = $Coupon->create();
            if($data){
                if($Coupon->save()){                	
                    S('DB_COUPON_DATA',null);
                    //记录行为
                    action_log('update_coupon','coupon',$data['id'],UID);
                    $this->success('更新成功', Cookie('__forward__'));
                } else {
                    $this->error('更新失败');
                }
            } else {
                $this->error($Coupon->getError());
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = M('Coupon')->field(true)->find($id);
          	
          	$coupon_type = M("CouponType");
        	$type = $coupon_type->where(array('id' => $info['type_id']))->find();
        	$info['value']=$type['value'];

            if(false === $info){
                $this->error('获取配置信息错误');
            }
            $this->assign('info', $info);
            $this->meta_title = '编辑配置';
            $this->display();
        }
    }
    
	public function del(){
        $id = array_unique((array)I('id',0));

        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('id' => array('in', $id) );
        if(M('Coupon')->where($map)->delete()){
            S('DB_COUPON_DATA',null);
            //记录行为
            action_log('update_coupon','coupon',$id,UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
	
}
