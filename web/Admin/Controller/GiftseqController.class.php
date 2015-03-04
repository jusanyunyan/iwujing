<?php
namespace Admin\Controller;
class GiftseqController extends AdminController {
    public function index($id){
        if(is_login()){
	        Cookie('__forward__',$_SERVER['REQUEST_URI']);		        
	        $map = array("policy_id = $id");
	       	$giftseqList = $this->lists('giftSeq',$map,'policy_id asc');
			foreach ($giftseqList as &$v){
				$v['cycle_time'] = $v['cycle_time']/(60*60*24*30);
				$v['preset_time'] = $v['preset_time']/(60*60*24*30);
				$v['remind_time'] = $v['remind_time']/(60*60*24*30);
				
				$Gift = D('Home/Gift');
				$giftList = $Gift->get_gift_by_id($v['gift_id']);
				$v['gift_name']=$giftList['gift_name'];
				$v['icon']=$giftList['icon'];
				
				$policyList = M('giftPolicy')->field(true)->find($id);
				
				$goods = D('Home/goods');
				$goodsList = $goods->getInfoById($policyList['goods_id']);
				$v['goods_name']=$goodsList['goods_name'];
			}
			$this->assign('policy_id',$id);
	       	$this->assign('giftseqList',$giftseqList);
	     	$this->meta_title = '赠品顺序管理';
	    	$this->display();            
        } else {
            $this->redirect('Public/login');
        }
    }
    
    public function add($id = 0){
    	if(IS_POST){
            $giftSeq = M('giftSeq');
            $data = $giftSeq->create();
            $data['cycle_time']=$data['cycle_time']*60*60*30*24;
            $data['preset_time']=$data['preset_time']*60*60*30*24;
            $data['remind_time']=$data['remind_time']*60*60*30*24;
            if($data){
                if($giftSeq->add($data)){
                    S('DB_GIFTSEQ_DATA',null);
                    $this->success('新增成功', Cookie('__forward__'));
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($giftSeq->getError());
            }
        } else {
        	$giftModel = D('Home/gift');
            $giftList = $giftModel->get_all($map,array('gift_id','gift_name'));
           
            $this->assign('policy_id', $id);
        	$this->assign('giftList', $giftList);
            $this->meta_title = '新增赠品策略';
            $this->assign('info',$info);
            $this->display('edit');
        }
    }
    
	public function edit($id = 0){
        if(IS_POST){
           	$giftSeq = M('giftSeq');
            $data = $giftSeq->create();
            $data['cycle_time']=$data['cycle_time']*60*60*30*24;
            $data['preset_time']=$data['preset_time']*60*60*30*24;
            $data['remind_time']=$data['remind_time']*60*60*30*24;
            if($data){
                if($giftSeq->save($data)){                	
                    S('DB_GIFTSEQ_DATA',null);
                    action_log('update_giftseq','giftseq',$data['seq_id'],UID);
                    $this->success('更新成功', Cookie('__forward__'));
                } else {
                    $this->error('更新失败');
                }
            } else {
                $this->error($giftSeq->getError());
            }
        } else {
        	$map = array();
            $info = M('giftSeq')->field(true)->find($id);
            $info['cycle_time'] = $info['cycle_time']/(60*60*30*24);
            $info['preset_time'] = $info['preset_time']/(60*60*30*24);
            $info['remind_time'] = $info['remind_time']/(60*60*30*24);
            
            $giftModel = D('Home/gift');
            $giftList = $giftModel->get_all($map,array('gift_id','gift_name'));
        	
            if(false === $info){
                $this->error('获取赠品信息错误');
            }

            $this->assign('giftList', $giftList);
            $this->assign('info', $info);
            $this->meta_title = '编辑赠品顺序';
            $this->display();
        }
    }
    
	public function del($id = 0){
		$id = array_unique((array)I('id',0));
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('seq_id' => array('in', $id) );
        if(M('giftSeq')->where($map)->delete()){
            S('DB_GIFTSEQ_DATA',null);
           	action_log('update_giftseq','giftseq',$data['policy_id'],UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
    
}
