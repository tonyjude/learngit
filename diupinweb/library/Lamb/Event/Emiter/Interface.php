<?php
/**
 * Lamb Framework
 * @author 小羊
 * @package Lamb_Event_Emiter
 */
interface Lamb_Event_Emiter_Interface
{
	/**
	 * @param int | string  $event
	 * @param PHP callback $listener
	 * @param mixed $params
	 * @return Lamb_Event_Emiter_Interface
	 */
	public function addEventListener($event, $listener, $params);
	

	/**
	 * @param int | string  $event
	 * @param PHP callback $listener
	 * @param mixed $params
	 * @return Lamb_Event_Emiter_Interface
	 */
	public function removeEventListener($event, $listener);

	/**
	 * @param int | string $event
	 * @return Lamb_Event_Emiter_Interface
	 */	
	public function removeAllListeners($event);

	/**
	 * @param int | string  $event
	 * @param PHP callback $listener
	 * @param mixed $params
	 * @return Lamb_Event_Emiter_Interface
	 */	
	public function runOnceListener($event, $listener, $params);
	
	/**
	 * @param int $event
	 * @param mixed $param
	 * @return Lamb_Event_Emiter_Interface
	 */
	public function emit($event, $param = null);
}