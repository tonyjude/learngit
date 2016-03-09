<?php
/**
 * Lamb Framework
 * @author 小羊
 * @package Lamb_Looper_Count
 */
interface Lamb_Looper_Count_Interface extends Lamb_Looper_Interface
{
	/**
	 * @param int $count
	 * @return Lamb_Looper_Count_Interface
	 */
	public function setCount($count);
	
	/**
	 * @param Lamb_Looper_Count_HandlerInterface | PHP callback $handle
	 * @return Lamb_Looper_Count_Interface
	 */
	public function setHandler($handle);
}