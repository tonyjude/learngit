<?php
/**
 * Lamb Framework
 * @author 小羊
 * @package Lamb_Db
 */
class Lamb_Db_RecordSet extends PDOStatement implements Lamb_Db_RecordSet_Interface
{
	/**
	 * @var Lamb_Db_Abstract 数据库对象
	 */
	protected $db = null;
	
	/**
	 * @var $bindParams 保存绑定的参数
	 */
	protected $bindParams = array();
	
	/**
	 * @var $bindValues 保存绑定的值
	 */
	protected $bindValues = array();
	
	/** 
	 * $queryString中是否含有union
	 * null - 默认使用Lamb_Db_Sql_Helper::hasUnion获取
	 * false, true
	 *
	 * @var boolean 
	 */
	protected $hasUnion = null;
	
	/**
	 * Construct the Lamb_Db_RecordSet
	 * 可用于POD::setAttribute(PDO::ATTR_STATEMENT_CLASS, array('Lamb_Db_RecordSet', array(PDO)))
	 *
	 * @param Lamb_Db_Abstract $db
	 */
	protected function __construct(Lamb_Db_Abstract $db = null)
	{
		if (null !== $db) {
			$this->setOrGetDb($db);
		}
	}
	
	/**
	 * @param Lamb_Db_Abstract $db
	 * @return Lamb_Db_Abstract | Lamb_Db_RecordSet
	 */
	public function setOrGetDb(Lamb_Db_Abstract $db = null)
	{
		if (null === $db) {
			return $this->db;
		}
		$this->db = $db;
		return $this;
	}
	
	/**
	 * @param boolean $hasUnion or null
	 * @return Lamb_Db_Select
	 */
	public function setHasUnion($hasUnion)
	{
		$this->hasUnion = $hasUnion === null ? null : (boolean)$hasUnion;
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	public function getHasUnion()
	{
		return $this->hasUnion;
	}	
	
	/**
	 * 收集所有绑定的参数，保存在bindParams数组中，
	 * 以便调用getAllCountCount用
	 *
	 * @override
	 *
	public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null)
	{
		$param = array(&$variable, $data_type);
		if (null === $length) {
			$ret = parent::bindParam($parameter, $data_type);
		} else if (null == $driver_options) {
			$param[] = $length;
			$ret = parent::bindParam($parameter, $data_type, $length);
		} else {
			$param[] = $length;
			$param[] = $driver_options;
			$ret = parent::bindParam($parameter, $data_type, $length);
		}
		unset($variable);
		$this->bindParams[$parameter] = $param;
		return $ret;
	}/
	
	/**
	 * 收集所有绑定的参数，保存在bindParams数组中，
	 * 以便调用getAllCountCount用
	 *	
	 * @override
	 */
	public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
	{
		$this->bindValues[$parameter] = array($value, $data_type);
		return parent::bindValue($parameter, $value, $data_type);
	}
	
	/**
	 * $value为null时候，当$key为字符串则是删除此键值
	 * 当$key为array时，则是批量修改
	 *
	 * @param string|int $key
	 * @param array | null $value
	 * @return Lamb_Db_RecordSet
	 */
	public function setBindParams($key, $value = null)
	{
		if (null === $value) {
			if (is_string($key)) {
				unset($this->bindParams[$key]);
			}
			if (is_array($key)) {
				foreach ($key as $k => $v) {
					$this->setBindParams($k, $v);
				}
			}
		} else if (is_array($value)){
			$this->bindParams[$key] = $value;
		}
		return $this;
	}
	
	/**
	 * @param string | int $key 如果为null则返回整个数组，否则则返回键值对应的值
	 * @return array
	 */
	public function getBindParams($key = null)
	{
		if (null === $key) {
			return $this->bindParams;
		}
		return isset($this->bindParams[$key]) ? $this->bindParams[$key] : null;
	}

	/**
	 * $value为null时候，当$key为字符串则是删除此键值
	 * 当$key为array时，则是批量修改
	 *
	 * @param string|int $key
	 * @param array | null $value
	 * @return Lamb_Db_RecordSet
	 */	
	public function setBindValues($key, $value = null)
	{
		if (null === $value) {
			if (is_string($key)) {
				unset($this->bindValues[$key]);
			}
			if (is_array($key)) {
				foreach ($key as $k => $v) {
					$this->setBindValues($k, $v);
				}
			}
		} else if (is_array($value)) {
			$this->bindValues[$key] = $value;
		}
		return $this;
	}
	
	/**
	* @param string | int $key 如果为null则返回整个数组，否则则返回键值对应的值
	 * @return array
	 */
	public function getBindValues($key = null)
	{
		if (null === $key) {
			return $this->bindValues;
		}
		return isset($this->bindValues[$key]) ? $this->bindValues[$key] : null;
	}
	
	/**
	 * 此方法不适合调用bindParam访问中的PARAM_OUTPUT参数
	 *
	 * Lamb_Db_RecordSet_Interface implemention
	 */
	public function getRowCount()
	{
		$nRowCount	=	parent::rowCount();
		if ($nRowCount < 0) {
			$hasUnion = $this->getHasUnion();
			if (null === $hasUnion) {
				$hasUnion = Lamb_App::getGlobalApp()->getSqlHelper()->hasUnionKey($this->queryString);
			}
			if (count($this->bindParams) <= 0 && count($this->bindValues) <= 0) { //没有使用预处理查询
				$nRowCount = $this->db->getRowCountEx($this->queryString, $hasUnion);
			} else {
				foreach ($this->bindParams as $key => $val) {
					switch(count($val)) {
						case 2:
							parent::bindParam($key, $val[0], $val[1]);
							break;
						case 3:
							parent::bindParam($key, $val[0], $val[1], $val[2]);
							break;
						case 4:
							parent::bindParam($key, $val[0], $val[1], $val[2], $val[3]);
							break;
					}
				}
				$nRowCount = $this->db->getPrepareRowCount($this->queryString, $this->bindValues, $hasUnion);
			}
		}
		return $nRowCount;	
	}
	
	/**
	 * Countable implemention
	 *
	 * @return int
	 */
	public function count()
	{
		return $this->getRowCount();
	}
	
	/**
	 * Lamb_Db_RecordSet_Interface implemention
	 */
	public function toArray()
	{
		return $this->fetchAll();
	}
	
	/**
	 * Lamb_Db_RecordSet_Interface implemention
	 */
	public function getColumnCount()
	{
		return $this->columnCount();
	}
}