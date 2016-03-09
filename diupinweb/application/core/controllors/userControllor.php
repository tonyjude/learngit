<?php
class userControllor extends Core_Controllor
{
	protected $dUserId;
	protected $dUsername;
	protected $dModel;
	public function __construct()
	{
		parent::__construct();
//		$this->dModel = new Diupin_Model_User;
	}

	public function getControllorName()
	{
		return 'user';
	}

	/**
	 *
	 *  登陆
	 *  req_data:
	 *  	p1 string
	 * 		p2 string
	 * 		login_type int 0-手机号密码登陆（默认）
	 * 			0-phone
	 * 				p1=>phone,p2=>password
	 * 			1-qq
	 * 				p1=>access_token,p2=>openid
	 * 			2-微信
	 * 				p1=>access_token,p2=>openid
	 *  		3-微博
	 * 				p2=>access_token,p2=>uid
	 * 		device_id string
	 * 	res_data:
	 * 		's' : 0  系统错误
	 * 			  1 成功
	 * 			 -3 账号或密码错误
	 * 		'd' : {
	 * 			'key' : string 客户端session_key,
	 *			'id' : 用户的ID,
	 *			'sex' : int性别 1-男 2-女,
	 *			'nickname' : string 用户的昵称,
	 *			'avatar' : string 头像的HTTP地址，如果没有则为空
	 * 			'sign' : string
	 * 			'bind_status' : int账号绑定状态，二进制形式：0000：微博|微信|qq|手机号
	 * 		}
	 */

	/**
	 * 默认（手机）登录
	 */
	public function phone_loginAction()
	{
		if (!$this->mRequest->isPost()) {
			$this->showResults(0);
		}
		$p1 = trim($this->mRequest->getPost('p1'));
		$p2 = trim($this->mRequest->getPost('p2'));
		$db = $this->mApp->getDb();
		$isNewUser = 0;
		if (!Core_Utils::isPhone($p1) || empty($p2)) {
			$this->showResults(-3, null, "账号或密码错误");
		}
		$member = new Core_Cache_Member;
		$uid = $member->phone2uid($p1);
		if (!$uid) {
			$this->showResults(-3, null, "手机号尚未注册");
		}
		$userInfo = $member->get("{$uid}", "id,nickname,avatar,sex,password,salt,sign,bind_status");
		$userInfo = $userInfo[0];
		if (md5(md5($p2) . $userinfo['salt']) != $userinfo['password']) {
			$this->showResults(-3, null, '密码错误');
		}
		unset($userinfo['salt'], $userinfo['password']);
		//更新用户信息
		$this->mModel->serialize($userInfo)->updateLoginInfo($username);
	}

	/**
	 *QQ登录
	 *
	 * $login_type
	 *   1:qq
	 *   2:weibo
	 *   3:weixin
	 *
	 */
	public function qq_loginAction()
	{
		$qc = new QC();
		$qc->qq_login();
	}

	/**
	 * 微博登录
	 */
	public function weibo_loginAction()
	{
		//配置文件
		$oa = new SaeTOAuthV2(WB_AKEY, WB_SKEY);
		$code_url = $oa->getAuthorizeURL(WB_CALLBACK_URL);
		header("Location:$login_url");
	}

	/**
	 * 微信登陆
	 */
	public function weixin_loginAction()
	{
		$_userinfo = Lamb_Http::quickGet("https://api.weixin.qq.com/sns/userinfo?access_token={$p1}&openid={$p2}");
		$_userinfo = json_decode($_userinfo, TRUE);
		if (isset($_userinfo['errcode'])) {
			$this->showResults(0);
		}
		$userinfo = array('nickname' => $_userinfo['nickname'], 'avatar' => $_userinfo['headimgurl'], 'sex' => $_userinfo['sex']);
		$userinfo['nickname'] = $_userinfo['nickname'];
		$userinfo['avatar'] = $_userinfo['headimgurl'];
		$userinfo['sex'] = $_userinfo['sex'];
		$userinfo['bind_status'] = 4;
	}

