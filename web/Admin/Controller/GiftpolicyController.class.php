<?php
namespace Admin\Controller;
class GiftpolicyController extends AdminController {
    public function index(){
        if(is_login()){
	        Cookie('__forward__',$_SERVER['REQUEST_URI']);		        
	        $map = array('is_delete' => 0);	
	       	$policyList = $this->lists('giftPolicy',$map,'policy_id asc');
	       	foreach ($policyList as &$v){	        	
	        	switch ($v['status']){
	        		case 0: $v['statusName']='不可用';break;
	        		case 1: $v['statusName']='可用';break;
	        	}
	       		switch ($v['pretermit']){
	        		case 0: $v['defaultName']='否';break;
	        		case 1: $v['defaultName']='是';break;
	        	}
	        	$v['total_time'] = $v['total_time']/(60*60*30*24);
	        	$v['createtime'] = date('Y-m-d H:i:s', $v['createtime']);
	        	$v['updatetime'] = date('Y-m-d H:i:s', $v['updatetime']);	
	        	
	        	$goodsModel = D("Home/Goods");
				$goodsInfo = $goodsModel->getInfoById($v['goods_id']);	
	        	$v['goods_name']=$goodsInfo['goods_name'];
	        }    	
	       	$this->assign('policyList',$policyList);
	     	$this->meta_title = '赠品策略管理';
	    	$this->display();            
        } else {
            $this->redirect('Public/login');
        }
    }
    
    public function add(){
    	if(IS_POST){
            $policy = M('giftPolicy');
            $data = $policy->create();
            $data['total_time']=$data['total_time']*60*60*30*24;
            $data['createtime']=strtotime($data['createtime']);
            $data['updatetime']=strtotime($data['updatetime']); 
            if($data){
                if($policy->add($data)){
                    S('DB_GIFTPOLICY_DATA',null);
                    $this->success('新增成功', U('index'));
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($policy->getError());
            }
        } else {
        	$goodsModel = D("Home/Goods");
            $goodsList = $goodsModel->get_all(array(),array('goods_id','goods_name'));
            
        	$info['createtime']=date('Y-m-d H:i:s', time());
        	$info['updatetime']=date('Y-m-d H:i:s', time());      	
        	
        	$this->assign('goodsList', $goodsList);
            $this->meta_title = '新增赠品策略';
            $this->assign('info',$info);
            $this->display('edit');
        }
    }
    
	public function edit($id = 0){
        if(IS_POST){
           	$policy = M('giftPolicy');
            $data = $policy->create();
            $data['total_time']=$data['total_time']*60*60*30*24;
            $data['createtime']=strtotime($data['createtime']);
            $data['updatetime']=strtotime($data['updatetime']);  
            if($data){
                if($policy->save($data)){                	
                    S('DB_GIFTPOLICY_DATA',null);
                    action_log('update_giftpolicy','giftpolicy',$data['id'],UID);
                    $this->success('更新成功', Cookie('__forward__'));
                } else {
                    $this->error('更新失败');
                }
            } else {
                $this->error($policy->getError());
            }
        } else {
        	$map  = array();
            $info = array();
            $info = M('giftPolicy')->field(true)->find($id);
            
            $goodsModel = D("Home/Goods");
            $goodsList = $goodsModel->get_all($map,array('goods_id','goods_name'));
                   
            $info['total_time'] = $info['total_time']/(60*60*30*24);
        	$info['createtime'] = date('Y-m-d H:i:s', $info['createtime']);
        	$info['updatetime'] = date('Y-m-d H:i:s', time());
        	
            if(false === $info){
                $this->error('获取赠品信息错误');
            }

            $this->assign('goodsList', $goodsList);
            $this->assign('info', $info);
            $this->meta_title = '编辑赠品策略';
            $this->display();
        }
    }
    
	public function del($id = 0){
		$id = array_unique((array)I('id',0));
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('policy_id' => array('in', $id) );
        if(M('giftPolicy')->where($map)->setField('is_delete',1)){
            S('DB_GIFTPOLICY_DATA',null);
           	action_log('update_giftpolicy','giftpolicy',$data['policy_id'],UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
    
	public function disable($id = 0){
		$id = array_unique((array)I('id',0));
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

		$map = array('policy_id' => array('in', $id) );
        if(M('giftPolicy')->where($map)->setField('status',0)){
            S('DB_GIFTPOLICY_DATA',null);
           	action_log('update_giftpolicy','giftpolicy',$data['policy_id'],UID);
            $this->success('修改成功');
        } else {
            $this->error('修改失败！');
        }
    }
    
	public function able($id = 0){
		$id = array_unique((array)I('id',0));
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

		$map = array('policy_id' => array('in', $id) );
        if(M('giftPolicy')->where($map)->setField('status',1)){
            S('DB_GIFTPOLICY_DATA',null);
           	action_log('update_giftpolicy','giftpolicy',$data['policy_id'],UID);
            $this->success('修改成功');
        } else {
            $this->error('修改失败！');
        }
    }
}
