<?php
/**
 * Lamb Framework
 * @author 小羊
 * @package Lamb_Cache
 */
interface Lamb_Cache_Interface
{
	/** 
	 * 设置缓存时间 
	 *
	 * @param int $second
	 * @return Lamb_Cache_Interface
	 */
	public function setCacheTime($second);
	
	/**
	 * @return int
	 */
	public function getCacheTime();
	
	/**
	 * 设置缓存的标识
	 * 如：文件缓存的标识是路径
	 * 内存缓存是键值
	 *
	 * @param string | int $identity
	 * @return Lamb_Cache_Interface
	 */
	public function setIdentity($identity);
	
	/** 
	 * Get the cache's identity
	 *
	 * @return string | int
	 */
	public function getIdentity();
	
	/**
	 * Read data from cache
	 *
	 * @return mixed 如果为null则为缓存还未创建活已经过期
	 */
	public function read();
	
	/**
	 * Write data to cache
	 *
	 * @return boolean
	 */
	public function write($data);
	
	/**
	 * Flush the cache
	 *
	 * @reutnr boolean is success?
	 */
	public function flush();
	
	/** 
	 * Retrieve the data whether in cached
	 *
	 * @return boolean
	 */
	public function isCached();
}