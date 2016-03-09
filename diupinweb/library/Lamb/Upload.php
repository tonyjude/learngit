<?php
/**
 * Lamb Framework
 * @author 小羊
 * @package Lamb
 */
class Lamb_Upload
{
	const SUFFIX_CHECK_ALLOWS = 1;
	
	const SUFFIX_CHECK_UNALLOWS = 2;
	
	/**
	 * @var array 允许上传的扩展名
	 */
	protected $_allowSuffixs = array(
					'.gif', '.jpg', '.png', '.jpeg'
				);
	/**
	 * @var array 不允许上传的扩展名 优先级最高
	 */
	protected $_unallowSuffixs = array();
	
	/**
	 * @var int 上传文件最大小，0为不限制 KB
	 */
	protected $_maxFilesize = 0; 
	
	/**
	 * @var int
	 */
	protected $_mCheckSuffixType = self::SUFFIX_CHECK_ALLOWS;
	
	
	public function __construct()
	{
	
	}
	
	/**
	 * @return array
	 */
	public function getAllowSuffixs()
	{
		return $this->_allowSuffixs;
	}
	
	/**
	 * @param string
	 *　@return Lamb_Upload
	 */
	public function addAllowSuffix($ext)
	{
		if (!in_array($ext, $this->getAllowSuffixs())) {
			$this->_allowSuffixs[] = $ext;
		}
		return $this;
	}
	
	/**
	 * @param string $ext
	 * @return boolean | int if exists
	 */
	public function hasAllowSuffix($ext)
	{
		return in_array(strtolower($ext), $this->getAllowSuffixs());
	}
	
	/**
	 * 设置所有的后缀名
	 * @param string | array 后缀名
	 * @return this
	 */
	public function setAllAllowSuffix($ext)
	{
		if (is_string($ext)) {
			$ext = explode(',', $ext);
		}
		$this->_allowSuffixs = $ext;
		return $this;
	}
	
