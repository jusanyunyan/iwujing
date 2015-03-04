<?php
namespace Home\Controller;
use Think\Controller;
class SnController extends Controller {
    public function index(){	
    	if(IS_POST){
    		$type=$size=$color=$number=$sizename=$dealer='';
			$dealer=isset($_POST['dealer'])?$_POST['dealer']:'';
			$type=isset($_POST['type'])?$_POST['type']:0;
			switch($type){
				case 1:
					$typename='车载机';
					$number=isset($_POST['number1'])?$_POST['number1']:0;
					$color=isset($_POST['color1'])?$_POST['color1']:0;	
					switch ($color){
						case 1: $colorname='黑色'; $model='HTTX-MC01-BK'; $goods_id=7; break;
						case 2: $colorname='酒红色'; $model='HTTX-MC01-RD'; $goods_id=8; break;
					}	
				break;
				case 2:
					$typename='壁挂单机';
					$number=isset($_POST['number2'])?$_POST['number2']:0;	
					$color=isset($_POST['color2'])?$_POST['color2']:0;	
					if($color==1) $colorname='白色';$model='HTTX-MB01';$goods_id=9;
				break;
				case 3:
					$typename='壁挂远控';
					$number=isset($_POST['number2'])?$_POST['number2']:0;	
					$color=isset($_POST['color2'])?$_POST['color2']:0;	
					if($color==1) $colorname='白色';$model='HTTX-MB01M';$goods_id=9;
				break;
				case 4:
					$typename='悟净机';
					$number=isset($_POST['number3'])?$_POST['number3']:0;	
					$color=isset($_POST['color3'])?$_POST['color3']:0;	
					$size=isset($_POST['size'])?$_POST['size']:0;
					switch ($color){
						case 1: $colorname='科技白';
							if($size == 1){
								$model = 'HTTX-WJL-BK';$goods_id=1;
							}else if($size == 2){
								$model = 'HTTX-WJM-BK';$goods_id=2;
							}else if($size == 3){
								$model = 'HTTX-WJS-BK';$goods_id=3;
							} 
						break;
						case 2: $colorname='薄荷蓝';
							if($size == 1){
								$model = 'HTTX-WJL-SB';$goods_id=4;
							}else if($size == 2){
								$model = 'HTTX-WJM-SB';$goods_id=5;
							}else if($size == 3){
								$model = 'HTTX-WJS-SB';$goods_id=6;
							} 
						break;
						case 3: $colorname='迷彩绿';
							if($size == 1){
								$model = 'HTTX-WJL-CF';$goods_id=10;
							}
						break;
						case 4: $colorname='天清蓝';
							if($size == 1){
								$model = 'HTTX-WJL-LB';$goods_id=11;
							}else if($size == 2){
								$model = 'HTTX-WJM-LB';$goods_id=12;
							}
						break;
						case 5: $colorname='水色蓝';
							if($size == 1){
								$model = 'HTTX-WJL-BE';$goods_id=13;
							}else if($size == 2){
								$model = 'HTTX-WJM-BE';$goods_id=14;
							}
						break;
						case 6: $colorname='咖啡色';
							if($size == 1){
								$model = 'HTTX-WJL-BN';$goods_id=15;
							}else if($size == 2){
								$model = 'HTTX-WJM-BN';$goods_id=16;
							}else if($size == 3){
								$model = 'HTTX-WJS-BN';$goods_id=17;
							} 
						break;
						case 7: $colorname='酒红色';
							if($size == 1){
								$model = 'HTTX-WJL-RD';$goods_id=18;
							}else if($size == 2){
								$model = 'HTTX-WJM-RD';$goods_id=19;
							}else if($size == 3){
								$model = 'HTTX-WJS-RD';$goods_id=20;
							} 
						break;
					}
					
					switch ($size){
						case 1: $sizename='大';break;
						case 2: $sizename='中';break;
						case 3: $sizename='小';break;
					}	
				break;
			}
			$tags = 0;
			$res = array();		
			$SnWujing = D("SnWujing");
			$res = $SnWujing->get_last();
			if(is_array($res)){
				$tags = $res['id'];
			}else{
				$sn = D("Sn");
				$res = $sn->get_last();
				$tags = $res['id'];
			}
			if($number > 100){
				$number = intval($number*1.05);
			}
			$startnum=$tags+1;
			$count=$number+$tags;
			for($i=$startnum;$i<=$count;$i++){
				$vcode='';
				$randnum='';
				$str="1,2,3,4,5,6,7,8,9";
				$list=explode(",",$str);
				for($j=0;$j<6;$j++){
					$randnum=rand(0,8); 
					$vcode .= $list[$randnum];
				}
				$fcode=14000000+$i;
				$createtime=time();
				$updatetime=time();
				
				$snWujing = D("SnWujing");
				$status = 0;
				$is_print = 0;
				$sid = $snWujing->saveInfo($i,$model,$goods_id,$typename,$sizename,$colorname,$fcode,$vcode,$dealer,$createtime,$updatetime,$status,$is_print);			
			}

    		if($sid == $count){
				$data['status']=1;
				$data['info']='生成SN码成功';
				$data['url']='index.php?s=sn/index';
				$this->ajaxReturn($data);
			}else{
				$data['status']=-1;
				$data['info']='生成SN码失败';
			}
    	}else{
    		$this->assign('WEB_SITE_TITLE','SN码生成 - 航天悟净');
    		$this->display();
    	}
    }
    
