<?php
/**
 * Lamb Framework
 * @author 小羊
 * @package Lamb_App
 */
class Lamb_App_Request
{
	const SCHEME_HTTP = 'http';
	
	const SCHEME_HTTPS = 'https';
	
	/**
	 * Custom request params
	 * @var array 
	 */
	protected $_mUserParams = array();
	
	/**
	 * @var array allowed parameters source
	 */
	protected $_mUserParamsSource = array('_GET', '_POST');
	
	/**
	 * @var string 请求的URI eg:http://www.a.com/c/index.php?a=1 uri:/c/index.php?a=1
	 */
	protected $_mRequestUri = '';
	
	/**
	 * @var string 请求时没有查询字符串的URL eg:http://a.com/dir/t.php?a=1 baseUrl:/dir/t.php
	 */
	protected $_mBaseUrl = '';
	
	/**
	 * Construct the Lamb_App_Request
	 *
	 * @param string
	 * @return void
	 * @throws Lamb_App_Request_Exception
	 */
	public function __construct($uri = null)
	{
		if (null === $uri || is_string($uri)) {
			$this->setRequestUri($uri);
		}
	}
	
	/**
	 * Access values contained in user's params,get,post,cookie,server,env
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		switch(true) {
			case isset($this->_mUserParams[$key]):
				return $this->_mUserParams[$key];
			case isset($_GET[$key]):
				return $_GET[$key];
			case isset($_POST[$key]):
				return $_POST[$key];
			case isset($_COOKIE[$key]):
				return $_COOKIE[$key];
			case isset($_SERVER[$key]):
				return $_SERVER[$key];
			case isset($_ENV[$key]):
				return $_ENV[$key];
			default:
				return null;
		}
	}
	
	/**
	 * Check to see if a property is set
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function __isset($key)
	{
		switch(true) {
			case isset($this->_mUserParams[$key]):
				return true;
			case isset($_GET[$key]):
				return true;
			case isset($_POST[$key]):
				return true;
			case isset($_COOKIE[$key]):
				return true;
			case isset($_SERVER[$key]):
				return true;
			case isset($_ENV[$key]):
				return true;
			default:
				return false;
		}	
	}
	
	/**
	 * Set $_GET values
	 *
	 * @param string|array $spec if $spec is array,the second parameter must be null
	 * @param mixed $value
	 * @return Lamb_App_Request
	 * @throws Lamb_App_Request_Exception
	 */
	public function setGet($spec, $value = null)
	{
		if (null === $value && !is_array($spec)) {
			throw new Lamb_App_Request_Exception('Invaild value passed to setGet();must be either array values or key/value pair');
		}
		
		if (null === $value && is_array($spec)) {
			foreach ($spec as $key => $val) {
				$this->setGet($key, $val);
			}
			return $this;
		}
		
		$_GET[(string)$spec] = $value;
		return $this;
	}
	
	/**
	 * Retrive a member of $_GET
	 *
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @param boolean $trim
	 * @return mixed
	 */
	public function getGet($key = null, $default = null, $trim = true)
	{
		if (null === $key) {
			return $_GET;
		}
		
		$val = isset($_GET[$key]) ? $_GET[$key] : $default;
		return $trim ? $this->batchTrim($val) : $val;
	}
	/**
	 * Set $_POST values
	 *
	 * @param string|array $spec if $spec is array,the second parameter must be null
	 * @param mixed $value
	 * @return Lamb_App_Request
	 * @throws Lamb_App_Request_Exception
	 */	
	public function setPost($spec, $value = null)
	{
		if (null === $value && !is_array($spec)) {
			throw new Lamb_App_Request_Exception('Invaild value passed to setPost();must be either array values or key/value pair');
		}
		
		if (null === $value && is_array($spec)) {
			foreach ($spec as $key => $val) {
				$this->setPost($key, $val);
			}
			return $this;
		}
		
		$_POST[(string)$spec] = $value;
		return $this;	
	}
	
	/**
	 * Retrieve a member of $_POST
	 *
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @param boolean $trim 
	 * @return mixed
	 */
	public function getPost($key = null, $default = null, $trim = true)
	{
		$trim = (boolean)$trim;
		if (null === $key) {
			return $_POST;
		}
		
		$val = isset($_POST[$key]) ? $_POST[$key] : $default;
		return $trim ? $this->batchTrim($val) : $val;
	}
	
	/**
	 * Set the value of user parameters,if $key is found and $value is null,
	 * then this key will unset
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return Lamb_App_Request
	 */
	public function setUserParam($key, $value)
	{
		if ($value === null && isset($this->_mUserParam[$key])) {
			unset($this->_mUserParams[$key]);
		} else if (null !== $value) {
			$this->_mUserParams[$key] = $value;
		}
		return $this;
	}
	
