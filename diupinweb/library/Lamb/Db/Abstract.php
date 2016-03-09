<?php
/**
 * Lamb Framework
 * @author 小羊
 * @package Lamb_Db
 */
abstract class Lamb_Db_Abstract extends PDO
{
	/**
	 * 批量的从$aPrepareSource中绑定预处理值到$stmt对象中
	 *
	 * @param &PDOStatement $stmt
	 * @param array $aPrepareSource [
	 *									SQL参数名，参数名对应的值，值的类型
	 *								]
	 * @return void
	 */
	public static function batchBindValue(PDOStatement &$stmt, array $aPrepareSource)
	{
		foreach ($aPrepareSource as $strKey => $aItem) {
			$stmt->bindValue($strKey, $aItem[0], $aItem[1]);
		}
		unset($stmt);
	}
	
	/**
	 * 使用滚动的游标查询记录集
	 * 注：此方法会消耗一定的性能，常用于无法获取记录集的总数
	 * 返回的记录集不用时要记得注销 eg:$recordset = null
	 * 
	 * @param string $strSql
	 * @param &array $aPrepareSource 如果为null则不使用预处理查询
	 * @return Lamb_Db_RecordSet_Interface implemention
	 */
	public function dynamicSelect($strSql, array $aPrepareSource = null)
	{
		$objRecordSet	=	null;
		try{
			if($objRecordSet = $this->prepare($strSql, array(PDO::ATTR_CURSOR=>PDO::CURSOR_SCROLL))){
				if ($aPrepareSource) {
					self::batchBindValue($objRecordSet, $aPrepareSource);
				}
				$objRecordSet->execute();
			}
		}catch(Exception $e){}
		return $objRecordSet;
	}	

	/**
	 * 通过类似这样的SQL语句获取记录的总数：
	 * sql count(*) as num from table [where ....]
	 *
	 * @param string $strSql 
	 * @param string $strNumKey 获取保存记录总数的列名
	 * @return int
	 */
	public function getRowCount($strSql,$strNumKey='num')
	{
		$nRowNum		=	-1;
		if($objRecordSet	=	$this->query($strSql)){
			$arr	=	$objRecordSet->fetch();
			$nRowNum=	$arr[$strNumKey];
			$objRecordSet	=	null;
		}
		return $nRowNum;
	}
	
	/**
	 * 调用dynamicSelect获取记录集的总数
	 *
	 * @param string $strSql
	 * @return int 如果失败则返回-1
	 */
	public function getRowCountDynamic($strSql)
	{
		$nRowCount		=	-1;
		if($objRecordSet = $this->dynamicSelect($strSql)){
			$nRowCount	=	$objRecordSet->rowCount();
			$objRecordSet->closeCursor();
		}
		return $nRowCount;	
	}
	
	/**
	 * 使用SQL预处理语句获取记录集的总数，内部调用了
	 * getRowCountEx，如果失败则调用性能差的dynamicSelect
	 * 
	 * @param string $strSql
	 * @param & array $aPrepareSource
	 * @param boolean $bIncludeUnion
	 * @return int 如果失败则返回-1
	 */
	public function getPrepareRowCount($strSql, array $aPrepareSource, $bIncludeUnion = false)
	{
		$nRowNum = -1;
		$strNewSql = $this->getRowCountEx($strSql, $bIncludeUnion, true);
		if (Lamb_Utils::isInt($strNewSql, true)) {
			return $strNewSql;
		}
		$stmt = $this->prepare($strNewSql);
		self::batchBindValue($stmt, $aPrepareSource);
		$stmt->execute();
		if (($arr = $stmt->fetch())) {
			$nRowNum = $arr['num'];
		}
		else {
			$stmt = null;
			if ($stmt = $this->dynamicSelect($strSql, $aPrepareSource)) {
				$nRowNum	=	$stmt->rowCount();
				$stmt->closeCursor();				
			}
		}
		$stmt = null;
		return $nRowNum;
	}
	
	/**
	 * 执行一条SQL语句，并返回改记录集以及记录的总数
	 * 这里使用了性能差的dynamicSelect，没使用高效的getRowCountEx
	 * 和其它的查询，主要因为调用此方法的场景一般是获取一条的记录集情况下
	 * 后期可能会升级
	 * 
	 * @param string $strSql
	 * @param boolean $bGetData
	 * @return array 如果$bGetData = false 则返回记录数组
	 *				 如果为true，则返回一个array('num' => 个数，'data' => 记录数组)
	 */
	public function getNumData($strSql, $bGetData=false)
	{
		if(!$bGetData) return $this->getRowCountDynamic($strSql);
		$aResult		=	array('num'=>-1,'data'=>null);
		if($objRecordSet = $this->dynamicSelect($strSql)){
			$aResult['num']	=	$objRecordSet->rowCount();
			$aResult['data']=	$objRecordSet->fetch();
			$objRecordSet->closeCursor();
		}
		return $aResult;
	}
	
