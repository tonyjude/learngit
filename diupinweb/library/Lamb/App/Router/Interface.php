<?php
/**
 * Lamb Framework
 * @author 小羊
 * @package Lamb_App_Router
 */
interface Lamb_App_Router_Interface
{
	/**
	 * Get name of controllor
	 *
	 * @return string
	 */
	public function getControllor();
	
	/**
	 * Get name of action
	 *
	 * @return string
	 */
	public function getAction();
	
	/**
	 * Get params of query
	 *
	 * @return array
	 */
	public function getParams();
	
	/**
	 * Parse query string to control,action and params
	 *
	 * @param string|Lamb_App_Request $query
	 * @return Lamb_App_Router_Interface
	 * @throws Lamb_App_Router_Exception
	 */
	public function parse($query = '');
	
	/**
	 * Inject the params to Lamb_App_Request_Interface implement
	 *
	 * @param Lamb_App_Request_Interface implemention
	 * @return Lamb_App_Router_Interface implemention
	 */
	public function injectRequest(Lamb_App_Request $request = null);
	
	/**
	 * Generate router string
	 *
	 * @param string|array $params
	 *						string [$params must be a legal url string, and have controllor and action key. eg:'controllor=b&action=d&n1=v1']
	 *						array [$params mush have controllor and action key eg: array('controllor' => 'c', 'action' => 'a', 'n1' => 'v1', ...)]
	 * @param boolean $encode 
	 * @return string
	 * @throws Lamb_App_Router_Exception
	 */
	public function url($params, $encode = true);
}