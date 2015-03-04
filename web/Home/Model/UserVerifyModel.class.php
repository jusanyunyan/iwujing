<?php
/**
 * Created by wujianke
 * Date: 14-8-10
 * 功能: 和UserVerify表相关的业务逻辑处理和数据处理
 */ 
namespace Home\Model;
use Think\Model;

class UserVerifyModel extends Model{
	
	/**
	 * Created by wujianke
	 * Date: 14-8-10
	 * 功能:给固定手机发送验证码
	 * @param  $mobile  接受验证码的手机号   
	 * @return array(状态值，生成的验证码信息ID)
	 */ 
	public function send_verify($mobile)
	{
		$timeout=600;
		$Token = D("UserVerifyToken");
		
		$map= array();
		$map['verify_type'] = 0;
		$map['verify_content'] = $mobile;
		$model = $this->where($map)->field('id,user_id,verify_type,verify_status,expired_time')->find();
		
		if(is_array($model)){
			if($model['verify_status']==0){
				//检查是否存在Token
				$token=$Token->get_model($model['id'],0);
				//存在Token
				if(is_array($token)){
					//已过期，删除当前记录，再产生一个
					if(($token['expired_time']-$timeout+60)<NOW_TIME){			
						//修改UserVerify的过期时间
						$r=$Token->del_model($model['id'],0);
						if($r !== false){
							//删除成功，再产生一个
							$this->send_code($model['id'],NOW_TIME+$timeout, $mobile,true);
							return array($model['id'],$model['id']);
						}else{
							return array(-24,0);//删除失败，无法产生验证码		
						}
					}
					return array(-22,$model['id']);//没有过期，不需要再插入
				}else{
					//直接产生一个就行了
					$this->send_code($model['id'],NOW_TIME+$timeout, $mobile,true);
					return array($model['id'],$model['id']);
				}			
			}else{
				return array(-23,$model['id']);
			}
		}else{
			$data= array(
				'user_id'             	=> 0,
				'verify_type' 			=> 0,
				'verify_content'   		=> $mobile,
				'create_time'   		=> NOW_TIME,
				'verify_time'			=> 0,
				'verify_status'			=> 0,
				);
			$data['expired_time']=$data['create_time']+600;
			if($this->create($data)){
				$vid = $this->add();
				if($vid)
				{
					$this->send_code($vid,$data['expired_time'],$mobile,false);
				}
				return array($vid>0 ? $vid : 0,0);
			}
		}
	}
	
	/**
	 * Created by wujianke
	 * Date: 14-8-10
	 * 功能:验证指定手机号的验证码
	 * @param  $uid  	用户ID
	 * @param  $vid  	验证码信息ID
	 * @param  $mobile  验证内容  
	 * @param  $code  	验证码 
	 * @return int 状态码
	 */ 
	public function verify_code($mobile,$code)
	{
		$map = array();
		if($mobile>0 && $code>0){
			$map['verify_type'] = 0;
			$map['verify_content'] = $mobile;
			$model = $this->where($map)->field('id,user_id,verify_type,verify_content,verify_status,expired_time,create_time')->find();

			$modeltoken = array();
			$Token = D("UserVerifyToken");
			$modeltoken=$Token->get_model($model['id'],0);
			
			if(!is_array($modeltoken))
			{
				return -1;//无此验证消息
			}
			if(($modeltoken['expired_time']<NOW_TIME))
			{
				return -3;//已过期无法验证
			}
			elseif($modeltoken['token']!=$code)
			{
				return -5;//验证码错误
			}
			return 1;
		}
		else
		{
			return -10;//参数错误
		}
	}
	/**
	 * Created by wujianke
	 * Date: 14-8-10
	 * 功能:发送验证码，修改验证信息，生成验证Token
	 * @param  $vid  			验证信息ID
	 * @param  $expired_time  	验证码过期时间
	 * @param  $mobile  		接受验证码的手机号ID
	 * @param  $isneedupdate  	是否需要更新验证信息
	 */ 
	private function send_code($vid,$expired_time,$mobile,$isneedupdate=true){
		if($isneedupdate)
		{
			$updatedata = array(
				'id'              	=> $vid,
				'expired_time' 		=> $expired_time,
			);
			$this->save($updatedata);
		}

		//1、先产生token 
		$code=rand(100000,999999);
		$Token = D("UserVerifyToken");
		$r=$Token->add_model($vid,0,$code,$expired_time);
		if($r>0)
		{
			//2、发短信	
            $code = $code.'（悟净手机验证码，请完成验证），如非本人操作，请忽略本短信。';
            send_sms($mobile,$code);
		}
	}
	
	/**
	 * Created by wujianke
	 * Date: 14-8-10
	 * 功能:验证时间是否过期
	 */ 
  	public function check_timeout($mobile,$code){
		$map = array();
		if($mobile>0 && $code>0){
       		$map['verify_type'] = 0;
			$map['verify_content'] = $mobile;
			$model = $this->where($map)->field('id,user_id,verify_type,verify_content,verify_status,expired_time')->find();
           	if(is_array($model)&&isset($model['expired_time'])){
         		if($model['expired_time']< NOW_TIME){
               		return -3;//已过期无法验证
            	}
          	}
                        
         	$maptoken = array();
			$Token = D("UserVerifyToken");
			$modeltoken=$Token->get_model($model['id'],0);
			if(!is_array($modeltoken))
			{
				return -1;//无此验证消息
			}
			if($modeltoken['expired_time']<NOW_TIME)
			{
				//删除Token记录
				$Token->del_model($model['id'],0); 
				return -3;//已过期无法验证
			}
			elseif($modeltoken['token']!=$code)
			{
				return -5;//验证码错误
			}
         	return 1;
                        
      	}
	}

	/**
	 * Created by wujianke
	 * Date: 14-8-10
	 * 功能:清理过期数据,包括UserVerify和UserVerifyToken
	 */ 
	public function clear_overdue()
	{
		$map=array();
		$map['verify_status']  = 1;
		$map['expired_time']  = array('lt',NOW_TIME);//小于
		$this->where($map)->delete();
		$Token = D("UserVerifyToken");
		$Token->clear_overdue();
	}
        
	/**
	 * Created by wujianke
	 * Date: 14-8-10
	 * 功能:根据验证ID和验证内容获取验证实体
	 * @param  $vid  	验证码信息ID
	 * @param  $mobile  验证内容   
	 * @return array()数据实体  -1 参数错误 
	 */ 
	public function get_model($vid,$mobile)
	{
		$map = array();
		if($vid>0 && $mobile>0){ //通过用户名获取
			$map['id'] = $vid;
			$map['verify_content'] = $mobile;	
		}
		else
		{
			return -1;
		}
		$model = $this->where($map)->field('id,user_id,expired_time')->find();
		if(is_array($model)){
			return array($model['id'], $model['user_id'], $model['expired_time']);
		} else {
			return -1; //用户不存在或被禁用
		}
	}
	

	/* 所有MODEL公共方法 */
	public function get_one($where,$field='')
	{
		return $this->field($field)->where($where)->find();
	}

	public function get_all($where,$field='')
	{
		return $this->field($field)->where($where)->select();
	}

	public function get_count($where,$field='')
	{
		return $this->field($field)->where($where)->count();
	}
        
	public function update_status($where,$data){
   		return $this->where($where)->save($data);
 	}
}