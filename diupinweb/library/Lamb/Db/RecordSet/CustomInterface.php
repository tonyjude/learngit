<?php
/**
 * Lamb Framework
 * 此接口不同于Lamb_Db_RecordSet_Interface,
 * Lamb_Db_RecordSet_Interface只适用于已经实现了Traversable接口的子类
 * 一般自定义的类是无法实现Traversable接口，只有PHP内部类才行
 * 而Lamb_Db_RecordSet_CustomInterface继承了Iterator接口，
 * 自定义的类只要实现Iterator接口所有的方法就可以了
 *
 * @author 小羊
 * @package Lamb_Db_RecordSet
 */
interface Lamb_Db_RecordSet_CustomInterface extends Lamb_Db_RecordSet_Interface, Iterator
{}