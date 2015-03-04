<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){   	
    	$session=session('user_auth');  	
    	if(IS_POST){
    		if($session['is_login']){
    			$data['status']=1;
    		}else{
    			$data['status']=0;
    			$data['url']='index.php?s=user/signup';
    		}
    		$this->ajaxReturn($data);
    	}else{
    		$Goods = D('Goods');
    		$type='L';
    		$color='科技白';
    		$details = $Goods->getInfoByTypeAndColor($type,$color);
    		
    		$this->assign('session',$session);
    		$this->display();
    	}
    }
}