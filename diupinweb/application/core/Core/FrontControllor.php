<?php
abstract class Core_FrontControllor extends Lamb_Controllor_Abstract
{
	
	/**
	 * @var string
	 * 网站当前的模版 默认为default
	 */
	protected $mRuntimeTemplate;
	
	/**
	 * @var array
	 * 当前application的配置
	 */
	protected $mSiteCfg;
	
	public function __construct()
	{
		parent::__construct();
		$this->mSiteCfg = Lamb_Registry::get(CONFIG);
		$this->mRuntimeTemplate = $this->mSiteCfg['template'];
		$this->mApp->setViewPath($this->mSiteCfg['view_path']);
	}
	
	
	/**
	 * @param string $filename
	 * @return string
	 */
	public function load($filename)
	{
		return $this->mView->load($filename, $this->mRuntimeTemplate);
	}
	
	/**
	 * @return string
	 */
	public function autoload()
	{
		return $this->load($this->C . '_' . $this->A);
	}
	
	
	public function d($str)
	{
		Lamb_Debuger::debug($str);
	}
}
