<?php
class Core_Utils
{
	const ADD_NUM = 7;
	private static $encodeMap =  array(
		0 => 's',  1 => 'g',  2 => 't',  3 => 'y',  4 => 'p', 5 => 'h',  6 => 'a',  
		7 => 'k',  8 => 'b',  9 => 'r',  10 => 'f', 11 => 'v', 12 => 'n', 13 => 'c', 
		14 => 'w', 15 => 'e', 16 => 'x', 17 => 'u', 18 => 'm', 19 => 'q'
	);
	private static $decodeMap = array(
		's' => 0, 'g' => 1,	't' => 2, 'y' => 3, 'p' => 4,	'h' => 5,
		'a' => 6, 'k' => 7,	'b' => 8, 'r' => 9,	'f' => 10,	'v' => 11,	'n' => 12,
		'c' => 13,'w' => 14, 'e' => 15,	'x' => 16,	'u' => 17,	'm' => 18, 'q' => 19
	);
	

	/*
	 数字对应字母map ： {0 : s, 1 : g, 2 : t, 3 : y, 4 : p, 5 : h, 6 : a, 7 : k, 8 : b, 9 : r, 10 : f, 11 : v, 12 : n, 13 : c, 14 : w, 15 : e, 16 : x, 17 : u, 18 : m, 19 : q}
		以相约号uid = 70689为例 ： 
		a, uid最后一位位数字 9 + 7（固定数字） % 10 = 6；
		b, uid所有数字都加上6(除了最后一位数字) = 13 6 12 14  (16) 
		c, 得到的结果对应map ：canwx即该用户的邀请码
	*/
	public static function encode($uid)
	{
		if (!Lamb_Utils::isInt($uid, true)) {
			return 'a';
		}
		
		$last = substr(strrev($uid),0,1) + self::ADD_NUM;
		$mod = $last % 10;

		if (!array_key_exists($mod,self::$encodeMap)) {
			return 'b';
		}
		
		$last = self::$encodeMap[$last];
		
		$str = '';
		for ($i=0; $i<strlen($uid)-1; $i++) {
			$index = substr($uid, $i, 1) + $mod;
			if (!array_key_exists($index,self::$encodeMap)) {
				return 'c';
			}
			$str = $str . self::$encodeMap[$index]; 
		}
		
		return $str . $last;
	}

	/*
	 * 把对应uid转成的邀请码，反转成uid  abcfre => 138548
	 **/
	public static function decode($invcode)
	{
		$invcode = strtolower($invcode);
		if (Lamb_Utils::isNumber($invcode)) {
			return 'a';
		}
		
		$len = strlen($invcode);
		if ($len > 10) {
			return 'b';
		}
	
		$last = substr(strrev($invcode),0,1);
		
		if (!array_key_exists($last,self::$decodeMap)) {
			return 'c';
		}
		
		$lastnum = self::$decodeMap[$last];
		$mod = $lastnum % 10;
		$ret = '';
		$lastnum -= self::ADD_NUM;
		
		if ($lastnum > 9 || $lastnum < 0) {
			return 'r';
		}
		
		for ($i=0; $i<$len-1; $i++) {
			$cha = substr($invcode,$i,1);
			
			if (!array_key_exists($cha,self::$decodeMap)) {
				return 'd';
			}
			
			$cha = self::$decodeMap[$cha];
			$cha = $cha - $mod;
			if ($cha > 9 || $cha < 0) {
				return 'e';
			}
			
			$ret .= $cha;
		}
		
		return $ret . $lastnum;
	}
	
	/**
	 * 按密钥加密
	 */
	public static function auth_encode($data, $key, $expire)
	{
		$key = md5($key);
		$iv = substr($key, 0, 16);
		$cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
		mcrypt_generic_init($cipher, $key, $iv);
		$data = $expire . ',' . time() . ',' . $data;
		
		$padsize = Lamb_Utils::mbLen($data) % 32;
		if ($padsize > 0) {
			$padsize = 32 - $padsize;
			$data .= str_repeat(chr(0), $padsize);
		}
		
		$str = mcrypt_generic($cipher, $data);
		mcrypt_generic_deinit($cipher);
		return base64_encode($str);		
	}
	
