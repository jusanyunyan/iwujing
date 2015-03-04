<?php
namespace Admin\Controller;

class CoupontypeController extends AdminController {
    public function index(){
        if(is_login()){	
        	Cookie('__forward__',$_SERVER['REQUEST_URI']);	
        	
	        $couponType = D("CouponType");
	        $couponTypeList = $couponType->get_all();
	          	
	        $this->assign('couponTypeList',$couponTypeList);
	     	$this->meta_title = '优惠券字典表';
	    	$this->display();            
        } else {
            $this->redirect('Public/login');
        }
    }
    
	public function add(){
        if(IS_POST){
            $CouponType = D('CouponType');
            $data = $CouponType->create();
            if($data){
                if($CouponType->add()){
                    S('DB_COUPONTYPE_DATA',null);
                    $this->success('新增成功', U('index'));
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($CouponType->getError());
            }
        } else {
            $this->meta_title = '新增配置';
            $this->assign('info',null);
            $this->display('edit');
        }
    }
    
	public function edit($id = 0){
        if(IS_POST){
            $CouponType = D('CouponType');
            $data = $CouponType->create();
            if($data){
                if($CouponType->save()){
                    S('DB_COUPONTYPE_DATA',null);
                    //记录行为
                    action_log('update_coupontype','coupontype',$data['id'],UID);
                    $this->success('更新成功', U('index'));
                } else {
                    $this->error('更新失败');
                }
            } else {
                $this->error($CouponType->getError());
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = M('CouponType')->field(true)->find($id);

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
        if(M('CouponType')->where($map)->delete()){
            S('DB_COUPONTYPE_DATA',null);
            //记录行为
            action_log('update_coupontype','coupontype',$id,UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
	
}
