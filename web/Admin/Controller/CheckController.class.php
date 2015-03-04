<?php
namespace Admin\Controller;
class CheckController extends AdminController {
    public function index(){
         if(is_login()){
         	Cookie('__forward__',$_SERVER['REQUEST_URI']);	
	        	
	        $checkList = $this->lists('OrderCheck', '', 'id asc');  
	        foreach ($checkList as &$v)	{
	        	$v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
	        	$user = M('Admin')->field(true)->find($v['user_id']); 
	        	$v['user'] = $user['username'];
	        }
	      
        	$this->assign('checkList',$checkList);
            $this->meta_title = '订单审核列表';
            $this->display();
            
        } else {
            $this->redirect('Public/login');
        }
    }
    
	public function del(){
        $id = array_unique((array)I('id',0));

        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('id' => array('in', $id) );
        if(M('OrderCheck')->where($map)->delete()){
            S('DB_ORDERCHECK_DATA',null);
            //记录行为
            action_log('update_orderCheck','orderCheck',$id,UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
}
