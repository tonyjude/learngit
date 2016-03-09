<?php
/**
 * Lamb Framework
 * @auth lamb
 * @time 2015-09-20 11:57
 * redis缓存
 */
class Lamb_Cache_Redis extends Lamb_Cache_Abstract
{
	protected $_redis = null;
	
	protected $_is_connected = false;
	
	/**
	 * 构造函数
	 *
	 * @param array $conn_opt 连接选项 = array(
	 *		'host' => 主机头，默认为localhost,
	 *		'port' => 端口号，必填
	 *		timeout => 超时时间，默认为0，不限制
	 *		auth => 登录密码，如果需要的话，填写该值
	 *		is_pconnect => 是否为永久链接，默认为true
	 * )
	 *
	 *
	 * @param int $cache_time 缓存时间
	 * @param int $identity 缓存名
	 */
	public function __construct($conn_opt = null, $cache_time = null, $identity = null)
	{
		parent::__construct($cache_time, $identity);
		$this->_redis = new Redis;
		
		if ($conn_opt !== null) {
			$this->connect($conn_opt);
		}
	}
	
	/**
	 * 构造函数
	 *
	 * @param array $conn_opt 连接选项 = array(
	 *		'host' => 主机头，默认为localhost,
	 *		'port' => 端口号，必填
	 *		timeout => 超时时间，默认为0，不限制
	 *		auth => 登录密码，如果需要的话，填写该值	 
	 *		is_pconnect => 是否为永久链接，默认为true
	 * )
	 *
	 * @return boolean
	 */
	public function connect($conn_opt)
	{
		$opt = array('timeout' => 15, 'host' => '127.0.0.1', 'port'=> 6379, 'auth' => '', 'is_pconnect' => true);
		Lamb_Utils::setOptions($opt, $conn_opt);
		$funcname = 'pconnect';
		
		if (!$opt['is_pconnect']) {
			$funcname = 'connect';
		}
		
		if ($this->_redis->$funcname($opt['host'], $opt['port'], $opt['timeout'])) {
			if ($opt['auth'] && $this->_redis->auth($opt['auth'])) {
				$this->_is_connected = true;
			} 
			
			if (!$opt['auth']) {
				$this->_is_connected = true;
			}
		}
		
		return $this->_is_connected;
	}
	
	/**
	 * 断开当前连接
	 *
	 * @return boolean
	 */
	public function close()
	{
		if ($this->isConnected()) {
			$this->_redis->quit();
			return true;
		}
		
		return false;
	}
	
	/**
	 * 清空缓存
	 */
	public function flush()
	{
		if ($this->isConnected()) {
			return $this->_redis->del($this->getIdentity());
		}
		return false;
	}
	
	/**
	 * 清空所有缓存
	 */
	public function flushAll()
	{
		if ($this->isConnected()) {
			return $this->_redis->flushAll();
		}
		return false;	
	}
	
	/**
	 * 返回缓存是否在有效期内
	 *
	 * @return boolean
	 */
	public function isCached()
	{
		if ($this->isConnected() && $this->getCacheTime()>0 && $this->_redis->ttl($this->getIdentity()) > 0 && $this->_redis->get($this->getIdentity()) !== false) {
			return true;
		}
		
		return false;
	}
	

	/**
	 * Lamb_Cache_Interface implemention
	 */	
	public function write($data)
	{
		if ($this->isConnected()) {	
			if (is_string($data)) {
				$data = ':$1$:' . $data;
			} else {
				$data = ':$2$:' . json_encode($data);
			}
			
			return $this->_redis->setex($this->getIdentity(), $this->getCacheTime(), $data);
		}
		
		return false;
	}
	
	
	/**
	 * Lamb_Cache_Interface implemention
	 */	
	public function read()
	{
		if ($this->isConnected()) {
			$ret = $this->_redis->get($this->getIdentity());
			
			if ($ret === false) {
				return null;
			}
			$header = substr($ret, 0, 5);
			$ret = substr($ret, 5);
			
			//如果是json字符串
			if ($header == ':$2$:') {
				$ret = json_decode($ret, true);
			}
			return $ret;
		}
		return null;
	}
	
	/**
	 * 获取原生的redis对象
	 */
	public function getRawRedis()
	{
		return $this->_redis;
	}
	
	/**
	 * 返回当前是否连接
	 */
	public function isConnected()
	{
		return $this->_is_connected;
	}
}