	/**
	 * Get value from user parameters or parameter source
	 *
	 * @param string $key
	 * @return mix
	 */
	public function getUserParam($key, $default = null)
	{
		$paramSource = $this->getUserParamsSource();
		if (isset($this->_mUserParams[$key])) {
			return $this->_mUserParams[$key];
		} else if (in_array('_GET', $paramSource) && isset($_GET[$key])) {
			return $_GET[$key];
		} else if (in_array('_POST', $paramSource) && isset($_POST[$key])) {
			return $_POST[$key];
		}
		return $default;
	}
	
	/**
	 * Set one or more user parameters
	 *
	 * @return Lamb_App_Request
	 */
	public function setUserParams(array $params)
	{
		foreach ($params as $key => $val) {
			$this->setUserParam($key, $val);
		}
		return $this;
	}
	
	public function getUserParams()
	{
		$ret = $this->_mUserParams;
		$paramSource = $this->getUserParamsSource();
		if (in_array('_GET', $paramSource) 
			&& isset($_GET)
			&& is_array($_GET)) {
			$ret += $_GET;	
		}
		
		if (in_array('_POST', $paramSource)
			&& isset($_POST)
			&& is_array($_POST)) {
			$ret += $_POST;	
		}
		return $ret;
	}
	
	/**
	 * @return Lamb_App_Request
	 */
	public function removeAllUserParams()
	{
		$this->_mUserParams = array();
		return $this;
	}
	
	/**
	 * Get allowed user parameter source
	 *
	 * @return array
	 */
	public function getUserParamsSource()
	{
		return $this->_mUserParamsSource;
	}
	
	/**
	 * Set allowed user parameter source
	 *
	 * @return Lamb_App_Request
	 */
	public function setUserParamsSource(array $paramSource = array())
	{
		$this->_mUserParamsSource = $paramSource;
		return $this;
	}
	
	/**
	 * Retrieve a member of $_COOKIE
	 *
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @param boolean $trim
	 * @return mixed
	 */
	public function getCookie($key = null, $default = null, $trim = true)
	{
		$trim = (boolean)$trim;
		if (null === $key) {
			return $_COOKIE;
		}
		
		$val = isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
		return $trim ? $this->batchTrim($val) : $val;
	}
	
	/**
	 * Retrieve a member of $_SERVER
	 * 
	 * @param string $key
	 * @param string $default
	 * @return mixed
	 */
	public function getServer($key = null, $default = null)
	{
		if (null === $key) {
			return $_SERVER;
		}
		
		return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
	}
	
	/** 
	 * Return the method by which the request was made
	 *
	 * @return string
	 */
	public function getMethod()
	{
		return $this->getServer('REQUEST_METHOD');
	}
	
	/**
	 * 判断是否递交了某个特定的表单，通过$key区分
	 * 
	 * @param string $key
	 * @return boolean
	 */
	public function isSubmit($key)
	{
		return $this->isPost() && $this->getPost($key);
	}
	
	/**
	 * Was the request made by POST?
	 *
	 * @return boolean
	 */
	public function isPost()
	{
		return 'POST' == $this->getMethod();
	}
	
	/**
	 * Was the request made by GET?
	 *
	 * @return boolean
	 */
	public function isGet()
	{
		return 'GET' == $this->getMethod();
	}
	