	/**
	 * 使用SQL预处理获取同getNumData一样的功能
	 *
	 * @param string $strSql
	 * @param & array $aPrepareSource
	 * @param boolean $bGetData
	 * @return array 如果$bGetData = false 则返回记录数组
	 *				 如果为true，则返回一个array('num' => 个数，'data' => 记录数组)
	 */
	public function getNumDataPrepare($strSql, array $aPrepareSource = null, $bGetData = false)
	{
		$aResult = $bGetData ? array('num' => -1, 'data' => null) : -1;
		$objRecordSet = $this->quickPrepare($strSql, $aPrepareSource);
		if ($objRecordSet) {
			if ($bGetData) {
				$aData = $objRecordSet->fetchAll();
				$aResult['data'] = @$aData[0];
				$aResult['num'] = count($aData);
			}
			else {
				$aResult = count($objRecordSet->fetchAll());
			}
			$objRecordSet = null;
		}
		return $aResult;
	}
	
	/**
	 * 快速使用SQL预处理执行SQL语句
	 * 注：返回的记录集不用时要记得注销 eg:$recordset = null
	 *
	 * @param string $strSql
	 * @param & array $aPrepareSource
	 * @param boolean $bExec 如果为true则调用PODStatement::execute不返回记录集
	 * @return Lamb_Db_RecordSet_Interface
	 */
	public function quickPrepare($strSql, array $aPrepareSource=null, $bExec = false)
	{
		$objRecordSet = null;
		$objRecordSet = $this->prepare($strSql);
		self::batchBindValue($objRecordSet, $aPrepareSource);
		if ($bExec) {
			$objRecordSet = $objRecordSet->execute();
		}
		else {
			$objRecordSet->execute();
		}
		return $objRecordSet;
	}	
	
	/**
	 * 查询指定偏移固定长度的记录集
	 * 注：返回的记录集不用时要记得注销 eg:$recordset = null
	 *
	 * @param string $strSql
	 * @param int $nLimit
	 * @param int $nOffset
	 * @param boolean $bIncludeUnion
	 * @return Lamb_Db_RecordSet_Interface
	 */ 
	public function limitSelect($strSql,$nLimit,$nOffset=0,$bIncludeUnion=false)
	{
		$objRecordSet	=	null;
		if($strNewSql = Lamb_App::getGlobalApp()->getSqlHelper()->getLimitSql($strSql,$nLimit,$nOffset,$bIncludeUnion)){
			$objRecordSet = $this->query($strNewSql);
		}
		return $objRecordSet;
	}			

	/**
	 * @param string $sql
	 * @param int $limit
	 * @param array $aPrepareSource
	 * @param int $offset
	 * @param boolean $hasUnion
	 * @return Lamb_Db_RecordSet_Interface
	 */
	public function limitSelectPrepare($sql, $limit, array $aPrepareSource = null, $offset = 0, $hasUnion =false)
	{
		$sql = Lamb_App::getGlobalApp()->getSqlHelper()->getPrePareLimitSql($sql, $hasUnion);
		if (!$aPrepareSource) {
			$aPrepareSource = array();
		}
		$aPrepareSource['g_offset'] = array($offset, PDO::PARAM_INT);
		$aPrepareSource['g_limit'] = array($limit, PDO::PARAM_INT);
		return $this->quickPrepare($sql, $aPrepareSource);
	}

	/**
	 * 开始一个事务，如果成功则返回true否则false
	 *
	 * @return boolean
	 */
	public function begin()
	{
		return $this->beginTransaction();
	}	
	
	/**
	 * 提交或回滚一个失误，如果提交或回滚成功则返回true否则false
	 *
	 * @return boolean
	 */
	abstract public function end();
	
	/**
	 * 改进getRowCount，参数SQL语句无需使用sql count(*) as num from table这样的格式
	 * 普通的任何一个SQL语句eg:select * from test 都会自动解析成以上的格式
	 * 如果参数$bRetSql为true则返回解析后的SQL否则执行解析后的SQL语句并返回结果
	 *
	 * @param string $strSql
	 * @param boolean $bIncludeUnion SQL语句中是含有union关键字
	 * @param boolean $bRetSql
	 * @return string | int 如果$bRetSql为true则返回处理后的SQL，否则返回总数
	 *						如果失败则返回-1
	 */
	abstract public function getRowCountEx($strSql,$bIncludeUnion=false, $bRetSql = false);
}