    public function output(){  
    	$this->assign('WEB_SITE_TITLE','SN码打印- 航天悟净');
    	$this->display();
    }
    
    public function export(){

    	$type=isset($_POST['type'])?$_POST['type']:0;
		$dealer=isset($_POST['dealer'])?$_POST['dealer']:'';
		
		
		switch($type){
			case 1:  $typename="车载机";break;
			case 2:  $typename="壁挂单机";break;
			case 3:  $typename="壁挂远控";break;
			case 4:  $typename="悟净机";break;
		}
    	$sn = D("SnWujing");
    	
    	switch ($dealer){
    		case '':	$dealerName="";break;
    		case '航天通信北京分公司':  $dealerName="HTTXBJ";break;
			case '广州金琥环保科技有限公司':  $dealerName="GZJH";break;
			case '四川民望新能源科技开发有限公司':  $dealerName="SCMW";break;
			case '上海澳马车辆物资采购有限公司':  $dealerName="SHAM";break;
			case '北京佰朔泉科技有限公司':  $dealerName="BJBSQ";break;
			case '北京市傲博胜咨询有限公司':  $dealerName="BJABS";break;
			case '东莞市宇洁新材料有限公司':  $dealerName="DGYJ";break;
			case '淘微网络科技（上海）有限公司':  $dealerName="SHTW";break;
			case '元天百利科技有限公司':  $dealerName="YTBL";break;
			case '上海佳潇信息科技有限公司':  $dealerName="SHJX";break;
    	}
    	if($typename == '车载机'){
    		$list1 = $sn->get_all(array('type'=>$typename,'color'=>'黑色','is_print'=>0,'dealer'=>$dealer),array('id','model','type','color','fcode','vcode'));
    		if(is_array($list1)){
	    		foreach ($list1 as $v){
	    			$t['id']=$v['id'];
	    			$t['model']=$v['model'];
	    			$t['type']=$v['type'];
	    			$t['color']=$v['color'];
	    			$t['sn']=$v['fcode'].$v['vcode']." ";
	    			$list1arr[]=$t;
	    		}
    		}
    		$list2 = $sn->get_all(array('type'=>$typename,'color'=>'酒红色','is_print'=>0,'dealer'=>$dealer),array('id','model','type','color','fcode','vcode'));
    		if(is_array($list2)){
	    		foreach ($list2 as $v){
	    			$t['id']=$v['id'];
	    			$t['model']=$v['model'];
	    			$t['type']=$v['type'];
	    			$t['color']=$v['color'];
	    			$t['sn']=$v['fcode'].$v['vcode']." ";
	    			$list2arr[]=$t;
	    		}
    		}
    		$list = array(isset($list1arr)?$list1arr:'',isset($list2arr)?$list2arr:'');
    		$sheet = array(isset($list1arr)?'黑色':'',isset($list2arr)?'酒红色':'');
    		
    		$listarr = array();  
		    foreach ($list as $key => $val) {  
		        if (empty($val)) {  
		            continue;  
		        }  
		        $listarr[] = $val;  
		    }  
		    
    		$sheetarr = array();  
		    foreach ($sheet as $key => $val) {  
		        if (empty($val)) {  
		            continue;  
		        }  
		        $sheetarr[] = $val;  
		    }	  
		    $return = $sn->where(array('type'=>$typename,'dealer'=>$dealer))->save(array('is_print' => 1));		    
		    if($return >0){
    			exportexcel($listarr,$dealerName.$typename,array('编号','型号','机型','颜色','SN码'),$sheetarr);
    			
		    }else{
		    	$this->redirect('sn/output');
		    }
    	}else if($typename == '悟净机'){
    		$list1 = $sn->get_all(array('type'=>$typename,'size'=>'大','color'=>'科技白','is_print'=>0,'dealer'=>$dealer),array('id','model','type','size','color','fcode','vcode'));
    		if(is_array($list1)){
	    		foreach ($list1 as $v){
	    			$t['id']=$v['id'];
	    			$t['model']=$v['model'];
	    			$t['type']=$v['type'];
	    			$t['size']=$v['size'];
	    			$t['color']=$v['color'];
	    			$t['sn']=$v['fcode'].$v['vcode']." ";
	    			$list1arr[]=$t;
	    		}
    		}
    		$list2 = $sn->get_all(array('type'=>$typename,'size'=>'中','color'=>'科技白','is_print'=>0,'dealer'=>$dealer),array('id','model','type','size','color','fcode','vcode'));
    		if(is_array($list2)){
	    		foreach ($list2 as $v){
	    			$t['id']=$v['id'];
	    			$t['model']=$v['model'];
	    			$t['type']=$v['type'];
	    			$t['size']=$v['size'];
	    			$t['color']=$v['color'];
	    			$t['sn']=$v['fcode'].$v['vcode']." ";
	    			$list2arr[]=$t;
	    		}
    		}
    		$list3 = $sn->get_all(array('type'=>$typename,'size'=>'小','color'=>'科技白','is_print'=>0,'dealer'=>$dealer),array('id','model','type','size','color','fcode','vcode'));
    		if(is_array($list3)){
	    		foreach ($list3 as $v){
	    			$t['id']=$v['id'];
	    			$t['model']=$v['model'];
	    			$t['type']=$v['type'];
	    			$t['size']=$v['size'];
	    			$t['color']=$v['color'];
	    			$t['sn']=$v['fcode'].$v['vcode']." ";
	    			$list3arr[]=$t;
	    		}
    		}
    		
    		$list4 = $sn->get_all(array('type'=>$typename,'size'=>'大','color'=>'薄荷蓝','is_print'=>0,'dealer'=>$dealer),array('id','model','type','size','color','fcode','vcode'));
    		if(is_array($list4)){
	    		foreach ($list4 as $v){
	    			$t['id']=$v['id'];
	    			$t['model']=$v['model'];
	    			$t['type']=$v['type'];
	    			$t['size']=$v['size'];
	    			$t['color']=$v['color'];
	    			$t['sn']=$v['fcode'].$v['vcode']." ";
	    			$list4arr[]=$t;
	    		}
    		}
    		$list5 = $sn->get_all(array('type'=>$typename,'size'=>'中','color'=>'薄荷蓝','is_print'=>0,'dealer'=>$dealer),array('id','model','type','size','color','fcode','vcode'));
    		if(is_array($list5)){
	    		foreach ($list5 as $v){
	    			$t['id']=$v['id'];
	    			$t['model']=$v['model'];
	    			$t['type']=$v['type'];
	    			$t['size']=$v['size'];
	    			$t['color']=$v['color'];
	    			$t['sn']=$v['fcode'].$v['vcode']." ";
	    			$list5arr[]=$t;
	    		}
    		}
    		$list6 = $sn->get_all(array('type'=>$typename,'size'=>'小','color'=>'薄荷蓝','is_print'=>0,'dealer'=>$dealer),array('id','model','type','size','color','fcode','vcode'));
    		if(is_array($list6)){
	    		foreach ($list6 as $v){
	    			$t['id']=$v['id'];
	    			$t['model']=$v['model'];
	    			$t['type']=$v['type'];
	    			$t['size']=$v['size'];
	    			$t['color']=$v['color'];
	    			$t['sn']=$v['fcode'].$v['vcode']." ";
	    			$list6arr[]=$t;
	    		}
    		}
    		  		
    		$list7 = $sn->get_all(array('type'=>$typename,'size'=>'大','color'=>'迷彩绿','is_print'=>0,'dealer'=>$dealer),array('id','model','type','size','color','fcode','vcode'));
    		if(is_array($list7)){
	    		foreach ($list7 as $v){
	    			$t['id']=$v['id'];
	    			$t['model']=$v['model'];
	    			$t['type']=$v['type'];
	    			$t['size']=$v['size'];
	    			$t['color']=$v['color'];
	    			$t['sn']=$v['fcode'].$v['vcode']." ";
	    			$list7arr[]=$t;
	    		}
    		}
    		$list8 = $sn->get_all(array('type'=>$typename,'size'=>'大','color'=>'天清蓝','is_print'=>0,'dealer'=>$dealer),array('id','model','type','size','color','fcode','vcode'));
    		if(is_array($list8)){
	    		foreach ($list8 as $v){
	    			$t['id']=$v['id'];
	    			$t['model']=$v['model'];
	    			$t['type']=$v['type'];
	    			$t['size']=$v['size'];
	    			$t['color']=$v['color'];
	    			$t['sn']=$v['fcode'].$v['vcode']." ";
	    			$list8arr[]=$t;
	    		}
    		}
    		$list9 = $sn->get_all(array('type'=>$typename,'size'=>'中','color'=>'天清蓝','is_print'=>0,'dealer'=>$dealer),array('id','model','type','size','color','fcode','vcode'));
    		if(is_array($list9)){
	    		foreach ($list9 as $v){
	    			$t['id']=$v['id'];
	    			$t['model']=$v['model'];
	    			$t['type']=$v['type'];
	    			$t['size']=$v['size'];
	    			$t['color']=$v['color'];
	    			$t['sn']=$v['fcode'].$v['vcode']." ";
	    			$list9arr[]=$t;
	    		}
    		}
    		
    		$list10 = $sn->get_all(array('type'=>$typename,'size'=>'大','color'=>'水色蓝','is_print'=>0,'dealer'=>$dealer),array('id','model','type','size','color','fcode','vcode'));
    		if(is_array($list10)){
	    		foreach ($list10 as $v){
	    			$t['id']=$v['id'];
	    			$t['model']=$v['model'];
	    			$t['type']=$v['type'];
	    			$t['size']=$v['size'];
	    			$t['color']=$v['color'];
	    			$t['sn']=$v['fcode'].$v['vcode']." ";
	    			$list10arr[]=$t;
	    		}
    		}
    		$list11 = $sn->get_all(array('type'=>$typename,'size'=>'中','color'=>'水色蓝','is_print'=>0,'dealer'=>$dealer),array('id','model','type','size','color','fcode','vcode'));
    		if(is_array($list11)){
	    		foreach ($list11 as $v){
	    			$t['id']=$v['id'];
	    			$t['model']=$v['model'];
	    			$t['type']=$v['type'];
	    			$t['size']=$v['size'];
	    			$t['color']=$v['color'];
	    			$t['sn']=$v['fcode'].$v['vcode']." ";
	    			$list11arr[]=$t;
	    		}
    		}
    		$list = array(isset($list1arr)?$list1arr:'',isset($list2arr)?$list2arr:'',isset($list3arr)?$list3arr:'',isset($list4arr)?$list4arr:'',isset($list5arr)?$list5arr:'',isset($list6arr)?$list6arr:'',isset($list7arr)?$list7arr:'',isset($list8arr)?$list8arr:'',isset($list9arr)?$list9arr:'',isset($list10arr)?$list10arr:'',isset($list11arr)?$list11arr:'');
    		$sheet = array(isset($list1arr)?'大科技白':'',isset($list2arr)?'中科技白':'',isset($list3arr)?'小科技白':'',isset($list4arr)?'大薄荷蓝':'',isset($list5arr)?'中薄荷蓝':'',isset($list6arr)?'小薄荷蓝':'',isset($list7arr)?'大迷彩绿':'',isset($list8arr)?'大天清蓝':'',isset($list9arr)?'中天清蓝':'',isset($list10arr)?'大水色蓝':'',isset($list11arr)?'中水色蓝':'');
    		
    		$listarr = array();  
		    foreach ($list as $key => $val) {  
		        if (empty($val)) {  
		            continue;  
		        }  
		        $listarr[] = $val;  
		    }  
		    
    		$sheetarr = array();  
		    foreach ($sheet as $key => $val) {  
		        if (empty($val)) {  
		            continue;  
		        }  
		        $sheetarr[] = $val;  
		    }
    	  	$return = $sn->where(array('type'=>$typename,'dealer'=>$dealer))->save(array('is_print' => 1));	
		    if($return >0){
    			exportexcel($listarr,$dealerName.$typename,array('编号','型号','机型','尺寸','颜色','SN码'),$sheetarr);
		    }else{
		    	$this->redirect('sn/output');
		    }
    	}else if($typename == '壁挂单机'){
    		$list = $sn->get_all(array('type'=>$typename,'dealer'=>$dealer),array('id','model','type','color','fcode','vcode'));
    		foreach ($list as $v){
    			$t['id']=$v['id'];
    			$t['model']=$v['model'];
    			$t['type']=$v['type'];
    			$t['color']=$v['color'];
    			$t['sn']=$v['fcode'].$v['vcode']." ";
    			$listarr[]=$t;
    		}
    		$list = array(isset($listarr)?$listarr:'');
    		$sheet = array(isset($listarr)?'白色':'');
    		
    		$return = $sn->where(array('type'=>$typename,'dealer'=>$dealer))->save(array('is_print' => 1));	
    		if($return >0){
    			exportexcel($list,$dealer.'-'.$typename,array('编号','型号','机型','颜色','SN码'),$sheet);
		    }else{
		    	$this->redirect('sn/output');
		    }  		
    	}else if($typename == '壁挂远控'){
    		$list = $sn->get_all(array('type'=>$typename,'dealer'=>$dealer),array('id','model','type','color','fcode','vcode'));
    		foreach ($list as $v){
    			$t['id']=$v['id'];
    			$t['model']=$v['model'];
    			$t['type']=$v['type'];
    			$t['color']=$v['color'];
    			$t['sn']=$v['fcode'].$v['vcode']." ";
    			$listarr[]=$t;
    		}
    		$list = array(isset($listarr)?$listarr:'');
    		$sheet = array(isset($listarr)?'白色':'');
    		
    		$return = $sn->where(array('type'=>$typename,'dealer'=>$dealer))->save(array('is_print' => 1));	
    		if($return >0){
    			exportexcel($list,$dealerName.$typename,array('编号','型号','机型','颜色','SN码'),$sheet);
		    }else{
		    	$this->redirect('sn/output');
		    }  		
    	}
    
    }
    
	
    
    
}