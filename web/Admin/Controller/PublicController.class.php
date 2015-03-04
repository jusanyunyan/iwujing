<?php
namespace Admin\Controller;
//use User\Api\UserApi;

class PublicController extends \Think\Controller {	
    public function login($username = null, $password = null, $verify = null){		
		if(IS_POST){		
            if(!check_verify($verify)){
                $this->error('验证码输入错误！');
            }
            $admin = D('Admin');
            $uid = $admin->adminlogin($username, $password);
           
            if(0 < $uid){          	
            	$auth = array(
					'is_login'  =>  1,
					'uid'             => $uid,
					'username'        => $username,
				);
				session('user_auth', $auth);
				session('user_auth_sign', data_auth_sign($auth));
				cookie('username',$username,array('expire'=>500*24*60*60,'prefix'=>''));
				
				$data['status']=1;
				$data['url']='admin.php?s=order/index';
				$this->ajaxReturn($data);				

            } else { 
                $data['status']=-2;
				$data['info']='密码错误';
				$this->ajaxReturn($data);
            }
        } else {  
            if(is_login()){
                $this->redirect('Index/index');
            }else{            
                $this->display();
            }
        }
    }

    /* 退出登录 */
    public function logout(){
        if(is_login()){           
            session('user_auth', null);
            $this->success('退出成功！', U('login'));
        } else {
            $this->redirect('login');
        }
    }

    public function verify(){
        $verify = new \Think\Verify();
        ob_end_clean();	        
        $verify->entry(1);
    }

}
