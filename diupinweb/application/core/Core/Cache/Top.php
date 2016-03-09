<?php
class Core_Cache_Top extends Lamb_Cache_Abstract
{
	/**
	 * @var Memcached 
	 */
	protected $cache = null;
	
		
	public function __construct(array $connectOptions = null, $cacheTime = null, $identity = null)
	{
		parent::__construct($cacheTime, $identity);
		
		$this->cache = Alibaba::cache($connectOptions);	
	}
	
	public function flush()
	{
		return $this->cache->delete($this->getIdentity());
	}
	
	public function flushAll()
	{
		
	}
	
	public function isCached()
	{
		return $this->read() !== false;
	}
	
	public function read()
	{
		return unserialize($this->cache->get($this->getIdentity()));
	}
	
	public function write($data)
	{
		return $this->cache->set($this->getIdentity(), serialize($data), $this->getCacheTime() + time());
	}
}
