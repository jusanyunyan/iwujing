<?php
namespace Home\Controller;
use Think\Controller;
class CarController extends Controller {
    public function index(){
    	$session=session('user_auth');  	
    	if(IS_POST){
    		if($session['is_login']){
    			$data['status']=1;
    		}else{
    			$data['status']=0;
    			$data['url']='index.php?s=user/login';
    		}
    		$this->ajaxReturn($data);
    	}else{
    		$this->assign('session',$session);
    		$this->display();
    	}
    }
}