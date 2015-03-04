<?php
class send_phone_sms{
	private $conf = array(
		'yimei' => array(
			'url' => 'http://sdk4report.eucp.b2m.cn:8080/sdk/SDKService?wsdl',
			'serialNumber'		=> '6SDK-EMY-6688-KEYNM',
			'password'			=> '430352',
		
			'sessionKey'		=> '884958',
			'connectTimeOut'	=> '2', 
			'readTimeOut'		=> '5', 
		)
	);
	private $mobile = '';
	private $code = '';

	public function __construct($mobile,$code)
	{
		$this->mobile = $mobile;
		$this->code = $code;
	}

	public function send_phone_yimei()
	{
		$mobile   = $this->mobile;
		$code     = $this->code;
		$gwUrl = $this->conf['yimei']['url'];

		$serialNumber = $this->conf['yimei']['serialNumber'];

		$password = $this->conf['yimei']['password'];

		$sessionKey = $this->conf['yimei']['sessionKey'];

		$connectTimeOut = $this->conf['yimei']['connectTimeOut'];

		$readTimeOut = $this->conf['yimei']['readTimeOut'];

		vendor('Yimei.nusoaplib.nusoap');
		vendor('Yimei.include.Client');
		
		$client = new Client($gwUrl,$serialNumber,$password,$sessionKey,false,false,false,false,$connectTimeOut,$readTimeOut);
		
		$client->setOutgoingEncoding("UTF-8");
		//$client->login($sessionKey);
		$statusCode = $client->sendSMS(array($mobile),$code);
		if($statusCode != null && $statusCode == 0){
			M('MemberSmsLog')->add(array(
				'channel' => '亿美',
				'mobile'  => $this->mobile,
				'content' => $this->code,
				'send_time' => time(),
				'send_status' => 2,
				'return_msg' => $statusCode
			));
			return array(true);
		}else{
			M('MemberSmsLog')->add(array(
				'channel' => '亿美',
				'mobile'  => $this->mobile,
				'content' => $this->code,
				'send_time' => time(),
				'send_status' => -1,
				'return_msg' => $statusCode
			));
			return array(false,$statusCode);
		}
	}
}
