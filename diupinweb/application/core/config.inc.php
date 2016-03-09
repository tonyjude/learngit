<?php
define('DATA_PATH', APP_PATH . 'data/');
define('CACHE_PATH', DATA_PATH . 'cache/');
define('CONFIG', 'site_config');

return array(
	'controllor_path' => APP_PATH . 'controllors/',
	'view_path' => APP_PATH . 'views/',
	'view_runtime_path' => DATA_PATH . '~runtime/',
	'theme_path' => ROOT . 'themes/',
	'template' => 'default',
	
	'db_cfg' => array(
		'dsn' => 'mysql:host=rd35o89yp1.mysql.rds.aliyuncs.com;dbname=diupin;charset=utf8mb4',
		'username' => 'r4g89a1s0i',
		'password' => 'ra0461b9'
	),
	
	'sms_verify' => array(
		'key' => 'e3m0f3aen9b641a5nj3v/m0lp=',
		'expire' => 6000
	),
	
	'user_cache_expire' => 86400,
	'oauth_cfg' => array (
	    'qq' => array(
			'app_id' => '101022282',
			'app_key' => '591bab5a390443da52074272acba59a3'
		)
	),
	
	'site' => array (
    	'name' => '丢品网',
    	'host' => 'http://www.diupin.com',
    	'root' => '/',
    	'img_host' => 'http://www.diupin.com/',
    	'keywords' => '图片插话,生活实用,创意速递,温暖故事,创意生活,视觉摄影,清新生活,diupin,duipin,丢品',
    	'description' => '丢品网(www.diupin.com)是一个收集奇文趣事,创意图片,温暖故事,生活实用的时尚小站。你可以在这里阅读、分享、交流,找到与你志趣相投的朋友,成为热爱生活的人。',
    	'mode' => 1,
    	'nav' => array (
    	
	    )
  	)	
	
	
);
