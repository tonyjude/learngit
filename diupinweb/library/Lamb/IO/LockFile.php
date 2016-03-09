<?php
/**
 * Lamb Framework
 * @author 小羊
 * @package Lamb_IO
 */
class Lamb_IO_LockFile extends Lamb_IO_File
{
	/**
	 * @var boolean Whether release lock when this destruct
	 */
	protected $_mAutoUnlock = false;
	
	/**
	 * @override
	 */
	public function __construct($path = '', $mode = '')
	{
		parent::__construct($path, $mode);	
	}
	
	/**
	 * @override
	 */
	public function __destruct()
	{
		if ($this->_mAutoUnlock){
			$this->unlock();
		}
	}
	
	/**
	 * @param boolean $lock
	 * @return boolen | Lamb_IO_LockFile
	 */
	public function setOrGetAutoUnlock($lock = null)
	{
		if (null === $lock) {
			return $this->_mAutoUnlock;
		}
		$this->_mAutoUnlock = (boolean)$lock;
		return $this;
	}
	
	/**
	 * @override
	 */
	public function open($path, $mode)
	{
		if (strpos($mode, '+') === false){
			$mode .= '+';
		}
		parent::open($path, $mode);
	}
	
	/**
	 * @param int $nLockType
	 * @return boolean
	 */
	public function lock($nLockType = LOCK_EX)
	{
		return flock($this->_mFileHandle, $nLockType);
	}
	
	/**
	 * @return boolean
	 */
	public function unlock()
	{
		return flock($this->_mFileHandle, LOCK_UN);
	}
}