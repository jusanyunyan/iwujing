<?php
namespace Admin\Controller;
class GiftController extends AdminController {
    public function index(){
        if(is_login()){
	        Cookie('__forward__',$_SERVER['REQUEST_URI']);		        
	        $map = array('is_delete' => 0);	      
	       	$giftList = $this->lists('Gift',$map,'gift_id asc');

	       	foreach ($giftList as &$v){	        	
	        	switch ($v['status']){
	        		case 0: $v['status']='不可用';break;
	        		case 1: $v['status']='可用';break;
	        	}
	        	$v['createtime'] = date('Y-m-d H:i:s', $v['createtime']);
	        	$v['updatetime'] = date('Y-m-d H:i:s', $v['updatetime']);	
	        }    	        
	       	$this->assign('giftList',$giftList);
	     	$this->meta_title = '赠品管理';
	    	$this->display();            
        } else {
            $this->redirect('Public/login');
        }
    }
    
    public function add(){
    	if(IS_POST){
            $Gift = M('Gift');
            $data = $Gift->create();
            $data['createtime']=strtotime($data['createtime']);
            $data['updatetime']=strtotime($data['updatetime']); 
            if($data){
                if($Gift->add($data)){
                    S('DB_GIFT_DATA',null);
                    $this->success('新增成功', U('index'));
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($User->getError());
            }
        } else {
        	$info['createtime']=date('Y-m-d H:i:s', time());
        	$info['updatetime']=date('Y-m-d H:i:s', time());
            $this->meta_title = '新增赠品';
            $this->assign('info',$info);
            $this->display('edit');
        }
    }
    
	public function edit($id = 0){
        if(IS_POST){
           	$Gift = M('Gift');
            $data = $Gift->create();
            $data['createtime']=strtotime($data['createtime']);
            $data['updatetime']=strtotime($data['updatetime']);  
            if($data){
                if($Gift->save($data)){                	
                    S('DB_GIFT_DATA',null);
                    action_log('update_gift','gift',$data['id'],UID);
                    $this->success('更新成功', Cookie('__forward__'));
                } else {
                    $this->error('更新失败');
                }
            } else {
                $this->error($sn->getError());
            }
        } else {
            $info = array();
            $info = M('gift')->field(true)->find($id);
            
        	$info['createtime'] = date('Y-m-d H:i:s', $info['createtime']);
        	$info['updatetime'] = date('Y-m-d H:i:s', time());
        	
            if(false === $info){
                $this->error('获取赠品信息错误');
            }
            $this->assign('info', $info);
            $this->meta_title = '编辑赠品';
            $this->display();
        }
    }
    
	public function del(){
		$id = array_unique((array)I('id',0));

        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('gift_id' => array('in', $id) );
        if(M('Gift')->where($map)->setField('is_delete',1)){
            S('DB_GIFT_DATA',null);
            action_log('update_gift','gift',$id,UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
}
