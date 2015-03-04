<?php
namespace Home\Controller;
use Think\Controller;
use User\Api\UserApi;
/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class UserController extends Controller { 
    public function sendcode($mobile='', $verify=''){
    	if(IS_POST){ 
    		if(!check_verify($verify)){
                $data['status']=-1;
				$data['info']='验证码输入错误';
				$this->ajaxReturn($data);
            }
			$User = D('User');
			if($User->get_Verify_Mobile($mobile)){
				$data['status']=-2;
				$data['info']='此号码已被注册';
				$this->ajaxReturn($data);
			}

    	 	$UserVerify = D('UserVerify');   	 
			list($vid,$mid) = $UserVerify->send_verify($mobile);			
			if(0<$vid){                          
				$data['status']=1;
				$data['info']='发送成功';
				$this->ajaxReturn($data);
			}else{
				switch($vid) {
					case -22: $error = "系统刚刚发送过验证码"; break;
					case -23: $error = "此号码已经注册成功"; break;
					case -24: $error = "验证码生成失败，请稍后再试"; break;
					default:  $error = "未知错误，请稍后再试"; break;
				}
				$data['status']=-3;
				$data['info']=$error;
				$this->ajaxReturn($data);
			}	
    	}	    
    }
    public function signup($mobile='', $password='', $code=''){
    	if(IS_POST){
    		$UserVerify = D('UserVerify');
       		$rid = $UserVerify->verify_code($mobile,$code);
    		if(0<$rid){           
           		//修改登录时间和状态
              	$logic = D('User', 'Logic');
              	$data = array('mobile'=>$mobile);
              	$get_uid = D('User')->get_one($data);
                            
              	if(!empty($get_uid)){
	           		$uid = $get_uid['uid'];
	          	}else{
                  	$uid = $logic->add_model($mobile);
              	}
              	$uid = intval($uid);
              	if($uid){
	                $update_uid = $UserVerify->update_status(array('verify_content'=>$mobile),array('user_id'=>$uid));
              	}              	
              	
              	$user = D('User');
	           	$return = $user->updateUserInfo($mobile,$mobile,$password);   
	           	$info = $user->get_User_By_Mobile($mobile);             
	           	$m = $UserVerify->update_status(array('verify_content'=>$mobile),array('verify_status'=>1,'verify_time'=>NOW_TIME));
	            if($return && $m){
	            	$auth = array(
						'is_login'  =>  1,
						'uid'             => $info['uid'],
						'username'        => $mobile,
					);
					session('user_auth', $auth);
              		$Token = D("UserVerifyToken");
					$Token->clear_overdue();
              		  
	           		$data['status'] = 1;
	             	$data['info'] = '注册成功';
	            	$data['url'] = 'index.php?s=user/registersuccess';
	           	}else{
	              	$data['status'] = -2;
	              	$data['info'] = '注册失败';
	             	$data['url'] = 'index.php?s=user/registerfail';
	          	}
	         	$this->ajaxReturn($data);   
          	}else{
          		switch($rid) {
                 	case -1: 
                    	$data['status']  = -1;
                      	$data['content'] = "不存在需要验证的信息";
                	break; 
               		case -2: 
                     	$data['status']  = -1;
                       	$data['content'] = "此手机号码已经成功验证，无法重复验证";
                 	break;
               		case -3: 
                      	$data['status']  = -1;
                       	$data['content'] = "验证码已经过期，请重新发送";
                 	break;
                	case -5: 
                       	$data['status']  = -1;
                     	$data['content'] = '输入验证码有误，请检查后重新输入';
                 	break;
                	case -10: 
                      	$data['status']  = -1;
                      	$data['content'] = "参数错误，无法进行验证";
                    break;
                 	default: 
               			$data['status']  = -1;
                       	$data['content'] = '未知错误，请稍后再试';
                    break; 
           		}
           		$this->ajaxReturn($data);
       		}	
          	
    	}else{
    		$this->assign('WEB_SITE_TITLE','注册登录 - 航天悟净');
    		$this->display();
    	}    	
    }
    
    public function verifymobile($mobile=''){  
    	if(IS_POST){			
			$user=M("User")->where("mobile={$mobile}")->order("status desc")->find();
            if(is_array($user)){
                $uid=$user['uid'];
                $this->_setUid($uid);

                $UserVerify = new \Home\Model\ResetpwdModel("UserVerify");
                list($vid,$mid) = $UserVerify->send_verify($uid, $mobile);

                if(0<$vid){
                    $data['status']  = 1;
                    $data['id']  = $uid;
                    $data['vid']  = $vid;
                    $data['mobile']  = $mobile;
                    $this->ajaxReturn($data);
                }else{
                    $authUid = uch_authcode($uid,'ENCODE');
                    switch($vid) {
                        case -21: $error = "此号码已被其他用户占用"; break;
                        case -22: $error = "系统刚刚发送过验证码"; break;
                        case -24: $error = '验证码生成失败，请稍后再试'; break;
                        default: $error = '未知错误，请稍后再试'; break;
                    }
                    $data['status']  = -1;
                    $data['info']  = $error;
                    $this->ajaxReturn($data);
                }
            }else{
                $data['status']  = -1;
                $data['info']  = '该手机号未注册';
                $this->ajaxReturn($data);
            }
		}else{
			$this->assign('WEB_SITE_TITLE','验证手机 - 航天悟净');
    		$this->display();
		} 	   	
    }
    
	public function verifycode($code='',$mobile='',$id='',$vid=''){
		$uid = $this->_getUid();                
		$UserVerify = new \Home\Model\ResetpwdModel("UserVerify");
		if(IS_POST){     			                   
			$rid = $UserVerify->verify_code($uid,$vid,$mobile,$code);           
			if(0<$rid){
				$data['status'] = 1;
				$expired = NOW_TIME + 300;
				$this->_setResetExpire($expired);//设置重置密码过期时间
				$data['url']="index.php?s=user/resetpwd";
				$this->ajaxReturn($data);
			}else{
				switch($rid) {
					case -1: 
						$data['status']  = -1;
						$data['content'] = "非常抱歉，不存在需要验证的信息";
						break; 
					case -2: 
						$data['status']  = -1;
						$data['content'] = "非常抱歉，此手机号码已经成功验证，无法重复验证";
						break;
					case -3: 
						$data['status']  = -1;
						$data['content'] = "非常抱歉，验证码已经过期，请重新发送";
						break;
					case -5: 
						$data['status']  = -1;
						$data['content'] = '输入的验证码有误，请检查后重新输！';
						break;
					case -10: 
						$data['status']  = -1;
						$data['content'] = "非常抱歉，参数错误，无法进行验证";
						break;
					case -11: 
						$data['status']  = -1;
						$data['content'] = "非常抱歉，该用户尚未成功绑定手机";
						break;
					default: 
						$data['status']  = -1;
						$data['content'] = '未知错误，请稍后再试';
						break; 
				}
				$this->ajaxReturn($data);
			}			
		}
	}
	
	public function resetpwd($password='', $password2=''){	
		$uid = $this->_getUid();	
		if (IS_POST) {
			if($password != $password2){
				$data['status']  = -1;
				$data['content'] = '两次输入密码不一致，请重新检查';
				$this->ajaxReturn($data);
			}
			if(!$uid){	
				$data['status']  = -1;
				$data['content'] = '验证码已过期,请返回上一步重新获取';
				$this->ajaxReturn($data);			
			}

			if($this->_getResetExpire() < NOW_TIME) {
				$data['status']  = -1;
				$data['content'] = '验证码已过期,请返回上一步重新获取';
				$this->ajaxReturn($data);
			}

			$userModel = M("User");
			$userInfo = $userModel->where("uid=$uid")->select();

			if($userInfo){
				$data = array();
				$data['password'] = md5($password);
				$data['status'] = 1;
				$res = $userModel->where(array('uid'=>$uid))->save($data);
				if($res){
					$data['status']  = 1;
					$data['url']  = 'index.php?s=user/resetsuccess';
					$this->ajaxReturn($data);
				}else{
					$data['status']  = -1;
					$data['content'] = '重置密码与初始密码相同';
					$this->ajaxReturn($data);
				}
			}
		} else {
			$this->assign('uid',$uid);
			$this->assign('WEB_SITE_TITLE','重置密码 - 航天悟净');
			$this->display();
		}
	}
    
	public function register($mobile='',$verify=''){
    	if(IS_POST){  	    		
    		if(!check_verify($verify)){
                $this->error('验证码输入错误！');
            }
            
			$User = D('User');
			if($User->get_Verify_Mobile($mobile))
			{
				$this->error("此号码已被注册，请<a class='dialog-link' href='index.php?s=/user/login/'>登录</a>或<a class='dialog-link' href='index.php?s=/user/checkmobile/'>找回密码</a>！");
			}
			
    	 	$UserVerify = D('UserVerify');   	 
			list($vid,$mid) = $UserVerify->send_verify($mobile);
			
			if(0<$vid)
			{                          
				$data['status']=1;
				$data['info']='发送成功';
				$this->ajaxReturn($data);
			}
			else
			{
				switch($vid) {
					case -22: $error = "系统刚刚发送过验证码，请在下方输入验证码"; break; 
					case -23: $error = "此号码已经注册成功，请<a class='dialog-link' href='index.php?s=/user/login'>登录</a>或<a class='dialog-link' href='index.php?s=/user/checkmobile'>找回密码</a>！"; break;
					case -24: $error = '验证码生成失败，请稍后再试！'; break;					
					default:  $error = '未知错误，请稍后再试！'; break; 
				}
				$data['status']=-1;
				$data['info']=$error;
				$this->ajaxReturn($data);
			}
    	}else{
    		$this->assign('WEB_SITE_TITLE','注册 - 航天悟净');
    		$this->display();
    	}  	
    }
    
 	public function verify(){
        $verify = new \Think\Verify();
        ob_end_clean();	        
        $verify->entry(1);
    }
       
	public function checkmobilenew($mobile,$code){
		if(IS_POST){	
			$UserVerify = D('UserVerify');
       		$rid = $UserVerify->verify_code($mobile,$code);
       		
			if(0<$rid){           
           		//修改登录时间和状态
              	$logic = D('User', 'Logic');
              	$data = array('mobile'=>$mobile);
              	$get_uid = D('User')->get_one($data);
                            
              	if(!empty($get_uid)){
	           		$uid = $get_uid['uid'];
	          	}else{
                  	$uid = $logic->add_model($mobile);
              	}
              	$uid = intval($uid);
              	if($uid){
	                $update_uid = $UserVerify->update_status(array('verify_content'=>$mobile),array('user_id'=>$uid));
              	}
              	
              	$v_m = $UserVerify->get_one(array('verify_content'=>$mobile));
               	$data['status']  = 1;
             	$data['content'] = "<span class='spanSuccess'>恭喜您，手机号验证成功!</span>";
				$data['url'] = "index.php?s=user/addname/vid/{$v_m[id]}/mobile/{$mobile}";
              	$this->ajaxReturn($data);
          	} else{
          		switch($rid) {
                 	case -1: 
                    	$data['status']  = -1;
                      	$data['content'] = "<span class='spanError'>非常抱歉，不存在需要验证的信息！</span>";
                	break; 
               		case -2: 
                     	$data['status']  = -1;
                       	$data['content'] = "<span class='spanError'>非常抱歉，此手机号码已经成功验证，无法重复验证！</span>";
                 	break;
               		case -3: 
                      	$data['status']  = -1;
                       	$data['content'] = "<span class='spanError'>非常抱歉，验证码已经过期，请重新发送！</span>";
                 	break;
                	case -5: 
                       	$data['status']  = -2;
                     	$data['content'] = '您输入的验证码有误，请您检查后重新输入！';
                 	break;
                	case -10: 
                      	$data['status']  = -1;
                      	$data['content'] = "<span class='spanError'>非常抱歉，参数错误，无法进行验证！</span>";
                    break;
                 	default: 
               			$data['status']  = -2;
                       	$data['content'] = '未知错误，请稍后再试！';
                      	break; 
           		}
           		$this->ajaxReturn($data);
       		}	
		}			
	}
	
	public function addname($mobile='',$username='', $password = '',$vid=""){   	
    	$UserVerify = D('UserVerify');
    	if(IS_POST){    		
    		$rid = $this->re_verfity_code($vid,$mobile);
    		if($rid>0){
          		$tokenss = D('UserVerifyToken');
              	$tokenss->del_model($vid,0);
              	$UserVerify->clear_overdue();//清理过期数据                      
        	}elseif($rid == -1){
            	$data['status'] = 0;
              	$data['info'] = '无此验证消息';
              	$data['url'] = 'index.php?s=user/register';
              	$this->ajaxReturn($data);
          	}elseif($rid == -3){
            	$data['status'] = 0;
               	$data['info'] = '已过期无法验证';
             	$data['url'] = 'index.php?s=user/register';
             	$this->ajaxReturn($data);
          	}elseif($rid == -5){
            	$data['status'] = 0;
              	$data['info'] = '验证码错误,请重新注册';
              	$data['url'] = 'index.php?s=user/register';
           		$this->ajaxReturn($data);
          	}
          
    		/* 调用注册接口注册用户 */
			$username=preg_replace('/\s+/', '', $username);
			$password=preg_replace('/\s+/', '', $password);
			
			if(strlen($username)<=0)
			{
				$this->error('请您输入用户名');
			}			
			if(strlen($password)<6)
			{
				$this->error('您输入的密码长度过短,必须大于6个字符');
			}
			else if(strlen($password)>20)
			{
				$this->error('您输入的密码长度过长，请控制在20个字符以内');	
			}
			
			//根据手机号修改用户    
			$user = D('User');
           	$return = $user->updateUserInfo($mobile,$username,$password);   
           	$info = $user->get_User_By_Mobile($mobile);             
           	$m = $UserVerify->update_status(array('verify_content'=>$mobile),array('verify_status'=>1,'verify_time'=>NOW_TIME));
            if($return && $m){
            	$auth = array(
					'is_login'  =>  1,
					'uid'             => $info['uid'],
					'username'        => $username,
				);
				session('user_auth', $auth);
				
           		$data['status'] = 1;
             	$data['info'] = '注册成功';
            	$data['url'] = 'index.php?s=index/index';
           	}else{
              	$data['status'] = 0;
              	$data['info'] = '注册失败';
             	$data['url'] = '';
          	}
         	$this->ajaxReturn($data);           
          	
    	}else{
    		$this->assign('WEB_SITE_TITLE','完善信息 - 航天悟净');
    		$this->display();
    	}
    }     
    
	public function checkmobile($mobile=''){
		if(IS_POST){			
			$user=M("User")->where("mobile={$mobile}")->order("status desc")->find();
			$uid=$user['uid'];			
			$this->_setUid($uid);

			$UserVerify = new \Home\Model\ResetpwdModel("UserVerify");			
			list($vid,$mid) = $UserVerify->send_verify($uid, $mobile);
			
			if(0<$vid){
				$data['status']  = 1;
				$data['id']  = $uid;
				$data['vid']  = $vid;
				$data['mobile']  = $mobile;
				$this->ajaxReturn($data);
			}else{
				$authUid = uch_authcode($uid,'ENCODE');
				switch($vid) {
					case -21: $error = "此号码已被其他用户占用，请更换其它号码进行验证！"; break; 
					case -22: $error = "系统刚刚发送过验证码，请在下方输入验证码"; break; 
					case -24: $error = '验证码生成失败，请稍后再试！'; break;
					case -4: $error = '验证码生成失败，请稍后再试！'; break;
					case -5: $error = '不存在此手机号码的记录，无法验证！'; break;
					default: $error = '未知错误，请稍后再试！'; break; 
				}
				$data['status']  = -1;
				$data['info']  = $error;
				$this->ajaxReturn($data);
			}
		}else{
			$this->assign('WEB_SITE_TITLE','找回密码 - 航天悟净');
			$this->display();
		}
	}
		
	
	public function resetsuccess(){
		$this->assign('WEB_SITE_TITLE','重置密码 - 航天悟净');
		$this->display();
	}
	
	public function resetfail(){
		$this->assign('WEB_SITE_TITLE','重置密码 - 航天悟净');
		$this->display();
	}
    
    public function login($username = '', $password = ''){
    	if(IS_POST){
			$username=preg_replace('/\s+/', '', $username);
			$password=preg_replace('/\s+/', '', $password);          

			$user = new UserApi;			
			if(!($user->existsEmail($username)||$user->existsPhone($username)))
			{
				$data['status']=-1;
				$data['info']="该手机号未注册";
				$this->ajaxReturn($data);	
			}
			
			list($rStatus,$uid) = $user->login($username, $password);
			if($rStatus > 0){ 
				$info = D("User")->get_User_By_Mobile($username);
				$auth = array(
					'is_login'  =>  1,
					'uid'             => $uid,
					'username'        => $username
				);
				session('user_auth', $auth);			
				$data['status']=1;
				$data['url']='index.php?s=user/loginsuccess';
				$this->ajaxReturn($data);
			} else { 
				$data['status']=-2;
				$data['info']='密码错误';
				$this->ajaxReturn($data);
       		}
		} else {
			$this->assign('WEB_SITE_TITLE','登录 - 航天悟净');
			$this->display();
		}
    }
    
	/**
	 * 注销当前用户
	 * @return void
	 */
	public function logout(){
		if(IS_AJAX){			
			session('user_auth', null);
			$this->ajaxReturn('1');
		}		
	}
	
	public function order(){
		$session = session('user_auth');
		$uid = $session['uid'];
		$order = D("Order")->get_all(array("user_id" => $uid));
		foreach ($order as $v){
			$v['createtime']=date("Y年m月d日 H:i", $v['createtime']);
			$orderGoods  = M("OrderGoods")->where(array("order_no" => $v['order_no']))->find();
			$goods = M("Goods")->where(array("goods_id" => $orderGoods['goods_id']))->find();
			$v['pic']=$goods['pic_url'];
			$v['info'] = $orderGoods;			
			$list[]=$v;
		}	
		$this->assign('order', $list);
		$this->assign('WEB_SITE_TITLE','我的订单 - 航天悟净');
		$this->display();
	}
	
	public function orderview($orderid){
		$orderInfo = D("Order")->where(array('order_no'=>$orderid))->find();	
		$orderInfo['date']=date("Y年m月d日", $orderInfo['createtime']);	
		$orderInfo['time']=date("H:i:s", $orderInfo['createtime']);
		$orderGoods  = M("OrderGoods")->where(array("order_no" => $orderid))->find();
		$goods = M("Goods")->where(array("goods_id" => $orderGoods['goods_id']))->find();
		$orderGoods['pic']=$goods['pic_url'];

		$result  = file_get_contents("http://api.zto.cn/json.aspx?Userid=iwujing&Pwd=I8WU96JI2KG&SrtjobNo=$orderInfo[ship_no]");
		$result = json_decode($result, true);		
		$express = array_reverse($result['traces']);
		
		switch ($orderInfo['status']){
			case 0: $status='未付款';break;
			case 1: $status='已付款';break;
			case 2: $status='已发货';break;
			case 3: $status='已收货';break;	
		}
		$orderInfo['statusvalue']=$status;
		$this->assign('orderInfo', $orderInfo);
		$this->assign('orderGoods', $orderGoods);
		$this->assign('express', $express);
		$this->assign('WEB_SITE_TITLE','订单详情 - 航天悟净');
		$this->display();
	}
    
	//重新验证验证码是否过期
	private function re_verfity_code($vid,$mobile){
  		$tokens = D('UserVerifyToken');
       	$token = $tokens->get_model($vid,0);
      	$UserVerify = D('UserVerify');
      	$rid = $UserVerify->check_timeout($mobile,$token['token']);    	
      	return $rid;
	}
	
	//session 设置uid
	private function _setUid($uid){
		$authUid = uch_authcode($uid,'ENCODE');//加密uid
		session('resetpwd', array('authUid'=>$authUid));
	}
	//session 获得用户uid
	private function _getUid(){
		$uid = session('resetpwd.authUid');
		return uch_authcode($uid,'DECODE');//解密uid
	}
	
	//session 设置用户uid与过期时间
	private function _setResetExpire($expired){
		$expired = uch_authcode($expired,'ENCODE', C("DATA_AUTH_KEY"));//加密expired
		$uid = $this->_getUid();
		$uid = uch_authcode($uid,'ENCODE');//加密uid
		$resetpwd = array('authUid'=>$uid, 'expired'=>$expired);
		session('resetpwd', $resetpwd);
	}
	//session 获得用户操作的过期时间
	private function _getResetExpire(){
		$expired = session('resetpwd.expired');
		return uch_authcode($expired,'DECODE', C("DATA_AUTH_KEY"));//加密expired

	}	

	public function registersuccess(){
		$this->assign('WEB_SITE_TITLE','注册成功 - 航天悟净');
		$this->display();
	}
	
	public function registerfail(){
		$this->assign('WEB_SITE_TITLE','注册失败 - 航天悟净');
		$this->display();
	}
	
	public function loginsuccess(){
		$this->assign('WEB_SITE_TITLE','登录成功 - 航天悟净');
		$this->display();
	}
	
	public function loginfail(){
		$this->assign('WEB_SITE_TITLE','登录失败 - 航天悟净');
		$this->display();
	}
}