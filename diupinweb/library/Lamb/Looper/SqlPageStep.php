<?php
/**
 * Lamb Framework
 * @author 小羊
 * @package Lamb_Looper_Counter
 */
class Lamb_Looper_SqlPageStep extends Lamb_Looper_SqlPage
{	
	protected $mCurrPage = 1;
	
	public function __construct($pagesize)
	{
		parent::__construct($pagesize);
	}
	
	/**
	 * @param int $page
	 * @return int | Lamb_Looper_SqlPageHttp
	 */
	public function setOrGetCurrentPage($page = null)
	{
		if (null === $page) {
			return $this->mCurrPage;
		}
		$this->mCurrPage = max((int)$page, 1);
		return $this;
	}
	
	/**
	 * @override
	 */
	public function run()
	{
		if ($handler = $this->getHandler()) { 
			$page = $this->setOrGetCurrentPage();
			if ($handler instanceof Lamb_Looper_Count_HandlerInterface) {
				if (!$handler->handle($page, $this->getHandlerExternalParam())) {
					return false;
				}
			} else if (!call_user_func($handler, $page, $this->getHandlerExternalParam())){
				return false;
			}
		}
		return true;		
	}
}