	/**
	 * 按密钥解密
	 */
	public static function auth_decode($data, $key, &$isExpire = 0)
	{
		$key = md5($key);
		$iv = substr($key, 0, 16);
		$decipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
		mcrypt_generic_init($decipher, $key, $iv);
		$data = base64_decode($data);
		
		if (!$data) {
			return '';
		}
		
		$str = mdecrypt_generic($decipher, $data);
		mcrypt_generic_deinit($decipher);
		$str = str_replace(chr(0), '', $str);
		
		if (!empty($str) && ($pos = strpos($str, ',')) !== false) {
			$expire = substr($str, 0, $pos);
			$str = substr($str, $pos + 1);
			
			if (($pos = strpos($str, ',')) !== false) {
				$ts = substr($str, 0, $pos);
				$str = substr($str, $pos + 1);
				
				if (Lamb_Utils::isInt($expire, true) && Lamb_Utils::isInt($ts, true) && time() - $ts < $expire) {
					$isExpire = 0;
					return $str;
				}
				
				if ($isExpire == -1) {
					$isExpire = 1;
					return $str;
				}
				$isExpire = 1;
			}
		}
		
		return '';		
	}
	
	/**
	 * 生成密码的salt，防止密码hash碰撞 
	 *
	 * @param int $min
	 * @param int $max
	 * @return string
	 */
	public static function createSalt($min = 5, $max = 10)
	{
		$ret = '';
		if ($min > $max) {
			$max = $min;
		}
		$key = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$len = rand($min, $max);
		$salt_len = strlen($key) - 1;
		for ($i = 1; $i <= $len; $i ++) {
			$ret .= $key{rand(0, $salt_len)};
		}
		return $ret;
	}
	
	/**
	 * 验证是否为手机号
	 * 注：只验证是否为1开头的11位数字
	 * 
	 * @param string $val
	 * @return boolean
	 */
	public static function isPhone($val)
	{
		return Lamb_Utils::isInt($val, true) && strlen($val) == 11 && substr($val, 0, 1) == 1;
	}	
	
	/**
	 * 获取当前日期0时0分0秒的时间戳
	 * 
	 * @return int
	 */
	public static function getCurrentDaytime()
	{
		return strtotime(date('Y-m-d 00:00:00'));
	}	
	
	
	/**
	 * 合并2个数组，并可以以inner,left,rigth,normal模式合并
	 * inner:如果任意一个数组成员中有null，则会放弃合并这一行
	 * left:以primaryData为主，不管otherData有没有null的成员，都会合并。如果primaryData成员有null，则不会合并
	 * right:以otherData为主，不管primaryData有没有null的成员，都会合并。如果otherData成员有null,则不会合并
	 * 
	 * @param array &$primaryData 主数据
	 * @param array $otherData 要合并的其他数据
	 * @param string $model 模式
	 * @return void
	 */
	public static function arrayCombine(&$primaryData, $otherData, $mode = 'inner')
	{
			
		$isDelete = false;
		foreach ($primaryData as $index => $item) {
			if (!array_key_exists($index, $otherData)) {
				continue;
			}
			
			if ($mode == 'inner') {
				
				if (!isset($primaryData[$index]) || !isset($otherData[$index])) {
					$isDelete = true;
					unset($primaryData[$index]);
					continue;
				}			
			} else if ($mode == 'left') {
				if (!isset($primaryData[$index])) {
					$isDelete = true;
					unset($primaryData[$index]);
					continue;
				}
			} else if (!isset($otherData[$index])){
				$isDelete = true;
				unset($otherData[$index], $primaryData[$index]);
				continue;
			}
			
			if (isset($primaryData[$index]) && isset($otherData[$index])) {
				$primaryData[$index] += $otherData[$index];
			}
		}
		
		if ($isDelete) {
			$primaryData = array_values($primaryData);
		}
		unset($primaryData);
	} 
	
