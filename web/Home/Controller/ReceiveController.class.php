<?php
namespace Home\Controller;
use Think\Controller;

class ReceiveController extends Controller {
	public function index($sn=''){		
		if(IS_POST){			
			$fcode = substr($sn, 0, 8);
			$vcode = substr($sn, 8);
			
			$GiftReceive = D('GiftReceive');
			$receiveList = $GiftReceive->getListBySn($sn);
			$snSeq = D('snSeq');
			$seqList = $snSeq->get_List_By_sn($sn);
			$snPolicy = D('snPolicy');
			$policyInfo = $snPolicy->get_Info_By_sn($sn);
			
			if(is_array($receiveList) && !is_array($seqList) && !is_array($policyInfo)){				
				$data['status'] = -1;
		       	$data['info'] = '该SN码已领取过清洗套装！';
		       	$this->ajaxReturn($data);
			}else{
				$SnModel = D('Sn');
				$snList = $SnModel->get_List_By_Code($fcode,$vcode);
				if($snList['type_id']==4){
					$type_id = 21;
				}			
				
				$SnWujingModel = D('SnWujing');			
				$snWujingList = $SnWujingModel->get_List_By_Code($fcode,$vcode);
				$goods_id = isset($type_id)?$type_id:$snWujingList['goods_id'];	
				$goodsModel = D("goods");
				$goodsInfo = $goodsModel->getInfoById($goods_id);	
				
				$snList = isset($snList)?$snList:$snWujingList;
	
				$giftPolicy = D("GiftPolicy");
				$policyList = $giftPolicy->get_policy_by_goodsId($goods_id);
				
				if(is_array($snList) && is_array($policyList)){					
					$data['status']=1;
					$data['name']=isset($type_name)?$type_name:$goodsInfo['goods_name'];
					$data['pic']="Public/Home/images/".$goodsInfo['pic_url'];
					$this->ajaxReturn($data);					
				}elseif(is_array($snList) && !is_array($policyList)){
					$data['status']=-1;
					$data['info']='该机型暂时不能领取洗护套装！';
					$this->ajaxReturn($data);
				}else{
					$data['status']=-1;
					$data['info']='SN码不存在，请核对后重新输入验证！';
					$this->ajaxReturn($data);
				}
			}									
		}else{		
			$this->assign('WEB_SITE_TITLE','SN码验证 - 航天悟净');
			$this->display();	
		}		
	}
	
