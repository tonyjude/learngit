<?php
/**
 * Lamb Framework
 * @author 小羊
 * @package Lamb_Looper
 */
class Lamb_Looper_SqlPage extends Lamb_Looper_Count
{
	/**
	 * @var int
	 */
	protected $mPagesize = 0;
	
	/**
	 * @var Lamb_Db_CallbackHandler
	 */
	protected $mDbCallbackHandler = null;
	
	/** 
	 * @param int $pagesize
	 */
	public function __construct($pagesize)
	{
		parent::__construct();
		$this->mDbCallbackHandler = new Lamb_Db_CallbackHandler();
		$this->setOrGetPageSize($pagesize);
	}
	
	/**
	 * @param int $pagesize
	 * @return int | Lamb_Looper_SqlPage
	 */
	public function setOrGetPageSize($pagesize = null)
	{
		if (null === $pagesize) {
			return $this->mPagesize;
		}
		$this->mPagesize = (int)$pagesize;
		$this->setHandlerExternalParam($this->mPagesize);
		return $this;
	}
	
	 /**
	  * @param PHP callback | Lamb_Db_Callback_Interface $callback
	  * @return Lamb_Looper_SqlPage
	  * @thorws Lamb_Exception
	  */	
	public function setDbCallback($callback)
	{
		$this->mDbCallbackHandler->setDbCallback($callback);
		return $this;
	}
	
	/**
	 * @return Lamb_Db_Abstract
	 */
	public function getDb()
	{
		return $this->mDbCallbackHandler->getDb();
	}
	
	/**
	 * @param string $sql
	 * @return Lamb_Looper_SqlPage
	 */
	public function setCountBySql($sql, &$count = null)
	{
		$count = $this->getDb()->getRowCount($sql);
		$this->setCount(ceil($count / $this->setOrGetPagesize()));
		unset($count);
		return $this;
	}
}