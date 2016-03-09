<div class="header">
	<div class="h-main clearfix">
		<div class="main-left fl clearfix">
			<div class="logo fl">
				<img src="<?php echo $this->mRuntimeThemeUrl;?>images/logo.png" alt="logo" />
			</div>
			<div class="nav fr">
				<a <?php if ($cate==0 ){?>class="active"<?php }?> href="<?php echo $this->mRouter->urlEx('index', 'index', array('cate' => 0));?>">全部</a>
				<a <?php if ($cate==1 ){?>class="active"<?php }?> href="<?php echo $this->mRouter->urlEx('index', 'index', array('cate' => 1));?>">视频</a>
				<a <?php if ($cate==2 ){?>class="active"<?php }?> href="<?php echo $this->mRouter->urlEx('index', 'index', array('cate' => 2));?>">图片</a>
				<a <?php if ($cate==3 ){?>class="active"<?php }?> href="<?php echo $this->mRouter->urlEx('index', 'index', array('cate' => 3));?>">段子</a>
			</div>

		</div>
		<div class="main-right fr clearfix">
			<div class="login fr">
				<ul>
					<li><a href="<?php echo $this->mRouter->urlEx('user', 'login', array());?>">登录</a></li>
					<li class="noBg"><a href="<?php echo $this->mRouter->urlEx('user', 'register', array());?>">注册</a></li>
				</ul>
			</div>
			</ul>
		</div>
	</div>
</div>