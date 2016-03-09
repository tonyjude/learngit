<?php
/**
 * Lamb Framework
 * @author 小羊
 * @package Lamb_Db
 */
class Lamb_Db_Select extends Lamb_Db_CallbackHandler
{	
	/**
	 * @var Lamb_
	 */
	protected $_mCache = null;
	
	/**
	 * @var string
	 */
	protected $_mSql = '';
	
	/** 
	 * $_mSql中是否含有union
	 * null - 默认使用Lamb_Db_Sql_Helper::hasUnion获取
	 * false, true
	 *
	 * @var boolean 
	 */
	protected $_mSqlHasUnion = null;
	
	/**
	 * @var boolean 当记录集为空时，是否需要缓存
	 */
	protected $_mIsEmptyCached = false;
	
	/**
	 * @param string $sql
	 * @param Lamb_Db_Abstract $db
	 */
	public function __construct($sql = null, $dbCallback = null)
	{
		if (null !== $sql) {
			$this->setOrGetSql($sql);
		}
		if (null !== $dbCallback) {
			$this->setDbCallback($dbCallback);
		}
	}
	
	/** 
	 * @param boolean $flag
	 * @return boolean | Lamb_Db_Select
	 */
	public function setOrGetIsEmptyCached($flag = null)
	{
		if (null === $flag) {
			return $this->_mIsEmptyCached;
		}
		$this->_mIsEmptyCached = (boolean)$flag;
		return $this;
	}
	
	/**
	 * @param string $sql
	 * @return string | Lamb_Db_Select
	 */
	public function setOrGetSql($sql = null)
	{
		if (null === $sql) {
			return $this->_mSql;
		}
		$this->_mSql = (string)$sql;
		return $this;
	}
	
	/**
	 * @param boolean $hasUnion or null
	 * @return Lamb_Db_Select
	 */
	public function setSqlHasUnion($hasUnion)
	{
		$this->_mSqlHasUnion = $hasUnion === null ? null : (boolean)$hasUnion;
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	public function getSqlHasUnion()
	{
		return $this->_mSqlHasUnion;
	}
	
	/**
	 * @param Lamb_Cache_Interface $cache
	 * @return Lamb_Cache_Interface | Lamb_Db_Select
	 */
	public function setOrGetCache(Lamb_Cache_Interface $cache = null)
	{
		if (null === $cache) {
			return $this->_mCache;
		}
		$this->_mCache = $cache;
		return $this;
	}
	
	/**
	 * @param array $aPrepareSource
	 * @return Lamb_Db_RecordSet_Interface
	 */
	public function query(array $aPrepareSource = null)
	{
		if ($this->_mCache && $this->_mCache->isCached()) {
			return new Lamb_Db_RecordSet_Array(unserialize($this->_mCache->read()));
		}
		$db = $this->getDb();
		$sql = $this->setOrGetSql();
		if ($aPrepareSource) {
			$objRecordSet = $db->prepare($sql);
			Lamb_Db_Abstract::batchBindValue($objRecordSet, $aPrepareSource);
			$objRecordSet->execute();
		} else {
			$objRecordSet = $db->query($sql);
		}
		$objRecordSet->setHasUnion($this->getSqlHasUnion($sql));
		if ($this->_mCache) {
			//如果有数据或者没有数据但是允许缓存空数据
			if ($objRecordSet->getRowCount() > 0 || $this->setOrGetIsEmptyCached()) {
				$data = $objRecordSet->toArray();
				$objRecordSet = new Lamb_Db_RecordSet_Array($data);			
				$this->_mCache->write(serialize($data));
			}
		}
		return $objRecordSet;
	}
	
	/**
	 * 分页查询
	 *
	 * @param int $page
	 * @param int $pagesize
	 * @param & int $allCount
	 * @param & array $aPrepareSource
	 * @return Lamb_Db_RecordSet_Interface
	 */
	public function pageQuery($page, $pagesize, &$allCount, array $aPrepareSource = null)
	{
		//read cache
		if ($this->_mCache && $this->_mCache->isCached()) {
			$data = unserialize($this->_mCache->read());
			$allCount = $data['num'];
			unset($allCount);
			return new Lamb_Db_RecordSet_Array($data['data']);
		}
		//if no cache or disabled cache
		$sqlHelper = Lamb_App::getGlobalApp()->getSqlHelper();
		$sqlSource = $this->setOrGetSql();
		$hasUnion = $this->getSqlHasUnion();
		if (null === $hasUnion) {
			$hasUnion = $sqlHelper->hasUnionKey($sqlSource);
		}
		$db = $this->getDb();
		if ($aPrepareSource !== null) {//prepare query
			$sql = $sqlHelper->getPrePareLimitSql($sqlSource, $hasUnion);
			$aPrepareSource[':g_limit'] = array($page * $pagesize, PDO::PARAM_INT);
			$aPrepareSource[':g_offset'] = array(($page - 1) * $pagesize, PDO::PARAM_INT);
			$objRecordSet = $db->prepare($sql);
			Lamb_Db_Abstract::batchBindValue($objRecordSet, $aPrepareSource);
			$objRecordSet->execute();
			unset($aPrepareSource[':g_limit'], $aPrepareSource[':g_offset']);
			$allCount = $db->getPrepareRowCount($sqlSource, $aPrepareSource, $hasUnion);
		} else {//normal query
			$sql = $sqlHelper->getPageSql($sqlSource, $pagesize, $page, $hasUnion);
			$objRecordSet = $db->query($sql);
			$allCount = $db->getRowCountEx($sqlSource, $hasUnion);
		}
		$objRecordSet->setHasUnion($hasUnion); 
		//save the cache if necessary
		if ($this->_mCache) {
			//如果有数据或者没有数据但是允许缓存空数据
			if ($objRecordSet->getRowCount() > 0 || $this->setOrGetIsEmptyCached()) {
				$data = $objRecordSet->toArray();
				$objRecordSet = new Lamb_Db_RecordSet_Array($data);
				$this->_mCache->write(serialize(array('data' => $data, 'num' => $allCount)));
			}
		}
		unset($allCount);
		return $objRecordSet;
	}
	
	/** 
	 * 生成SQL唯一的CRC32字符串
	 *
	 * @param string $sql
	 * @param int $page
	 * @param int $pagesize
	 * @param array $aPrepareSource
	 */
	public static function getSqlIdentity($sql, $page = null, $pagesize = null,array $aPrepareSource = null)
	{
		if ($page !== null) {
			$sql .= ':page' . $page;
		}
		if ($pagesize != null) {
			$sql .= ':pagesize' . $pagesize;
		}
		if ($aPrepareSource !== null) {
			$sql .= ':prepare' . print_r($aPrepareSource, true);
		}
		return Lamb_Utils::crc32FormatHex($sql);
	}
}