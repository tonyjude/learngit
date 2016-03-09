<?php
class Core_Cache_Factory
{
	/**
	 * @param int $type
	 * @param array $options
	 * @return Lamb_Cache_Interface
	 */
	public static function getCache()
	{
		$cfg = Lamb_Registry::get(CONFIG);
		return new Core_Cache_Top($cfg['cache_cfg']);
	}
}