	/**
	 * 通过键将2个数组连接在在一起
	 * 
	 * @param array & $srcData 主数组
	 * @param array $joinData 附加拼接数组
	 * @param string $srcKey 主数组用于连接的键名
	 * @param string $joinKey 附加拼接数组用于连接的键名
	 * @param boolean $isDeleNotMatchs 是否删除未匹配到的
	 * @param int $deleKeyFlag 拼接完成后是否删除 $srcKey,$joinKey的标记，如果为0，则都不删除。1删除srcKey 2删除joinKey 3全部都删除
	 */
	public static function arrayCombineByKey(&$srcData, $joinData, $srcKey, $joinKey, $isDeleNotMatchs = true, $deleKeyFlag = 0)
	{
		$newJoinData = array();
		$isDelete = false;
		
		foreach ($joinData as $index => $item) {
			$newJoinData[$item[$joinKey]] = $item;
		}
		
		foreach ($srcData as $index => $item) {
			$val = $srcData[$index][$srcKey];
			
			if (isset($newJoinData[$val])) {
				$srcData[$index] = $newJoinData[$val] + $item;
				
				if ($deleKeyFlag & 1) {
					unset($srcData[$index][$srcKey]);
				}
				
				if ($deleKeyFlag & 2) {
					unset($srcData[$index][$joinKey]);
				}
			} else if ($isDeleNotMatchs){
				$isDelete = true;
				unset($srcData[$index]);
			}
		}
		
		if ($isDelete) {
			$srcData = array_values($srcData);
		}
		unset($srcData);
	}	
	
	/**
	 * 计算字符串所占字节长度
	 * 
	 * @param string $str 要计算的字符串
	 * @return int
	 */
	public static function strLen($str)
	{
		return mb_strlen($str,'utf8');
		//return (strlen($str) + mb_strlen($str,'utf8')) / 2;
	}
	
	/**
	 * 查找或过滤emoji表情字符串
	 * 
	 * @param {string} $str字符串
	 * @param {string} $replaceMenu 要替换的字符串，如果不要替换则为null
	 * @param {boolean} &$isFind 是否找到
	 * @return {string} 返回替换后最新的结果
	 */
	public static function findEmojiString($str, $replaceMent = null, &$isFind)
	{
		$isFind = false;
		$len = mb_strlen($str, 'UTF8');
		
		for ($i = 0; $i < $len; $i++) {
			$word = mb_substr($str, $i, 1, 'UTF8');
			if (strlen($word) == 4) {
				$isFind = true;
				if ($replaceMent !== null) {
					$str = mb_substr($str, 0, $i, 'UTF8') . $replaceMent . mb_substr($str, $i + 1, $len - $i + 1, 'UTF8');
					$len = $len + mb_strlen($replaceMent, 'UTF8') - 1;
					$i = $i + mb_strlen($replaceMent, 'UTF8') - 1;
				}
			}
		}
		
		return $str;
	}
	
	public static function downImg($url, $path, $isGetInfo = false)
	{
		$namespace = 'diupin';
		$cfg = Lamb_Registry::get(CONFIG);	
		$image  = new AlibabaImage($cfg['top_client_cfg']['appkey'], $cfg['top_client_cfg']['secretKey']);
		$uploadPolicy = new UploadPolicy($namespace);   
		if ($path) {
			$month = date('Ym');
			$day = date('d');
			$uploadPolicy->dir = "$path/$month/$day/";
		}
		$uploadPolicy->name = Lamb_Utils::crc32FormatHex(microtime(true) . rand(0, 10000)); 
		
		$data = Lamb_Utils::fetchContentByUrl($url); //读本地图片内容到字符串中
		$res  = $image->uploadData($data, $uploadPolicy);
		
		//获取上传到顽兔上的图片信息 (宽|高)
		if ($isGetInfo) {
			if (!isset($res['dir']) || !isset($res['name']) || $res['dir'] == '' || $res['name'] == '') {
				return null;
			}
			
			return $image->getFileInfo($namespace, $res['dir'], $res['name']);
		}
		
		return $res['isSuccess'];
	}
	
	public static function log($data, $filename = '')
	{
		if (!$filename) {
			$filename = date('Ymd') . '.log';
		}
		
		$filename = '/ace/log/' . $filename;
		
		if (is_array($data)) {
			$data = print_r($data, true);
		}
		
		file_put_contents($filename, $data . "\n", FILE_APPEND);
	}	
}
