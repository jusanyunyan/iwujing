<?php
return array(
	/* 模块相关配置 */
	'AUTOLOAD_NAMESPACE' => array('Addons' => ONETHINK_ADDON_PATH), //扩展模块列表
	'DEFAULT_MODULE'     => 'Home',
	'MODULE_DENY_LIST'   => array('Common','User'),
	'MODULE_ALLOW_LIST'  => array('Home','Admin'),

	/* 系统数据加密设置 */
	'DATA_AUTH_KEY' => '@X$Eo%80KSc6p;zgP.Hs5hn[v&e^ZW=tLx#(G1Q_', //默认数据加密KEY

	/* 调试配置 */
 	//'SHOW_PAGE_TRACE' => true,

	/* 用户相关设置 */
	'USER_MAX_CACHE'     => 1000, //最大缓存用户数
	'USER_ADMINISTRATOR' => 1, //管理员用户ID

	/* URL配置 */
	'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
	'URL_MODEL'            => 3, //URL模式
	'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
	'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符

	/* 全局过滤配置 */
	'DEFAULT_FILTER' => '', //全局过滤函数

	/* 数据库配置 */
	'DB_TYPE'   => 'mysqli', // 数据库类型
	'DB_HOST'   => 'localhost', // 服务器地址
	'DB_NAME'   => 'alidata', // 数据库名
	'DB_USER'   => 'www', // 用户名
	'DB_PWD'    => 'httxbj600677',  // 密码
	'DB_PORT'   => '3306', // 端口
	'DB_PREFIX' => '', // 数据库表前缀
	
	
	
	'WEB_SITE_TITLE'=>"航天迅驰官网 - 立式、车载空气净化器",
	'WEB_SITE_KEYWORDS'=>"悟净,空气净化器,wujing,航天迅驰,家用空气净化器,HAT,最好的空气净化器",
	'WEB_SITE_DESCRIPTION'=>"悟净空气净化器",
	'URL_CASE_INSENSITIVE' =>false,  //URL参数大小写区分设定
	
	
	/* 支付设置 */
	'payment' => array(
		/*
		'unionpay' => array(
			'key' => 'RJ34892TUFGP2JT2039PJR523P5JR234H5B4R',
			'partner' => '898111157221816'
			),
		'unionpay2' => array(
			'key' => '398RU923RUOJ2O3LRJ23LRJ2L3JR4FTRH42U',
			'partner' => '898111157221806'
			),
		*/
		'unionpay' => array(
			'key' => '88888888',
			'partner' => '898111157221816'
			),
		'unionpay2' => array(
			'key' => '88888888',
			'partner' => '898111157221806'
			),
		'alipay' => array(
			// 收款账号邮箱
			'email' => 'jinghuaqi@httxbj.net',
			// 加密key，开通支付宝账户后给予
			'key' => 'vav77kieb927am3oqsidsrf7jqe26hwd',
			// 合作者ID，支付宝有该配置，开通易宝账户后给予
			'partner' => '2088611203369801',
			),
		),
	'THINK_EMAIL' => array(  
		'SMTP_SSL'		=> 1, //是否需要SSL协议  
		'SMTP_HOST'		=> 'smtp.qq.com', //SMTP服务器  
		'SMTP_PORT'		=> '465', //SMTP服务器端口  
		'SMTP_USER'		=> 'test@qq.com', //SMTP服务器用户名
		'SMTP_PASS'		=> 'TEST', //SMTP服务器密码
		'SMTP_MAIL_TYPE'=>'HTML',	 //发送邮件类型:HTML,TXT(注意都是大写)
		'SMTP_TIME_OUT'	=>30,	 //超时时间
		'SMTP_AUTH'		=>true,	 //邮箱验证(一般都要开启)*/
		'FROM_EMAIL'	=> 'TEST@qq.com', //发件人EMAIL
		'FROM_NAME'		 => '吴建科', //发件人名称  
		),
	'SERVICE'=>array(
		'secrectKey' => 'UYy9WCnLbQ98ZZ_paiao9-RahGtXhKeTuZBjByKv',
		),		
);