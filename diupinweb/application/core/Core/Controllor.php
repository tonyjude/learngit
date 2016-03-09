<?php
abstract class Core_Controllor extends Lamb_Controllor_Abstract
{
	
	
	
	/**
	 * 每页获取最大的数据数
	 */
	const MAX_PAGESIZE = 50;
	
	/**
	 * @var array
	 */
	protected $mSiteCfg;
	
	/**
	 * @var string current Controllor;
	 */
	public $C;
	
	/**
	 * @var string current Action
	 */
	public $A;
	
	/** 
	 * @var int
	 */
	public $mCacheTime ;	
	
	/**
	 * @var string
	 */
	protected $mRuntimeTemplate;
	
	/**
	 * @var string
	 */
	protected $mRuntimeViewPath;
	
	/**
	 * @var string
	 */
	protected $mRuntimeThemePath;
	
	/**
	 * @var string
	 */
	protected $mRuntimeThemeUrl;	
	
	/**
	 * @var callback
	 */
	protected $mCacheCallback;
	
	/**
	 * @var int
	 */
	protected $mCacheType;
	
	/**
	 * @var string
	 */
	protected $mHash;	
	
	public function __construct()
	{
		parent::__construct();
		$this->mSiteCfg = Lamb_Registry::get(CONFIG);
		$this->mRuntimeTemplate = $this->mSiteCfg['template'];
		$this->mRuntimeThemePath = $this->mSiteCfg['theme_path'] . $this->mRuntimeTemplate . '/';;
		$this->mRuntimeViewPath = $this->mSiteCfg['view_path'] . $this->mRuntimeTemplate . '/';
		$this->mRuntimeThemeUrl =  $this->mSiteCfg['site']['root'] . substr($this->mRuntimeThemePath, strlen(ROOT));
		$this->mApp->setViewPath($this->mRuntimeViewPath);
//		$this->mCacheCallback = 'Diupin_Cache_Factory::getCache';
		$this->mCacheTime = $this->mRequest->ct;
		if (!Lamb_Utils::isInt($this->mCacheTime)) {
			$this->mCacheTime = 0;
		}
		$this->mCacheType = Lamb_View_Tag_List::CACHE_HTML;			
		$this->C = $this->mDispatcher->setOrGetControllor();
		$this->A = $this->mDispatcher->setOrGetAction();
		$this->mHash = spl_object_hash($this);
		Lamb_Utils::registerCallObject($this);	
	}
	
	
	
	/**
	 * 判断用户是否登录
	 * @param &boolean $isExpire 当返回值为false可以通过
	 * 			$isExpire的值来判断，是因为sesskey过期，而导致的还是因为
	 * 			sesskey非法而导致。如果$isExpire=1则表示为过期导致的
	 * @param 当没有登录，或超时时，是否直接输出错误码
	 * 
	 * @return int 如果没有登录则返回0，登录则返回>0
	 */
	public function isLogin(&$isExpire = 0, $isExitWhenError = true)
	{
		if ($this->mSessionKey) {
			
			if ($this->mSessionKey == 'abcd') {
				if ($isExitWhenError) {
					$this->showResults(-1);
				}
				return FALSE;
			}
			
			$uid = Core_Utils::auth_decode($this->mSessionKey, self::SESSION_MECRYPT_KEY, $isExpire);
			
			if (Lamb_Utils::isInt($uid)) {
				return $uid;
			}	
		}
		
		if ($isExitWhenError) {
			$this->showResults($isExpire ? -2 : -1);			
		}

		unset($isExpire);
		return false;
	}
	
	/**
	 * 带错误信息的输出
	 *
	 * @param int $code 错误码
	 * @param array $data 输出的内容
	 * @param string $errorString 错误信息，如果为空，当$code=0,-1,-2则会输出固定的错误信息，如果不为空，则会先从配置文件error_strings找出对应的映射，
	 * 如果找不到映射，则直接将该值输出
	 */
	public function showResults($code, array $data = null, $errorString = '')
	{
		static $fixedErrorStr = array(
			'0' => '服务器繁忙，请稍后再试',
			'-1' => '您还没有登录',
			'-2' => '登录过期，请重新登录'
		);
		
		$ret = array('s' => $code);
		
		if ($data) {
			$ret['d'] = $data;
		}
		
		if ($errorString && isset($this->mSiteCfg['error_strings']) && isset($this->mSiteCfg['error_strings'][$errorString])) {
			$errorString = $this->mSiteCfg['error_strings'][$errorString];
		}
		
		if (!$errorString && isset($fixedErrorStr[$code])) {
			$errorString = $fixedErrorStr[$code];
		}
		
		$ret['err_str'] = $errorString;
		
		$ret = json_encode($ret);
		$this->mResponse->eecho($ret);	
	}
	

	/**
	 * 二维数组根据某个键值排序
	 * @param array $arr 数组
	 * @param string $keys 键值
	 * @return array 排序后的数组
	 */
	public function array_sort($arr, $keys, $type = 'desc') {
	    $keysvalue = $new_array = array();
	    foreach ($arr as $k => $v) {
	        $keysvalue[$k] = $v[$keys];
	    }
	    if ($type == 'asc') {
	        asort($keysvalue);
	    } else {
	        arsort($keysvalue);
	    }
	    reset($keysvalue);
	    foreach ($keysvalue as $k => $v) {
	        $new_array[$k] = $arr[$k];
	    }
		
    	return $new_array;
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
	
	
	/**
	 * 将2个结果集合并，并且按照指定的列排序，返回前面N个结果
	 * 
	 * @param array $ret1  结果集1
	 * @param array $ret2 结果集2
	 * @param string $orderColumn 指定排序的列
	 * @param string $orderVal $orderVal=desc 为降序 asc为倒序
	 * @param int $limit 指定返回的数目
	 * 
	 * @return array
	 */
	public function combinDataAndOrder($ret1, $ret2, $orderColumn, $orderval, $limit)
	{
		$ret = array_merge($ret1, $ret2);
		$func = create_function('$a,$b', 'return $a[\'' . $orderColumn . '\'] ' . ($orderval == 'asc' ? '<' : '>') . ' $b[\'' . $orderColumn . '\'] ? -1:1;');
		usort($ret, $func);
		return array_slice($ret, 0, $limit);
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
