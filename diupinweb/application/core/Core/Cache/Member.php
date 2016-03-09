<?php
/**
 * 无缓存版
 */
class Core_Cache_Member
{
	/**
	 * 
	 */
	protected $mSiteCfg;
	
	/**
	 * 
	 */
	protected $mDb;
	
	/**
	 * 
	 */
	const IS_USE_CACHE = 0;
	
	/**
	 * @var array
	 * 要缓存的列
	 */
	protected static $sColumns = array(
		'id' => 1, 'nickname' => 1, 'avatar' => 1, 'sex' => 1,
		'sign' => 1, 'regtime' => 1, 'phone' => 1, 'password' => 1, 
		'salt' => 1, 'status' => 1, 'qq_openid' => 1, 'wechat_openid' => 1, 'weibo_openid' => 1, 'bind_status' => 1
	);
	
	public function __construct()
	{
		$this->mSiteCfg = Lamb_Registry::get(CONFIG);
		$app = Lamb_App::getGlobalApp();
		$this->mDb = $app->getDb();
	}
	
	/**
	 * 获取多个用户的数据
	 * 
	 * @param string | array $uids 多个用户的uid，如果是字符串，以英文逗号隔开
	 * @param string | array $fields 指定返回的列，如果是字符串，以英文逗号隔开
	 * 
	 * @return array(
	 * 	用户1的信息，array，如果没有则为空
	 * 	用户2的信息，array
	 * 	....
	 * )
	 */
	public function get($uids, $fields, $isNotExistsFillNull = true)
	{
		if (is_string($fields)) {
			$fields = explode(',', $fields);
		}
		if (is_string($uids)) {
			$uids = explode(',', $uids);
		}
					
		$result = array();
		
		$noCacheUsers = array();
		if (self::IS_USE_CACHE) {
			foreach ($uids as $index => $uid) {
				$cache = $this->getCache($uid);
				if ($cache->isCached()) {
					$data = $cache->read();
					//只返回指定列
					$newdata = array();				
					
					foreach ($fields as $_fields) {
						if (array_key_exists($_fields, $data)) {
							$newdata[$_fields] = $data[$_fields];
						}	
					}
					$result[$index] = $newdata;
				} else {
					$noCacheUsers[$index] = $uid;
				}
			}
		} else {
			foreach ($uids as $index => $uid) {
				$noCacheUsers[$index] = $uid;
			}
		}	
		
		if (count($noCacheUsers)){
			$allFields = implode(',', array_keys(self::$sColumns));		
			$noCacheUids = implode(',', array_values($noCacheUsers));
			$sql = "select {$allFields} from member as a where id in ({$noCacheUids})";	
			$noCacheData = $this->mDb->query($sql)->toArray();
			
			foreach ($noCacheUsers as $index => $uid) {
				$isFind = false;
				foreach ($noCacheData as $i => $_data) {
					if ($_data['id'] == $uid) {
						$isFind = true;
						if (self::IS_USE_CACHE) {
							$cache = $this->getCache($uid);
							$cache->write($noCacheData[$i]);
						}
						//只返回指定列						
						$newdata = array();
						foreach ($fields as $_fields) {
							$newdata[$_fields] = $_data[$_fields];	
						}
						$result[$index] = $newdata;							
						unset($noCacheData[$i]);
						break;
					}
				}
				
				if (!$isFind && $isNotExistsFillNull) {
					$result[$index] = null;
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * 通过手机号获得用户的uid
	 * 
	 * @param int $phone 手机号
	 * @return int uid | 0
	 */
	public function phone2uid($phone)
	{
		$result = $this->mDb->quickPrepare('call getUidByPhone(:phone)', array(
			':phone' => array($phone, PDO::PARAM_STR, 15)
		))->toArray();
		
		if (!count($result)) {
			return 0;
		}

		return $result[0]['id'];
	}	

	/**
	 * 获取存放用户的缓存
	 */
	public function getCache($uid) 
	{
		return Core_Cache_Factory::getCache()->setIdentity("USER_{$uid}")
					->setCacheTime($this->mSiteCfg['user_cache_expire']);	
	}
	
	/**
	 * 清除用户缓存
	 */
	public static function clear($uid)
	{
		$obj = new self;
		$cache = $obj->getCache($uid);
		if ($cache->isCached()) {
			$cache->flush();
		}
	}	
	
	protected function d($str)
	{
		Lamb_Debuger::debug($str);
	}
	
}