	/**
	 * Return the value of the given http head,eg: Ask for 'Accept' to
	 * get the Accept header.Ask for 'Accept-Encoding' to get the Accept-Encodeing header.
	 *
	 * @param string $header Http head name
	 * @return string|false Http head value, or false if not found
	 */
	public function getHeader($header)
	{
		$header = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
		
		if (isset($_SERVER[$header])) {
			return $_SERVER[$header];
		}
		
        // This seems to be the only way to get the Authorization header on
        // Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers[$header])) {
                return $headers[$header];
            }
            $header = strtolower($header);
            foreach ($headers as $key => $value) {
                if (strtolower($key) == $header) {
                    return $value;
                }
            }
        }		
				
		return false;
	}
	
	/**
	 * Get the request URI scheme
	 * 
	 * @return string
	 */
	public function getScheme()
	{
		 return ($this->getServer('HTTPS') == 'on') ? self::SCHEME_HTTPS : self::SCHEME_HTTP;
	}
	
    /**
     * Get the HTTP host.
     *
     * "Host" ":" host [ ":" port ] ; Section 3.2.2
     * Note the HTTP Host header is not the same as the URI host.
     * It includes the port while the URI host doesn't.
     *
     * @return string
     */
	public function getHttpHost()
	{
        $host = $this->getServer('HTTP_HOST');
        if (!empty($host)) {
            return $host;
        }

        $scheme = $this->getScheme();
        $name   = $this->getServer('SERVER_NAME');
        $port   = $this->getServer('SERVER_PORT');

        if(null === $name) {
            return '';
        }
        elseif (($scheme == self::SCHEME_HTTP && $port == 80) || ($scheme == self::SCHEME_HTTPS && $port == 443)) {
            return $name;
        } else {
            return $name . ':' . $port;
        }		
	}
	
    /**
     * Get the client's IP addres
     *
     * @param  boolean $checkProxy
     * @return string
     */
    public function getClientIp($checkProxy = true)
    {
        if ($checkProxy && $this->getServer('HTTP_CLIENT_IP') != null) {
            $ip = $this->getServer('HTTP_CLIENT_IP');
        } else if ($checkProxy && $this->getServer('HTTP_X_FORWARDED_FOR') != null) {
            $ip = $this->getServer('HTTP_X_FORWARDED_FOR');
        } else {
            $ip = $this->getServer('REMOTE_ADDR');
        }

        return $ip;
    }
	
	/**
	 * 设置请求的URI，如果传入的参数为null，则从$_SERVER集合中的REQUEST_URI获取
	 * 在IIS URLRewrite组件时， 从REQUEST_URI获取的是真实URI，必须要通过判断获取
	 * 伪URI，如果传人的为string，则调用parse_str解析，并注入到$_GET集合中
	 *
	 * @param string $requestUri
	 * @return Lamb_App_Request
	 */
	public function setRequestUri($requestUri = null)	
	{
		if (null === $requestUri) {
			if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {//检测是否存在伪静态
				$this->_mRequestUri = $_SERVER['HTTP_X_REWRITE_URL'];
			} else if (
				//IIS7 以及 URLRewrite情况 从 UNENCODE_URL获取
				isset($_SERVER['IIS_WasUrlRewritten'])
                && $_SERVER['IIS_WasUrlRewritten'] == '1'
                && isset($_SERVER['UNENCODED_URL'])
                && $_SERVER['UNENCODED_URL'] != ''				
				) {
				$this->_mRequestUri = $_SERVER['UNENCODE_URL'];
			} else if (isset($_SERVER['REQUEST_URI'])) {
				$this->_mRequestUri = $_SERVER['REQUEST_URI'];
                $schemeAndHttpHost = $this->getScheme() . '://' . $this->getHttpHost();
                if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                    $this->_mRequestUri = substr($requestUri, strlen($schemeAndHttpHost));
                }				
			} else if (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0, PHP as CGI
                $this->_mRequestUri = $_SERVER['ORIG_PATH_INFO'];
                if (!empty($_SERVER['QUERY_STRING'])) {
                    $this->_mRequestUri .= '?' . $_SERVER['QUERY_STRING'];
                }
            } else {
                return $this;
            } 			
		} else if (is_string($requestUri)) {
			if (false !== ($pos = strpos($requestUri, '?')) ) {
				$query = substr($requestUri, $pos + 1);
				parse_str($query, $vars);
				$this->setGet($vars);
			}
		}
		return $this;
	}
	
	/**
	 * 获取请求的URI，如果为空，则调用setRequestUri()生成
	 *
	 * @return string
	 */
	public function getRequestUri()
	{
		if (empty($this->_mRequestUri)) {
			$this->setRequestUri();
		}
		return $this->_mRequestUri;
	}
	
	/**
	 * @param string $baseUrl
	 * @return Lamb_App_Request
	 */
	public function setBaseUri($baseUrl = null)
	{
		if (null !== $baseUrl && !is_string($baseUrl)) {
			return $this;
		}
		
		if (null === $baseUrl) {
			if (isset($_SERVER['SCRIPT_NAME'])) {
				$baseUrl = $_SERVER['SCRIPT_NAME'];
			} else {
				$baseUrl = $_SERVER['ORIG_SCRIPT_NAME'];
			}
			
			$requestUri = $this->getRequestUri();
			
			/*大部分正常情况 eg:http://a.com/a/b.php?a=1
			requestUri为：/a/b.php?a=1
			baseUrl为：/a/b.php
			最终结果：/a/b.php
			*/
			if (0 === strpos($requestUri, $baseUrl)) {
				$this->_mBaseUrl = $baseUrl;
				return $this;
			}
			
			/*假如服务器默认为index.php 则http://a.com/a/c/
			requestUri为：/a/c/
			baseUrl为：/a/c/index.php
			最终结果：/a/c
			*/
			if (0 === strpos($requestUri, dirname($baseUrl))) {
				$this->_mBaseUrl = rtrim(dirname($baseUrl), '/');
				return $this;
			}
		} else {
			$this->_mBaseUrl = rtrim($baseUrl, '/');
		}
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getBaseUri()
	{
		if (empty($this->_mBaseUrl)) {
			$this->setBaseUri();
		}
		return $this->_mBaseUrl;
	}
	
	/**
	 * @param string | array $str
	 * @return string | array
	 */
	public function batchTrim(&$str)
	{
		if (is_array($str)) {
			foreach ($str as $key => $val) {
				$str[$key] = $this->batchTrim($val);
			}
			return $str;
		} else {
			return trim($str);
		}
	}
}