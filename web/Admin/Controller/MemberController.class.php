<?php
namespace Admin\Controller;
use User\Api\UserApi;

class MemberController extends AdminController {
    public function index(){
    	if(is_login()){
    		Cookie('__forward__',$_SERVER['REQUEST_URI']);	
    		$map = array();
	        if(isset($_GET['username'])){
	            $map['username|mobile']    =   array('like', '%'.(string)I('username').'%');
	        }		
	    	$userList = $this->lists("User",$map);
	    	foreach ($userList as &$v){
	    		$v['createtime'] = date('Y-m-d H:i:s', $v['createtime']);
	    		if($v['updatetime']){
	        		$v['updatetime'] = date('Y-m-d H:i:s', $v['updatetime']);
	        	}else{
	        		$v['updatetime'] = '';
	        	}
	        	if($v['status'] == 1){
	        		$v['status']='已验证';
	        	}else{
	        		$v['status']='未验证';
	        	}
	    	}	    	
	    	$this->meta_title = '会员管理';
	  		$this->assign('userList',$userList);
	    	$this->display(); 
    	} else {
            $this->redirect('Public/login');
        }
    }	
    
	public function add(){
        if(IS_POST){
            $User = D('User');
            $data = $User->create();
            $data['createtime']=strtotime($data['createtime']);
            $data['updatetime']=strtotime($data['updatetime']); 
            if($data){
                if($User->add($data)){
                    S('DB_USER_DATA',null);
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
            $this->meta_title = '新增会员信息';
            $this->assign('info',$info);
            $this->display('edit');
        }
    }
    
	public function edit($uid = 0){
        if(IS_POST){
            $User = D('User');
            $data = $User->create();          
            $data['createtime']=strtotime($data['createtime']);
            $data['updatetime']=strtotime($data['updatetime']);          
            if($data){            	
                if($User->save($data)){                	
                    S('DB_USER_DATA',null);
                    //记录行为
                    action_log('update_user','user',$data['uid'],UID);
                    $this->success('更新成功', Cookie('__forward__'));
                } else {
                    $this->error('更新失败');
                }
            } else {
                $this->error($User->getError());
            }
        } else {
    		$info = array();
            /* 获取数据 */
            $info = M('User')->field(true)->find($uid); 
            $info['createtime']=date('Y-m-d H:i:s', time());
        	$info['updatetime']=date('Y-m-d H:i:s', time());

            $this->assign('info', $info); 
            $this->meta_title = '编辑会员信息';
            $this->display();
        }
    }
    
	public function del(){
		$uid = array_unique((array)I('uid',0));

        if ( empty($uid) ) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('uid' => array('in', $uid) );
        if(M('User')->where($map)->delete()){
            S('DB_USER_DATA',null);
            //记录行为
            action_log('update_user','user',$uid,UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
    
 	public function updateNickname(){
        $nickname = M('Admin')->getFieldByUid(UID, 'username');
       
        $this->assign('nickname', $nickname);
        $this->meta_title = '修改昵称';
        $this->display();
    }
    
 	public function submitNickname(){
        //获取参数
        $nickname = I('post.nickname');
        $password = I('post.password');
        empty($nickname) && $this->error('请输入昵称');
        empty($password) && $this->error('请输入密码');
		
        //密码验证
        $password = md5($password);
       	$list = M('Admin')->field(true)->find(UID,$password);
		if(is_array($list)){
	        $Admin = D('Admin');
	        $data = $Admin->create(array('username'=>$nickname));
	        if(!$data){
	            $this->error($Admin->getError());
	        }
	
	        $res = $Admin->where(array('uid'=>UID))->save($data);
	        if($res){
	            $user               =   session('user_auth');
	            $user['username']   =   $data['nickname'];
	            session('user_auth', $user);
	            session('user_auth_sign', data_auth_sign($user));
	            $this->success('修改昵称成功！');
	        }else{
	            $this->error('修改昵称失败！');
	        }
		}else{
			 $this->error('密码错误！');
		}
    }
    
 	public function updatePassword(){
        $this->meta_title = '修改密码';
        $this->display();
    }
    
	public function submitPassword(){
        //获取参数
        $password   =   I('post.old');
        empty($password) && $this->error('请输入原密码');
        $newpassword = I('post.password');
        empty($newpassword) && $this->error('请输入新密码');
        $repassword = I('post.repassword');
        empty($repassword) && $this->error('请输入确认密码');

        if($newpassword !== $repassword){
            $this->error('您输入的新密码与确认密码不一致');
        }

		//密码验证
        $password = md5($password);
       	$list = M('Admin')->field(true)->find(UID,$password);
		if(is_array($list)){
	        $Admin = D('Admin');
	        $data = $Admin->create(array('password'=>$newpassword));
	        if(!$data){
	            $this->error($Admin->getError());
	        }
	
	        $res = $Admin->where(array('uid'=>UID))->save($data);
	        if($res){
	            $user               =   session('user_auth');
	            $user['username']   =   $data['nickname'];
	            session('user_auth', $user);
	            session('user_auth_sign', data_auth_sign($user));
	            cookie('username',null);
	            $this->success('修改密码成功！');
	        }else{
	            $this->error('修改密码失败！');
	        }
		}else{
			 $this->error('密码错误！');
		}
    }
}
