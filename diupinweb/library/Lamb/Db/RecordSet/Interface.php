<?php
/**
 * Lamb Framework
 * Lamb_Db_RecordSet_Interface只适用于已经实现了Traversable接口的子类
 * 一般自定义的类是无法实现Traversable接口，只有PHP内部类才行
 * 
 * @author 小羊
 * @package Lamb_Db_RecordSet
 */
interface Lamb_Db_RecordSet_Interface extends Traversable,Countable
{
	/**
	 * 获取当前记录集的总数，当前页的总数
	 *
	 * @return int
	 */
	public function getRowCount();
	
	/**
	 * 获取数据源列的数目
	 *
	 * @return int
	 */
	public function getColumnCount();
	
	/**
	 * 将数据源转换成数组
	 *
	 * @return array
	 */
	public function toArray();
}