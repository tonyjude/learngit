<?php
/**
 * Lamb Framework
 *
 * @author 小羊
 * @package Lamb_Mssql_Sql
 */
class Lamb_Mssql_Sql_Helper extends Lamb_Db_Sql_Helper_Abstract
{
	/**
	 * @override
	 */
	public function getLimitSql($sql, $nLimit, $nOffset = 0, $bIncludeUnion=false)
	{
		$strNewSql	=	'';
		if($bIncludeUnion){
			$sql	=	"select * from ($sql) as __UINT_XY_T__";
		}
		$strFields	=	$this->getSqlField($sql);
		$strSqlLast	=	strstr($sql, $strFields);		
		if($nOffset <= 0){
			$strNewSql	=	"select top $nLimit $strSqlLast";
		}else{
			$nTopPos	=	$nLimit+$nOffset;
			$strSelField=	preg_replace('/[a-zA-Z_]+?\./is', '', preg_replace('/([,\s+])[^,]*?\s+as\s+([a-zA-Z_]*)/is', '$1$2', $strFields));
			$strNewSql	=	"select $strSelField from (select row_number() over (order by __T_COL__) as __ROW_NUMBER__,*
			 from (select top $nTopPos 0 as __T_COL__,$strSqlLast) __XY_T___) __XY_T__ where __ROW_NUMBER__>$nOffset";
		}
		return $strNewSql;	
	}
	
	/**
	 * @override
	 */
	public function escape($sql)
	{
		return str_replace("'", "''", $sql);
	}
	
	/**
	 * @override
	 */
	public function escapeBlur($sql)
	{
		return preg_replace('/([\[\]%^_-])/is','[$1]', $sql);
	}
	
	/**
	 * @override
	 */
	public function escapeBlurEncoded($sql)
	{
		return $this->escapeBlur($this->escape($sql));
	}
	
	/**
	 * @override
	 */	
	public function getPrePareLimitSql($sql, $bIncludeUnion = false)
	{
		if($bIncludeUnion){
			$sql	=	"select * from ($sql) as __UINT_XY_T__";
		}
		$strFields	=	self::getSqlField($sql);
		$strSqlLast	=	strstr($sql, $strFields);		
		$strSelField=	preg_replace('/[a-zA-Z_]+?\./is', '', preg_replace('/([,\s+])[^,]*?\s+as\s+([a-zA-Z_]*)/is', '$1$2', $strFields));
		return "select $strSelField from (select row_number() over (order by __T_COL__) as __ROW_NUMBER__,*
		 from (select top(:g_limit) 0 as __T_COL__,$strSqlLast) __XY_T___) __XY_T__ where __ROW_NUMBER__>:g_offset";	
	}
	
	/**
	 * @override
	 */
	public function escapeField($field)
	{
		return '[' . $field . ']';
	}	 	 	 	 
}