	public function callback($type)
	{
		$type = trim($this->mRequest->type);
		if (!$type) {
			$this->showResults(0);
		} else {
			switch ($type) {
				case 'qq' :
					//access_token,openid 写入session中
					$qc = new QC();
					$qc->qq_callback();
					$qc->get_access_token();
					$qc->get_openid();
					$login_type = 1;
					//获取到 access_token ,open_id 利用其获取用户信息
					$userinfo = $this->getInfoByid($_SESSION['openid'], 1);
					if (count($userinfo)) {
						//数据库有授权的openid
						$userinfo = $userinfo[0];
					} else {
						//第一次授权
						$connect = new QC($_SESSION['access_token'], $_SESSION['openid']);
						$userinfo = $connect->get_user_info();
						$userinfo['nickname'] = $_userinfo['nickname'];
						$userinfo['avatar'] = $_userinfo['figureurl_2'];
						$userinfo['sex'] = $_userinfo['gender'] == '男' ? 1 : 2;
						$userinfo['bind_status'] = 2;
						//add
					}
					unset($userinfo['salt'], $userinfo['password']);
					//更新用户信息
//					$this->mModel->serialize($userInfo)->updateLoginInfo($username);
					break;
				case 'weibo' :
					//获取access_token/uid，应该有步callback步骤的
					if (isset($_REQUEST['code'])) {
						$keys = array();
						$keys['code'] = $_REQUEST['code'];
						$keys['redirect_uri'] = WB_CALLBACK_URL;
						try {
							$token = $oa->getAccessToken('code', $keys);
						} catch (OAuthException $e) {
							new OAuthException("授权失败");
						}
					}
					$connect = new SaeTClientV2(WB_AKEY, WB_SKEY, $token);
					//需要再写一个 getUid方法？
					$userinfo = $connect->show_user_by_id($uid);
					break;
				case 'weixin' :
					break;

				default :
					break;
			}

			//
		}

	}

	/**
	 * 通过openid,获取表中数据
	 */
	public function getInfoByid($open_id, $type)
	{
		$db = $this->mApp->getDb();
		$userinfo = $db->quickPrepare('call getUserinfoByOpenid(:openid,:type,:fields)', array(':openid' => array($open_id, PDO::PARAM_STR), ':type' => array($type, PDO::PARAM_INT), ':fields' => array('id,nickname,avatar,sex,sign,bind_status', PDO::PARAM_STR)))->toArray();
		return $userinfo;
	}

	/**
	 * 退出
	 */
	public function logoutAction()
	{
		$uid = $this->isLogin();
		$this->showResults(1);
		$this->mApp->getDb()->quickPrepare('call removeDeviceId(:uid)', array(':uid' => array($uid, PDO::PARAM_INT)));
	}

	/**
	 * 完善全部详细资料
	 * req_data = {
	 * 	'fields' : {
	 * 		'要修改的字段名' : '字段的值',
	 * 		...
	 * 	 }
	 * 	可支持修改的字段为：avatar,sex,nickname,sign
	 * }
	 * res_data = {
	 * 	's' : -3 fields参数非法
	 * }
	 */
	public function updateInfoAction()
	{
		$uid = $this->isLogin();

		if (!$this->mRequest->isPost()) {
			$this->showResults(0);
		}
		$fields = $this->mRequest->getPost('fields');
		try {
			$fields = json_decode(rawurldecode($fields), true);
		} catch (Exception $e) {
			$this->showResults(0);
		}
		if (!$fields) {
			$this->showResults(0);
		}

		static $allowEditFields = array('avatar' => 1, 'sex' => 0, 'nickname' => 1, 'sign' => 1);
		foreach ($fields as $key => $value) {
			if (!isset($allowEditFields[$key])) {
				$this->showResults(0);
			}
		}

		if (isset($fields['nickname'])) {
			$fields['nickname'] = trim($fields['nickname']);
			if (empty($fields['nickname'])) {
				$this->showResults(-3, null, '昵称不能为空');
			}
			if (Core_Utils::strLen($fields['nickname']) > 30) {
				$this->showResults(-3, null, '昵称不能超过10个文字');
			}
		}

		if (isset($fields['sign']) && Core_Utils::strLen($fields['sign']) > 100) {
			$this->showResults(-3, null, '签名过长');
		}

		if (isset($fields['sex']) && $fields['sex'] != 1 && $fields['sex'] != 2) {
			$this->showResults(-3, null, '性别错误');
		}

		if (isset($fields['avatar']) && !Lamb_Utils::isHttp($fields['avatar'])) {
			$this->showResults(-3, null, '头像错误');
		}

		$prepare = array();
		$newfields = array();
		$index = 1;
		foreach ($fields as $key => $val) {
			$newfields[$key] = '?';
			if ($allowEditFields[$key]) {
				$prepare[$index] = array($val, PDO::PARAM_STR);
			} else {
				$prepare[$index] = array($val, PDO::PARAM_INT);
			}
			$index++;
		}

		$prepare[$index] = array($uid, PDO::PARAM_INT);
		$table = new Lamb_Db_Table('member', Lamb_Db_Table::UPDATE_PREPARE_MODE);
		$table->setOrGetWhere('id=?')->set($newfields)->execute($prepare);

		if (isset($fields['nickname']) || isset($fields['avatar'])) {
			$this->mApp->getDb()->quickPrepare('call syncUserinfo(:uid,:nickname,:avatar)', array('uid' => array($uid, PDO::PARAM_INT), 'nickname' => array(isset($fields['nickname']) ? $fields['nickname'] : '', PDO::PARAM_STR), 'avatar' => array(isset($fields['avatar']) ? $fields['avatar'] : '', PDO::PARAM_STR)));
		}
		$this->showResults(1);
	}

}
?>