	/**
	 * @param string $ext
	 * @return Lamb_Upload
	 */
	public function removeAllowSuffix($ext)
	{
		if (false !== ($index = $this->hasAllowSuffix($ext))) {
			unset($this->_allowSuffixs[$index]);
		}
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getUnallowSuffixs()
	{
		return $this->_unallowSuffixs;
	}
	
	/**
	 * @param string
	 *　@return Lamb_Upload
	 */
	public function addUnallowSuffix($ext)
	{
		if (!in_array($ext, $this->getUnallowSuffixs())) {
			$this->_unallowSuffixs[] = $ext;
		}
		return $this;
	}
	
	/**
	 * @param string $ext
	 * @return boolean | int if exists
	 */
	public function hasUnallowSuffix($ext)
	{
		return in_array($ext, $this->getUnallowSuffixs());
	}
	
	/**
	 * @param string $ext
	 * @return Lamb_Upload
	 */
	public function removeUnallowSuffix($ext)
	{
		if (false !== ($index = $this->hasUnallowSuffix($ext))) {
			unset($this->_unallowSuffixs[$index]);
		}
		return $this;
	}
	
	/**  
	 * @param int $size
	 * @return int | Lamb_Uplaod
	 */
	public function setOrGetMaxFilesize($size = null)
	{
		if (null === $size) {
			return $this->_maxFilesize;
		}
		$this->_maxFilesize = (int)$size;
		return $this;
	}
	
	/**
	 * @param int $checkSuffixType
	 * @return int | Lamb_Upload
	 */
	public function setOrGetCheckSuffixType($checkSuffixType = null)
	{
		if (null === $checkSuffixType) {
			return $this->_mCheckSuffixType;
		}
		$this->_mCheckSuffixType = (int)$checkSuffixType;
		return $this;
	}
	
	/**
	 * @param array $aOptions = array(
	 *					'varname' => '', http标识名 如果为空则为多文件上传
	 *					'is_keepname' => false, 是否保存原文件名
	 *					'save_path' => '',保存的路径
	 *					'is_safe_check' => true 是否需要扩展名，文件大小等检测
	 *				)
	 * @return int | array 如果出错则为int型 -1为没有可上传的文件 >=0则为没有通过安检的文件索引
	 * 						如果成功则返回成功的文件数组，每个元素都是文件名
	 */
	public function upload(array $aOptions)
	{
		$options = array(
			'varname' => '',
			'is_keepname' => false,
			'save_path' => '',
			'is_safe_check' => true
		);
		Lamb_Utils::setOptions($options, $aOptions);
		$attachments = $this->getAttachments($options['varname']);

		if (false === $attachments) { //没有可上传的文件
			return -1;
		}
		if ($options['is_safe_check']) {//安检
			if (($errorno = $this->checkSuffix($attachments)) >= 0) {//扩展名检测失败
				return $errorno;
			}
			if (($errorno = $this->checkSize($attachments)) >= 0) {//文件大小检测失败
				return $errorno;
			}
		}
		$aFiles = $this->_upload($options['save_path'], $attachments, $options['is_keepname']);
		return count($aFiles) ? $aFiles : -1;
	}	
	
	/**
	 * @param array $files 文件名或者文件名数组
	 * @return int -1 sucss >= 0 代表哪个文件上传失败
	 */
	public function checkSuffix(array $files)
	{
		$suffixType = $this->setOrGetCheckSuffixType();	

		foreach ($files as $key => $file) {
			$suffix = Lamb_IO_File::getFileExt($file['name']);
			if ($suffixType === self::SUFFIX_CHECK_ALLOWS && !$this->hasAllowSuffix($suffix)) {
				return $key;
			}
			if ($suffixType === self::SUFFIX_CHECK_UNALLOWS && $this->hasUnallowSuffix($suffix)) {
				return $key;
			}
		}
		return -1;
	}

	/**
	 * @param array $files 文件名或者文件名数组
	 * @return int -1 sucss >= 0 代表哪个文件上传失败
	 */	
	public function checkSize(array $files)
	{
		$maxsize = $this->setOrGetMaxFilesize() * 1024;
		if ($maxsize > 0) {
			foreach ($files as $key => $file) {
				if ($maxsize < $file['size']) {
					return $key;
				}
			}
		}
		return -1;
	}
	
	/**
	 * @param string $varname 如果$varmae为空则为多文件上传
	 * @return array | false if not found
	 */
	public static function getAttachments($varname = '')
	{
		$ret = array();
		if(!$varname) {
			foreach ($_FILES as $v) {
				!empty($v['name']) && $v['error'] == 0 ? $ret[] = $v : '';
			}
			if (count($ret)<=0) {
				return false;
			}
		} else {
			if (!isset($_FILES[$varname]) || !is_array($_FILES[$varname])) {				
				return false;
			}
			if (is_array($_FILES[$varname]['error'])) {
					if ($_FILES[$varname]['error'][$key] === 0) {
						$ret[] = array(
								'name' => $_FILES[$varname]['name'][$key],
								'tmp_name' => $_FILES[$varname]['tmp_name'][$key],
								'type' => $_FILES[$varname]['type'][$key],
								'size' => $_FILES[$varname]['size'][$key]
							);
					}
			} else if ($_FILES[$varname]['error'] === 0){
				$ret[0] = $_FILES[$varname];
			}			
		}
		return $ret;	
	}
	
	/**
	 * 上传文件
	 *
	 * @param string $path
	 * @param array $attachments froms $_FILES
	 * @param boolean $isKepp
	 * @return array
	 */
	protected function _upload($path, array $attachments, $isKeep = false)
	{
		$aFileName=array();
		foreach($attachments as $k => $data) {		
			if (!$isKeep) {
				$filepath = Lamb_IO_File::generateCrc32EncodeFileNamePath($path . microtime(true) . rand(0, 1000), 
								Lamb_IO_File::getFileExt($data['name']));
			} else {
				$filepath = $path . $data['name'];
			}
			$filepath = Lamb_IO_File::getUniqueName($filepath);
			move_uploaded_file($data['tmp_name'], $filepath);
			@unlink($data['tmp_name']);
			$aFileName[] = $filepath;
		}
		return $aFileName;	
	}
}