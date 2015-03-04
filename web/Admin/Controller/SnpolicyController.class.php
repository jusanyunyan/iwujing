<?php
namespace Admin\Controller;
class SnpolicyController extends AdminController {
    public function index(){
   		if(is_login()){
	        Cookie('__forward__',$_SERVER['REQUEST_URI']);		        
	        $map = array('is_delete'=>0);
	       	$snPolicyList = $this->lists('snPolicy',$map,'sid asc');
	       	foreach ($snPolicyList as &$v){	        	
	        	switch ($v['status']){
	        		case 0: $v['statusName']='不可用';break;
	        		case 1: $v['statusName']='可用';break;
	        	}
	        	$v['createtime'] = date('Y-m-d H:i:s', $v['createtime']);
	        	$v['updatetime'] = date('Y-m-d H:i:s', $v['updatetime']);

	        	$giftPolicyModel = D('Home/GiftPolicy');
	        	$policyInfo = $giftPolicyModel->getListById($v['policy_id']);
	        	$v['policy_name'] = $policyInfo['policy_name'];
	        }    	
	       	$this->assign('snPolicyList',$snPolicyList);
	     	$this->meta_title = 'SN码策略管理';
	    	$this->display();            
        } else {
            $this->redirect('Public/login');
        }
    }
    
	public function add(){
    	if(IS_POST){
            $snPolicy = M('snPolicy');
            $data = $snPolicy->create();
            $data['createtime']=strtotime($data['createtime']);
            $data['updatetime']=strtotime($data['updatetime']); 
            if($data){
            	$map = array('sn' => $data['sn']);
            	$sid = M('snSeq')->where($map)->setField('status',0);

            	$GiftPolicy = D('Home/GiftPolicy');
				$policyList = $GiftPolicy->getListById($data['policy_id']);
				
            	$GiftSeq = D('Home/GiftSeq');
				$seqList = $GiftSeq->get_List_by_id($data['policy_id']);
				foreach ($seqList as &$v){
					$Gift = D('Home/Gift');
					$giftList = $Gift->get_gift_by_id($v['gift_id']);
					$gift_name = $giftList['gift_name'];
					$icon = $giftList['icon'];
					$sequence = $v['sequence'];
					$total_time = $policyList['total_time'];
					$cycle_time = $v['cycle_time'];
					$preset_time = $v['preset_time'];
					$remind_time = $v['remind_time'];
					
					$snSeq = D('Home/SnSeq');	
					$status = 1;	
					$sn = $data['sn'];
					$sid = $snSeq->saveInfo($sn,$sequence,$gift_name,$icon,$total_time,$cycle_time,$preset_time,$remind_time,$status);
				}	
                if($snPolicy->add($data)){
                    S('DB_SNPOLICY_DATA',null);
                    $this->success('新增成功', U('index'));
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($policy->getError());
            }
        } else {
        	$giftPolicy = D("Home/GiftPolicy");
            $policyList = $giftPolicy->get_all($map,array('policy_id','policy_name'));
            
        	$info['createtime']=date('Y-m-d H:i:s', time());
        	$info['updatetime']=date('Y-m-d H:i:s', time());      	
        	
        	$this->assign('policyList', $policyList);
        	$this->assign('info', $info);
            $this->meta_title = '新增SN码策略';
            $this->display('edit');
        }
    }
    
	public function del($id = 0){
		$id = array_unique((array)I('id',0));
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('sid' => array('in', $id) );
        if(M('snPolicy')->where($map)->setField('is_delete',1)){
            S('DB_GIFTPOLICY_DATA',null);
           	action_log('update_snpolicy','snpolicy',$data['sid'],UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
    
	public function edit($id = 0){
        if(IS_POST){
           	$snPolicy = M('snPolicy');
            $data = $snPolicy->create();
            $data['createtime']=strtotime($data['createtime']);
            $data['updatetime']=strtotime($data['updatetime']); 
            if($data){
            	$snPolicyModel = D('Home/snPolicy');
            	$policyInfo = $snPolicyModel->get_Info_By_Id($data['sid']);
            	if($policyInfo['policy_id'] != $data['policy_id']){
            		$map = array('sn' => $data['sn']);
	            	$sid = M('snSeq')->where($map)->setField('status',0);
	
	            	$GiftPolicy = D('Home/GiftPolicy');
					$policyList = $GiftPolicy->getListById($data['policy_id']);
					
	            	$GiftSeq = D('Home/GiftSeq');
					$seqList = $GiftSeq->get_List_by_id($data['policy_id']);
					foreach ($seqList as &$v){
						$Gift = D('Home/Gift');
						$giftList = $Gift->get_gift_by_id($v['gift_id']);
						$gift_name = $giftList['gift_name'];
						$icon = $giftList['icon'];
						$sequence = $v['sequence'];
						$total_time = $policyList['total_time'];
						$cycle_time = $v['cycle_time'];
						$preset_time = $v['preset_time'];
						$remind_time = $v['remind_time'];
						
						$snSeq = D('Home/SnSeq');	
						$status = 1;	
						$sn = $data['sn'];
						$sid = $snSeq->saveInfo($sn,$sequence,$gift_name,$icon,$total_time,$cycle_time,$preset_time,$remind_time,$status);
					}	
            	}
                if($snPolicy->save($data)){                	
                    S('DB_SNPOLICY_DATA',null);
                    action_log('update_snpolicy','snpolicy',$data['sid'],UID);
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
            $info = M('snPolicy')->field(true)->find($id);
            
            $giftPolicy = D("Home/GiftPolicy");
            $policyList = $giftPolicy->get_all($map,array('policy_id','policy_name'));
                   
        	$info['createtime'] = date('Y-m-d H:i:s', $info['createtime']);
        	$info['updatetime'] = date('Y-m-d H:i:s', time());
        	
            if(false === $info){
                $this->error('获取信息错误');
            }

            $this->assign('policyList', $policyList);
            $this->assign('info', $info);
            $this->meta_title = '编辑SN码策略';
            $this->display();
        }
    }
    
	public function disable($id = 0){
		$id = array_unique((array)I('id',0));
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

		$map = array('sid' => array('in', $id) );
        if(M('snPolicy')->where($map)->setField('status',0)){
            S('DB_SNPOLICY_DATA',null);
           	action_log('update_snpolicy','snpolicy',$data['sid'],UID);
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

		$map = array('sid' => array('in', $id) );
        if(M('snPolicy')->where($map)->setField('status',1)){
            S('DB_SNPOLICY_DATA',null);
           	action_log('update_snpolicy','snpolicy',$data['sid'],UID);
            $this->success('修改成功');
        } else {
            $this->error('修改失败！');
        }
    }
}