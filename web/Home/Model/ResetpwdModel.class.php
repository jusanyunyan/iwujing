<?php
/**
 * Created by wujianke
 * Date: 2014-08-18
 * 找回密码对相关ModelVerify
 */ 
namespace Home\Model;
use Think\Model;

class ResetpwdModel extends Model{
	
	/**
	 * Created by wujianke
	 * Date: 14-8-18
	 * 功能:给固定手机发送验证码
	 * @param  $uid  	发送验证码的用户ID
	 * @param  $mobile  接受验证码的手机号   
	 * @return array(状态值，生成的验证码信息ID)
	 */ 
	public function send_verify($uid, $mobile)
	{

		$timeout=600;//5分钟有效期
		$Token = D("UserVerifyToken");
		
		//1、检查是否存在相同的verify_content
		$map= array();
		$map['verify_type'] = 0;
		$map['verify_content'] = $mobile;
		$model = M('UserVerify')->where($map)->field('id,user_id,verify_type,verify_status,expired_time')->find();
		if(!$model){
           	$data['verify_status'] = 1;
        	$data['create_time'] = NOW_TIME;
           	$data['expired_time'] = NOW_TIME;
        	$data['user_id'] = $uid;
          	$add_data = array_merge($map,$data);
       		M('UserVerify')->add($add_data);
       	}
    	$model = M('UserVerify')->where($map)->field('id,user_id,verify_type,verify_status,expired_time')->find();
    	if(is_array($model))
		{
			//2、存在且未验证的
			if($model['user_id']!=$uid)
			{
				return array(-21,0);//存储的user_id与当前操作的Uid不一致
			} 
			else {
				//2.1检查是否存在Token
				$token=$Token->get_model($model['id'],1);//$token_type = 1;//0=注册验证,1=忘记密码 
				//2.2存在Token,查看是否过期
				if(is_array($token))
				{
					//已过期1分钟的，删除当前记录，再产生一个
					if(($token['expired_time']-$timeout+60)<NOW_TIME)
					{
						//修改UserVerify的过期时间
						$r=$Token->del_model($model['id'],1);//$token_type = 1;//0=注册验证,1=忘记密码 
						if($r !== false)
						{
							//删除成功，再产生一个
							$this->send_code($model['id'],NOW_TIME+$timeout, $mobile,true);
							return array($model['id'],$model['id']);
						}
						else
						{
							return array(-24,0);//删除失败，无法产生验证码		
						}
					}
					return array(-22,$model['id']);//没有过期，不需要在插入
				}
				//2.3不存在Token,且未验证通过，那就在发一次吧 
				else
				{
                    $Token = D("UserVerifyToken");
                    $Token->clear_overdue();
					//直接产生一个就行了

					$this->send_code($model['id'],NOW_TIME+$timeout, $mobile,true);
					return array($model['id'],$model['id']);
				}
			}
		}
	}
	/**
	 * Created by wujianke
	 * Date: 2014-08-18
	 * 功能:发送验证码，修改验证信息，生成验证Token
	 * @param  $vid  			验证信息ID
	 * @param  $expired_time  	验证码过期时间
	 * @param  $mobile  		接受验证码的手机号ID
	 * @param  $isneedupdate  	首次需要更新验证信息的过期时间
	 */ 
	private function send_code($vid,$expired_time,$mobile,$isneedupdate=true)
	{
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
		$token_type = 1;//0=注册验证,1=忘记密码 
		$r=$Token->add_model($vid,$token_type, $code,$expired_time);
		if($r>0)
		{
			//2、发短信
            $code = $code.'（悟净手机验证码，请完成验证），如非本人操作，请忽略本短信。';
			send_sms($mobile,$code);
		}
	}
	/**
	 * Created by wujianke
	 * Date: 2014-08-18
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
	/**
	 * Created by wujianke
	 * Date: 2014-08-18
	 * 功能:验证指定手机号的验证码
	 * @param  $uid  	用户ID
	 * @param  $vid  	验证码信息ID
	 * @param  $mobile  验证内容  
	 * @param  $code  	验证码 
	 * @return int 状态码
	 */ 
	public function verify_code($uid,$vid,$mobile,$code)
	{
		$map = array();
		if($uid>0 && $vid>0 && $code>0)
		{
			$map['id'] = $vid;
			$map['user_id'] = $uid;
			$map['verify_type'] = 0;//验证类型0=手机,1=邮件
			$map['verify_content'] = $mobile;
			$model = $this->where($map)->field('id,user_id,verify_type,verify_content,verify_status,expired_time')->find();
			if(!is_array($model))
			{
				return -1;//无法验证
			}
			$maptoken = array();
			$Token = D("UserVerifyToken");
			$modeltoken=$Token->get_model($vid,1);//找回密码是1
			if(!is_array($modeltoken))
			{
				return -1;//无此验证消息
			}
			if($modeltoken['expired_time']<NOW_TIME)
			{
				//删除Token记录
				$Token->del_model($vid,2); 
				return -3;//已过期无法验证
			}
			elseif($modeltoken['token']!=$code)
			{
				return -5;//验证码错误
			}
			else
			{
				//修改UserVerify为已通过
				$updatedata = array(
					'id'              	=> $vid,
					'verify_time' 		=> NOW_TIME,
					'verify_status'   	=> 1,
					);
				$this->save($updatedata);
				//删除Token记录
				$Token->del_model($vid,2);
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
	 * Date: 14-4-11
	 * 功能:清理过期数据,包括UserVerify和UserVerifyToken
	 */ 
	public function clear_overdue()
	{
		$map=array();
		$map['verify_status']  = 0;
		$map['expired_time']  = array('lt',NOW_TIME);//小于
		$this->where($map)->delete();
		$Token = D("UserVerifyToken");
		$Token->clear_overdue();
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
}
