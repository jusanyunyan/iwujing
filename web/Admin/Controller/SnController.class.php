<?php
namespace Admin\Controller;
class SnController extends AdminController {
    public function index(){
        if(is_login()){
	        Cookie('__forward__',$_SERVER['REQUEST_URI']);		        
	        $map = array();
	        if(isset($_GET['code'])){
	        	$fcode = substr($_GET['code'], 0, 8);
	        	$vcode = substr($_GET['code'], 8);
	            $map['fcode']    =   array('like', '%'.$fcode.'%');
	            $map['vcode']    =   array('like', '%'.$vcode.'%');
	        }		
	        $snList = $this->lists('Sn',$map,'id asc');
	        
	    	foreach ($snList as &$v){	        	
	        	switch ($v['type_id']){
	        		case 1: $v['type']='车载';break;
	        		case 2: $v['type']='壁挂单机';break;
	        		case 3: $v['type']='壁挂远控';break;
	        		case 4: $v['type']='悟净机';break;
	        	}   
	        	switch ($v['status']){
	        		case 0: $v['status']='未领取';break;
	        		case 1: $v['status']='已领取';break;
	        	}
	        	$v['createtime'] = date('Y-m-d H:i:s', $v['createtime']);
	        	$v['updatetime'] = date('Y-m-d H:i:s', $v['updatetime']);
	        	$v['value']=$v['fcode'].$v['vcode'];   	
	        }    
	        $this->assign('snList',$snList);
	     	$this->meta_title = 'SN码管理';
	    	$this->display();            
        } else {
            $this->redirect('Public/login');
        }
    }
    
	public function edit($id = 0){
        if(IS_POST){
            $sn = D('Sn');
            $data = $sn->create();
            $data['createtime']=strtotime($data['createtime']);
            $data['updatetime']=strtotime($data['updatetime']);  
            if($data){
                if($sn->save($data)){                	
                    S('DB_SN_DATA',null);
                    //记录行为
                    action_log('update_sn','sn',$data['id'],UID);
                    $this->success('更新成功', Cookie('__forward__'));
                } else {
                    $this->error('更新失败');
                }
            } else {
                $this->error($sn->getError());
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = M('Sn')->field(true)->find($id);
            
        	$info['createtime'] = date('Y-m-d H:i:s', $info['createtime']);
        	$info['updatetime'] = date('Y-m-d H:i:s', time());
        	$info['value']=$info['fcode'].$info['vcode'];
        	
            if(false === $info){
                $this->error('获取配置信息错误');
            }
            $this->assign('info', $info);
            $this->meta_title = '编辑配置';
            $this->display();
        }
    }
}
