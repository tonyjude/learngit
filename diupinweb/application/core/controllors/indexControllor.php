<?php
class indexControllor extends Core_Controllor
{
	public $indexSql;
	public function __construct()
	{
		parent::__construct();
	}

	public function getControllorName()
	{
		return 'index';
	}

	/**
	 * 首页
	 */
	public function indexAction()
	{
		$cate = trim($this->mRequest->cate);
		$page = $this->mRequest->p;
		$page = isset($page) ? $page : 1;
		$pagesize = 10;

		$indexSql = "select * from diupin where 1=1";
		if (!Lamb_Utils::isInt($cate, TRUE)) {
			$cate = 0;
		}
		if ($cate > 0) {
			$indexSql .= " and type = $cate";
		}
		$indexSql .= ' order by id desc';

		$firstPageUrlTemplate = $this->mRouter->urlEx('index', 'index', array('p' => 1, 'cate' => $cate));
		$prevPageUrlTemplate = $this->mRouter->urlEx('index', 'index', array('p' => '#prevPage#', 'cate' => $cate), FALSE);
		$nextPageUrlTemplate = $this->mRouter->urlEx('index', 'index', array('p' => '#nextPage#', 'cate' => $cate), FALSE);
		$lastPageUrlTemplate = $this->mRouter->urlEx('index', 'index', array('p' => '#lastPage#', 'cate' => $cate), FALSE);
		$pageUrlTemplate = $this->mRouter->urlEx('index', 'index', array('p' => '#page#', 'cate' => $cate), FALSE);
		include $this->autoload();
	}

	/**
	 * 根据类型，判断返回视频或者图片
	 * 1视频
		2图片
		3段子
		4gif
	 */
	public static function chechkType($type, $url)
	{
		if ($type == 1) {
			return '<div id="video"></div>';
		} else if ($type == 2 || $type == 4) {
			return '<p><img src="' . $url . '" alt="图片" /></p>';
		}
	}

}
?>