	public function review($sn='',$sequence=''){
		$fcode = substr($sn, 0, 8);
		$vcode = substr($sn, 8);
		
		$SnModel = D('Sn');
		$snList = $SnModel->get_List_By_Code($fcode,$vcode);	
		$SnWujingModel = D('SnWujing');			
		$snWujingList = $SnWujingModel->get_List_By_Code($fcode,$vcode);
		if($snList['type_id']==4){
			$type_id = 21;
		}	
		$goods_id = isset($type_id)?$type_id:$snWujingList['goods_id'];
		
		$snPolicy = D('SnPolicy');
		$policyInfo = $snPolicy->get_Info_By_sn($sn);
		$GiftPolicy = D('GiftPolicy');
		if(is_array($policyInfo)){
			$policyList = $GiftPolicy->getListById($policyInfo['policy_id']);
		}else{
			$policyList = $GiftPolicy->get_policy_by_goodsId($goods_id);
		}	
		
		if(IS_POST){		
			$GiftReceive = D('GiftReceive');
			$receiveInfo = $GiftReceive->getListBySnAndSeq($sn,$sequence-1);
			if($receiveInfo){
				$snSeq = D('snSeq');
				$seqInfo = $snSeq->getListBySnAndSeq($sn,$sequence);
				if(!is_array($seqInfo)){
					$GiftSeq = D('GiftSeq');
					$seqInfo = $GiftSeq->get_List_By_Seq($policyList['policy_id'],$sequence);	
					$seqInfo['total_time']=$policyList['total_time'];
				}
			}	
			if(is_array($receiveInfo) && ($receiveInfo['createtime'] + $seqInfo['cycle_time'] - $seqInfo['preset_time']) > time()){
				$data['status']=-1;
				$data['info']='未到领取时间请您等待！';
				$this->ajaxReturn($data);
			}else if(is_array($receiveInfo) && ($receiveInfo['createtime'] + $seqInfo['total_time']) < time()){
				$data['status']=-1;
				$data['info']='超出有效时间不能领取！';
				$this->ajaxReturn($data);
			}else{
				$data['status']=1;
				$data['url']="index.php?s=receive/info/sn/{$sn}/rid/{$sequence}";
				$this->ajaxReturn($data);
			}			
		}else{
			$snSeq = D('snSeq');
			$seqList = $snSeq->get_List_By_sn($sn);
			$seqCount = count($seqList);
			if(!is_array($seqList)){				
				$GiftSeq = D('GiftSeq');
				$seqList = $GiftSeq->get_List_by_id($policyList['policy_id']);		
				$seqCount = count($seqList);
				foreach ($seqList as &$v){
					$Gift = D('Gift');
					$giftList = $Gift->get_gift_by_id($v['gift_id']);
					$v['gift_name']=$giftList['gift_name'];
					$v['icon']=$giftList['icon'];
				}
			}

			$GiftReceive = D('GiftReceive');
			$receiveList = $GiftReceive->getListBySn($sn);
			$receiveCount = count($receiveList);
			foreach ($receiveList as $k => &$v){
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
					$v['address'] = $provinceName.$cityName.$zoneName.$v['address'];
				}
				$seqList[$k]['createtime']=date("Y-m-d H:i", $v['createtime']);
				$seqList[$k]['username']=$v['username'];
				$seqList[$k]['mobile']=$v['mobile'];
				$seqList[$k]['address']=$v['address'];
			}
			$this->assign('sn', $sn);
			$this->assign('receiveCount', $receiveCount);
			$this->assign('seqList', $seqList);
			$this->assign('policyList', $policyList);
			$this->assign('seqCount', $seqCount);
			$this->assign('receiveCount', $receiveCount);
			$this->assign('receiveList', $receiveList);
			$this->assign('WEB_SITE_TITLE','领取记录 - 航天悟净');		
			$this->display();
		}	
	}
	
	public function info($sn='',$receivename='',$receivemobile='',$receiveemail='',$province='',$city='',$zone='',$address='',$rid=''){
		$session=session('user_auth'); 	
		if(IS_POST){
			$uid = $session['uid'];
			$GiftReceive = D('GiftReceive');
			
			$areaProvince = D('AreaProvince');
			$provinceInfo = $areaProvince->where(array('id' => $province))->field('name')->find();
			if($provinceInfo['name'] == '直辖市'){
				$provinceName = '';
			}else{
				$provinceName = $provinceInfo['name'].'省';
			}
					
			$areaCity = D('AreaCity');
			$cityInfo = $areaCity->where(array('id' => $city))->field('name')->find();
			$cityName = $cityInfo['name'].'市';
			
			$areaZone = D('AreaZone');
			$zoneInfo = $areaZone->where(array('id' => $zone))->field('name')->find();
			if(strstr($zoneInfo['name'], "区") || strstr($zoneInfo['name'], "县")){
				$zoneName = $zoneInfo['name'];
			}else{
				$zoneName = $zoneInfo['name'].'区';
			}
			
			$status = 0;
			$is_send = 0;
			$receive_id = $GiftReceive->saveInfo($uid,$sn,$receivename,$receivemobile,$receiveemail,$province,$city,$zone,$address,$status,$rid,$is_send);
			
			if($receive_id && ($rid == 1) ){
				$fcode = substr($sn, 0, 8);
				$vcode = substr($sn, 8);
				$SnModel = D('Sn');
				$snList = $SnModel->get_List_By_Code($fcode,$vcode);	
				$SnWujingModel = D('SnWujing');			
				$snWujingList = $SnWujingModel->get_List_By_Code($fcode,$vcode);
				if($snList['type_id']==4){
					$type_id = 21;
				}	
				$goods_id = isset($type_id)?$type_id:$snWujingList['goods_id'];
		
				$GiftPolicy = D('GiftPolicy');
				$policyList = $GiftPolicy->get_policy_by_goodsId($goods_id);
				
				$GiftSeq = D('GiftSeq');
				$seqList = $GiftSeq->get_List_by_id($policyList['policy_id']);
				foreach ($seqList as &$v){
					$Gift = D('Gift');
					$giftList = $Gift->get_gift_by_id($v['gift_id']);
					$gift_name = $giftList['gift_name'];
					$icon = $giftList['icon'];
					$sequence = $v['sequence'];
					$total_time = $policyList['total_time'];
					$cycle_time = $v['cycle_time'];
					$preset_time = $v['preset_time'];
					$remind_time = $v['remind_time'];
					
					$snSeq = D('SnSeq');	
					$status = 1;		
					$sid = $snSeq->saveInfo($sn,$sequence,$gift_name,$icon,$total_time,$cycle_time,$preset_time,$remind_time,$status);												
				}		
			}
			if($receive_id){
	    		$data['status'] = 1;
	        	$data['url'] = 'index.php?s=receive/success';
	    	}else{
	    		$data['status'] = -1;
	        	$data['url'] = 'index.php?s=receive/fail';
	    	}			
			$this->ajaxReturn($data);
		}else{		
			$GiftReceive = D('GiftReceive');
			if($rid >1){
				$receiveInfo = $GiftReceive->getListBySnAndSeq($sn,$rid-1);
			}
			
			$province = D('AreaProvince');
			$provinces = $province->get_all('status=1','id,name');
			
			if ($receiveInfo['city']) {
	            $city = D("AreaCity");
	            $citys = $city->get_all('status=1 and parent_id=' . $receiveInfo['province'], 'id,name,parent_id');
	            if ($citys) {
	                foreach ($citys as $rs) {
	                    if ($rs['id'] == $receiveInfo['city_id']) {
	                        $city_name = $rs['name'] . "，";
	                    }
	                }
	            }
	            $this->assign('citys', $citys);
	        }

	        if ($receiveInfo['zone']) {
	            $zone = D("AreaZone");
	            $zones = $zone->get_all('status=1 and parent_id=' . $receiveInfo['city'], 'id,name,parent_id');
	            if ($zones) {
	                foreach ($zones as $rs) {
	                    if ($rs['id'] == $receiveInfo['zone_id']) {
	                        $zones_name = $rs['name'] . "，";
	                    }
	                }
	            }
	            $this->assign('zones', $zones);
	        }
			
			$this->assign('provinces',$provinces);
    		$this->assign('sn',$sn);
    		$this->assign('rid',$rid);
    		$this->assign('receiveInfo',$receiveInfo);
    		$this->assign('WEB_SITE_TITLE','登记信息 - 航天悟净');
			$this->display();	
		}		
	}
	
	public function scan(){
		$GiftReceive = D('GiftReceive');
		$receiveList = $GiftReceive->get_all(array("is_send = 0"),array());
		foreach ($receiveList as $v){
			$sn = $v['sn'];
			$createtime = $v['createtime'];
			$sequence = $v['times'];		
			
			$snSeq = D('snSeq');
			$seqList = $snSeq->getListBySnAndSeq($sn,$sequence);
			$sendtime = $v['createtime']+$seqList['cycle_time']-$seqList['remind_time'];
			$total_time = $seqList['total_time']+$v['createtime'];

			if(is_array($seqList)){
				if(time() > $sendtime && time() < $total_time){
					$giftCmsTmp = D("GiftCmsTmp");
					$mobile = $v['mobile'];
					$status = 0;
					$gid = $giftCmsTmp->saveInfo($mobile,$status);
					if($gid){
						$arr['is_send'] = 1;
						M("GiftReceive")->where("receive_id = $v[receive_id]" )->save($arr);
					}
				}
			}
		}	
	}
	
	public function send(){
		$giftCmsTmp = D("GiftCmsTmp");
		$cmsList = $giftCmsTmp->get_all(array("status = 0"),array());
		foreach ($cmsList as $v){
			$mobile = $v['mobile'];
			$code = "尊敬的悟净用户，供您下一年使用的滤网清洗剂，现在可以免费领取了。请登陆www.iwujing.com领取，如有疑问请致电400-0600-677。";
			$id = send_sms($mobile,$code);
			if($id){
				$arr['status'] = 1;
				M("GiftCmsTmp")->where("cms_id = $v[cms_id]" )->save($arr);
			}
		}
	}
	
	public function success(){
		$this->assign('WEB_SITE_TITLE','登记成功 - 航天悟净');
		$this->display();
	}
	
	public function fail(){
		$this->assign('WEB_SITE_TITLE','登记失败 - 航天悟净');
		$this->display();
	}
	
	
	public function prize(){
		$this->assign('WEB_SITE_TITLE','中奖名单 - 航天悟净');
		$this->display();
	}
	
	public function cityajax($type,$id){
		if($type=='province'){
			$city = D('AreaCity');
		}elseif($type=='city'){
			$city = D('AreaZone');
		}elseif($type=='zone'){
			$city = D('AreaStreet');
		}
		$citys = $city->get_all('status=1 and parent_id='.$id,'id,name');
		$this->ajaxReturn($citys);
	}
}