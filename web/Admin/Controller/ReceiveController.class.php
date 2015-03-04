<?php
namespace Admin\Controller;
class ReceiveController extends AdminController {
    public function index(){
        if(is_login()){
	        Cookie('__forward__',$_SERVER['REQUEST_URI']);		        
	        $map = array();
	        if(isset($_GET['code'])){
	            $map['sn']    =   array('like', '%'.$_GET['code'].'%');
	        }		
	        $receiveList = $this->lists('GiftReceive',$map,'receive_id desc');
	        
	    	foreach ($receiveList as &$v){	        	  
	        	switch ($v['status']){
	        		case 0: $v['status']='未发货';break;
	        		case 1: $v['status']='已发货';break;
	        	}
	        	$v['createtime'] = date('Y-m-d H:i:s', $v['createtime']);
	        	$v['value']=$v['fcode'].$v['vcode'];  

	        	$user = M('User')->field(true)->find($v['user_id']); 
	        	$v['user'] = $user['username'];
	        	
	        	if($v['province'] && $v['city'] && $v['zone']){
	        		$areaProvince = D('AreaProvince');
					$provinceInfo = $areaProvince->where(array('id' => $v['province']))->field('name')->find();
					if($provinceInfo['name'] == '直辖市'){
						$provinceName = '';
					}else{
						$provinceName = $provinceInfo['name'].'省';
					}
							
					$areaCity = D('AreaCity');
					$cityInfo = $areaCity->where(array('id' => $v['city']))->field('name')->find();
					$cityName = $cityInfo['name'].'市';
					
					$areaZone = D('AreaZone');
					$zoneInfo = $areaZone->where(array('id' => $v['zone']))->field('name')->find();
					if(strstr($zoneInfo['name'], "区") || strstr($zoneInfo['name'], "县")){
						$zoneName = $zoneInfo['name'];
					}else{
						$zoneName = $zoneInfo['name'].'区';
					}
					$address = $provinceName.$cityName.$zoneName.$v['address'];
					$v['address'] = $address;
	        	}
	        }    
	        $this->assign('receiveList',$receiveList);
	     	$this->meta_title = 'SN码管理';
	    	$this->display();            
        } else {
            $this->redirect('Public/login');
        }
    }
    
	public function edit($id = 0){
        if(IS_POST){
            $reveive = D('GiftReceive');
            $data = $reveive->create();
            if($data){
                if($reveive->save($data)){                	
                    S('DB_RECEIVE_DATA',null);
                    //记录行为
                    action_log('update_receive','receive',$data['receive_id'],UID);
                    $this->success('更新成功', Cookie('__forward__'));
                } else {
                    $this->error('更新失败');
                }
            } else {
                $this->error($reveive->getError());
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = M('GiftReceive')->field(true)->find($id);  
            
       		$province = D('Home/AreaProvince');
			$provinces = $province->get_all('status=1','id,name');
			
			if ($info['city']) {
	            $city = D("Home/AreaCity");
	            $citys = $city->get_all('status=1 and parent_id=' . $info['province'], 'id,name,parent_id');
	            if ($citys) {
	                foreach ($citys as $rs) {
	                    if ($rs['id'] == $info['city_id']) {
	                        $city_name = $rs['name'] . "，";
	                    }
	                }
	            }
	            $this->assign('citys', $citys);
	        }

	        if ($info['zone']) {
	            $zone = D("Home/AreaZone");
	            $zones = $zone->get_all('status=1 and parent_id=' . $info['city'], 'id,name,parent_id');
	            if ($zones) {
	                foreach ($zones as $rs) {
	                    if ($rs['id'] == $info['zone_id']) {
	                        $zones_name = $rs['name'] . "，";
	                    }
	                }
	            }
	            $this->assign('zones', $zones);
	        }       	
            if(false === $info){
                $this->error('获取配置信息错误');
            }
            $this->assign('info', $info);
            $this->assign('provinces',$provinces);
            $this->meta_title = '编辑SN码领取表';
            $this->display();
        }
    }
}
