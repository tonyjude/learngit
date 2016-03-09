<?php
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 1);
ini_set("date.timezone","PRC");
define('ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('APP_PATH', ROOT . '../application/core/');
require('../library/Connect/qq/qqConnectAPI.php');
set_include_path(ROOT . '../library/'
	.PATH_SEPARATOR . APP_PATH
	.PATH_SEPARATOR . get_include_path());
require_once 'Lamb/Loader.php';
$loader = Lamb_Loader::getInstance();
$loader->registerNamespaces('Core');
//registry
$aCfg = require_once('config.inc.php');
Lamb_Registry::set(CONFIG, $aCfg);

Lamb_App::getInstance()->setControllorPath($aCfg['controllor_path'])
						->setViewRuntimePath($aCfg['view_runtime_path'])
						->setErrorHandler(new Core_ErrorHandler)
						->setDbCallback('getDb')
						->setSqlHelper(new Lamb_Mysql_Sql_Helper)
						->run();
						
function getDb()
{
	static $objInstance = null;
	global $aCfg;
	
	if ($objInstance) {
//		return $objInstance;
	}
	
	try{
		$objInstance = new Lamb_Mysql_Db($aCfg['db_cfg']['dsn'], $aCfg['db_cfg']['username'], $aCfg['db_cfg']['password'], array(
									PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NAMED,
									PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES'utf8mb4';"
								));
		$objInstance->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('Lamb_Db_RecordSet', array($objInstance)));
	}catch (Exception $e){
		die('Connect database error');
	}
	return $objInstance;
}	