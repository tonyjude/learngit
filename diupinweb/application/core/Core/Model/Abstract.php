<?php
abstract class Core_Model_Abstract
{
	protected $mApp;
	
	protected $mSiteCfg;
	
	public function __construct()
	{
		$this->mApp = Lamb_App::getGlobalApp();
		$this->mSiteCfg = Lamb_Registry::get(CONFIG);
	}
	
	public function d($str)
	{
		Lamb_Debuger::debug($str);
	}
	
	/**
	 * php对象转数组
	 * @param object $object
	 * @return array 
	 */
	public function object2array(&$object) 
	{
        if (is_object($object)) {
            $arr = (array)($object);
        } else {
            $arr = &$object;
        }
		
        if (is_array($arr)) {
            foreach($arr as $varName => $varValue){
                $arr[$varName] = json_decode( json_encode( $varValue),true);
            }
        }
		
		unset($object);
        return $arr;
    }
}