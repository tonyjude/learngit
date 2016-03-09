<?php
return array(
  'send_url' => 'http://sdk999ws.eucp.b2m.cn:8080/sdkproxy/sendsms.action?cdkey=9SDK-EMY-0999-JFSMT&password=579339&phone={phone}&message={content}',
  'send_limit_num' => '5',
  'num_length' => '4',
  'cont_tpl' => 
  array (
    'verify' => '验证码：{code}，感谢您注册相约。请不要把验证码泄露给其他人。',
    'find_pass' => '验证码：{code}，相约帐号密码找回。请不要把验证码泄露给其他人。',
    'unbind_old' => '验证码：{code}，相约新帐号验证。请不要把验证码泄露给其他人。',
    'unbind_new' => '验证码：{code}，相约帐号解绑验证。请不要把验证码泄露给其他人。',
    'change_paypass' => '验证码：{code}，相约支付密码找回。请不要把验证码泄露给其他人。',
	'recruit' => '验证码：{code}，感谢您参加相约天使招募。请不要把验证码泄露给其他人。'
  ),
  
  //好友添加上限
  'max_friends_num' => '1000',
  'today_add_friend_num' => 
  array (
    'general' => '80',
    'vip' => '200',
  ),
  
  'vip_discount' => array(
  	'vip' => 0.9,
  	'year_vip' => 0.8
  ),
 
  //聊天价格设置
  'angel_price' => array(
  	'rentme_prices' => array(
				array(
					'price' => 1000,
					'advice' => 0
				),
				array(
					'price' => 3000,
					'advice' => 1
				),
				array(
					'price' => 5000,
					'advice' => 0
				),
				array(
					'price' => 8000,
					'advice' => 0
				),
				array(
					'price' => 10000,
					'advice' => 0
				)
		),
		'text_prices' => array(
				array(
					'price' => 1000,
					'advice' => 0
				),
				array(
					'price' => 2000,
					'advice' => 1
				),
				array(
					'price' => 3000,
					'advice' => 0
				),
				array(
					'price' => 4000,
					'advice' => 0
				),
				array(
					'price' => 5000,
					'advice' => 0
				)
			),
			
		//语音通话
		'voice_prices' => array(
			array(
				'price' => 2000,
				'advice' => 0
			),
			array(
				'price' => 3000,
				'advice' => 0
			),
			array(
				'price' => 4000,
				'advice' => 1
			),
			array(
				'price' => 5000,
				'advice' => 0
			),
			array(
				'price' => 6000,
				'advice' => 0
			)
		),
			
		//视频通话
		'video_prices' => array(
			array(
				'price' => 3000,
				'advice' => 0
			),
			array(
				'price' => 4000,
				'advice' => 0
			),
			array(
				'price' => 5000,
				'advice' => 0
			),
			array(
				'price' => 6000,
				'advice' => 1
			),
			array(
				'price' => 7000,
				'advice' => 0
			)
		)
  ),
  
   'vip_pay_options' => array (
	    0 => array (
	      'money' => '12',
	      'time' => 2592000,
	      'pid' => 'vip1new',
	    ),
	    
	    1 =>  array (
	      'money' => '30',
	      'time' => 7776000,
	      'pid' => 'vip3new',
	    ),
	    
	    2 => array (
	      'money' => '60',
	      'time' => 15552000,
	      'pid' => 'vip6new',
	    ),
	    
	    3 => array (
	      'money' => '108',
	      'time' => 31536000,
	      'pid' => 'vip12new',
	    )
  ), 
  
);
