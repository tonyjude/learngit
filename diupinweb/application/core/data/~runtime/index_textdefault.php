<!DOCTYPE html>
<html>

	<head>
		<meta property="qc:admins" content="214022765764150166375">
		<meta name="keywords" content="<?php echo $this->mSiteCfg['site']['keywords'];?>" />
		<meta name="description" content="<?php echo $this->mSiteCfg['site']['description'];?>" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
		<link href="<?php echo $this->mRuntimeThemeUrl;?>css/main.css" type="text/css" rel="stylesheet" />
		<link href="<?php echo $this->mRuntimeThemeUrl;?>css/myzii.css" type="text/css" rel="stylesheet" />
		<script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.8.0.js" type="text/javascript" charset="utf-8"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				$(window).scroll(function() {
					var scroTop = $(window).scrollTop();
					var bodyHeight = $(window).height();
					if (scroTop >= bodyHeight) {
						$(".backtop").css('display', 'block');
					} else {
						$(".backtop").css('display', 'none');
					}
				})
			})
		</script>
		<title>丢品网</title>
	</head>

	<body>
		<div class="wraper">
			<?php include $this->mView->load("head");?>
			<div class="center clearfix">
				<div class="center-left fl">
					<div class="itemBox">
						<!--id,type,time,description,url,nickname,avatar,content_type,share_num,praise_num,
							1视频
							2图片
							3段子
							4gif
						-->
						<?php Lamb_View_Tag_List::main(array(
				'sql' => ''.$indexSql.'',
				'include_union' => null,
				'prepare_source' => null,
				'is_page' => true,
				'page' => $page,
				'pagesize' => $pagesize,
				'offset' => null,
				'limit' => null,
				'cache_callback' => $this->mCacheCallback,
				'cache_time' => $this->mCacheTime,
				'cache_type' => $this->mCacheType,
				'cache_id_suffix' => '',
				'is_empty_cache' => false,
				'id' => 'list',
				'empty_str' => '',
				'auto_index_prev' => 0,
				'db_callback' => null,
				'show_result_callback' => create_function('$item,$index','return str_replace("#autoIndex#",$index,\'
						<div class="item">
							<div class="item-heading clearfix">
								<div class="author fl clearfix">
									<!--<a href="" class="author-pic"><img src="<?php echo $this->mRuntimeThemeUrl;?>images/default_pic.png" alt="默认头像头像" /></a>-->
									<a href="" class="author-pic"><img src="\'.$item[\'avatar\'].\'" alt="头像" /></a>
									<a href="" class="author-nicname">\'.$item[\'nickname\'].\'</a>
								</div>
								<div class="report fr">
									<a href="">举报</a>
								</div>
							</div>
							<div class="item-body">
								<p class="title">\'.$item[\'description\'].\'</p>
								\'.(indexControllor::chechkType($item[\'content_type\'],$item[\'url\'])).\'
							</div>
							<div class="item-footer clearfix">
								<div class="fankui fl clearfix">
									<a href="" class="zan"><span>\'.$item[\'praise_num\'].\'</span></a>
									<a href="" class="comments"><span>\'.$item[\'share_num\'].\'</span></a>
									<a href="" class="collection"></a>
									<a href="" class="download"></a>
								</div>
								<div class="share fr clearfix">
									<span class="fl" style="text-align: right;">分享到：</span>
									<div class="jiathis_style fr">
										<a class="jiathis_button_qzone"></a>
										<a class="jiathis_button_tsina"></a>
										<a class="jiathis_button_tqq"></a>
										<a class="jiathis_button_weixin"></a>
										<a class="jiathis_button_renren"></a>
										<a class="jiathis_button_xiaoyou"></a>
										<a href="http://www.jiathis.com/share" class="jiathis jiathis_txt jtico jtico_jiathis" target="_blank"></a>
										<a class="jiathis_counter_style"></a>
									</div>
									<script type="text/javascript" src="http://v3.jiathis.com/code/jia.js" charset="utf-8"></script>
								</div>
							</div>
						</div>
						\');')
			))?>
					</div>
					<?php Lamb_View_Tag_Page::page(array(
			'page_num'		=>	5,
			'page_style'	=>	1,
			'listid'		=>	'list',
			'page_start_html'=>	'
					<div class="page clearfix">
						<a href="'.$firstPageUrlTemplate.'" class="p-first-last">首页</a> ',
			'page_end_html'	=>	'
						<a href="'.$nextPageUrlTemplate.'" class="p-prev-next">&gt;</a>
						<a href="'.$lastPageUrlTemplate.'" class="p-first-last">尾页</a>
					</div>
					',
			'more_html'		=>	'',
			'focus_html'	=>	'<a class="current">#page#</a>',
			'nofocus_html'	=>	'<a class="p-index" href="'.$pageUrlTemplate.'">#page#</a>',
			'max_page_count' => 0,
			'page' => null,
			'pagesize' => null,
			'data_num' => null
		))?>
				</div>
				<div class="center-right fr">
					<div class="ewm">
						<img src="<?php echo $this->mRuntimeThemeUrl;?>images/qr.png" alt="二维码" />
						<p>扫一扫，手机观看丢品</p>
					</div>
				</div>
			</div>
			<div class="backtop">
				<a href="#" class="gotop"></a>
			</div>
			<!--footer-->
			<?php include $this->mView->load("foot");?>
		</div>
	</body>
	<script type="text/javascript" src="<?php echo $this->mSiteCfg['site']['root'];?>api/base.js"></script>

</html>