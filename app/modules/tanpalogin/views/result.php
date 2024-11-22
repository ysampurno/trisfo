<!DOCTYPE HTML>
<html lang="en">
<title><?=$artikel['judul_artikel']?></title>
<meta name="descrition" content="<?=$artikel['meta_description']?>"/>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

<link rel="shortcut icon" href="<?=BASE_URL?>public/images/favicon.png" />
<link rel="stylesheet" type="text/css" href="<?=BASE_URL?>public/vendors/font-awesome/css/all.css?r=<?=time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=BASE_URL?>public/vendors/bootstrap/css/bootstrap.min.css?r=<?=time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=BASE_URL?>public/themes/modern/css/bootstrap-custom.css?r=<?=time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=BASE_URL?>public/themes/modern/css/tanpalogin.css?r=<?=time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=BASE_URL?>public/vendors/overlayscrollbars/OverlayScrollbars.min.css?r=<?=time()?>"/>
<link rel="stylesheet" id="font-switch" type="text/css" href="<?=BASE_URL . 'public/themes/modern/css/fonts/'.$app_layout['font_family'].'.css?r='.time()?>"/>
<link rel="stylesheet" id="font-size-switch" type="text/css" href="<?=BASE_URL . 'public/themes/modern/css/fonts/font-size-'.$app_layout['font_size'].'.css?r='.time()?>"/>

<script type="text/javascript" src="<?=BASE_URL?>public/vendors/jquery/jquery.min.js"></script>
<script type="text/javascript" src="<?=BASE_URL?>public/themes/modern/js/site.js?r=<?=time()?>"></script>
<script type="text/javascript" src="<?=BASE_URL?>public/vendors/bootstrap/js/bootstrap.min.js?r=<?=time()?>"></script>
<script type="text/javascript" src="<?=BASE_URL?>public/vendors/overlayscrollbars/jquery.overlayScrollbars.min.js?r=<?=time()?>"></script>
<script type="text/javascript">
	var base_url = "<?=BASE_URL?>";
</script>

</head>
<body>
	<div class="site-container">
	<header class="shadow-sm">
		<div class="menu-wrapper wrapper clearfix">
			<a href="#" id="mobile-menu-btn" class="show-mobile">
				<i class="fa fa-bars"></i>
			</a>
			<div class="nav-left">
				<a href="" class="logo-header" title="Jagowebdev">
					<img src="<?=BASE_URL?>public/images/logo_login.png" alt="Jagowebdev"/>
				</a>
			</div>
			<nav class="nav-right nav-header">
				<ul class="main-menu">
					<li class="menu">
						<a class="depth-0" href="<?=BASE_URL?>">
							<i class="menu-icon fas fa-home"></i>Home </a>
					</li>
					<li class="menu">
						<a class="depth-0" href="<?=BASE_URL?>"><i class="menu-icon fas fa-sign-in-alt"></i>Admin</a>
					</li>
				</ul>
			</nav>
			<div class="clearfix"></div>
		</div>
	</header>
	<div class="page-container">
		<div class="title-container shadow-lg">
			<div class="wrapper wrapper-post-single">
				<h1 class="post-title"><?=$artikel['judul_artikel']?></h1>
				<div class="clearfix post-meta-single">
					<p class="post-description"><?=$artikel['meta_description']?></p>
				</div>
			</div>
		</div>
		<div class="wrapper">
			<div class="row article-single-container">
				<div class="card-body">
					<?=$artikel['konten']?>
				</div>
			</div>
		</div>
	</div>
	<footer>
		<div class="footer-desc">
		<div class="wrapper">
			<div class="row mb-0">
				<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 mb-2">
					<h2 class="widget-title">Contact us</h2>
					<ul class="list"><li><i class="fa fa-envelope me-2"></i>Email: support@jagowebdev.com</li>
					<li><i class="fas fa-file-signature me-2"></i><a target="_blank" href="https://jagowebdev.com/members/contact">Via Contac form</a></li>
					</ul>
				</div>
				<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 mb-2">
					<h2 class="widget-title">About</h2>
					<p>Pusat belajar Web Development terbaik, dengan berbagai materi berkualitas</p>
					<ul class="list">
						<li><i class="fab fa-facebook-square me-2"></i><a href="https://web.facebook.com/JagoWebDev" target="_blank">facebook</a></li>
					</ul>
				</div>
				<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4">
					<h2 class="widget-title">More Info</h2>
					<ul class="list">
						<li><i class="fa fa-user-plus me-2"></i><a href="https://jagowebdev.com/members/membership" target="_blank">Premium Member</a></li>
						<li><i class="fas fa-external-link-alt me-2"></i><a href="http://jagowebdev.com/artikel/" target="_blank">Artikel Blog</a></li>
					</ul>
				</div>
			</div>
		</div>
		</div>
		<div class="footer-menu-container">
			<div class="wrapper clearfix">
				<div class="nav-left">Copyright &copy; 2021 <a title="Jagowebdev" href="https://jagowebdev.com">Jagowebdev</a>
				</div>
				<nav class="nav-right nav-footer">
					<ul class=footer-menu>
						<li class="menu">
							<a class="depth-0" href="<?=BASE_URL?>">Home</a>
						</li>
						<li class="menu">
							<a class="depth-0" href="tremofuser">Term of Use</a>
						</li>
					</ul>
				</nav>
			</div>
		</div>
	</footer>
	</div><!-- site-container -->
</body>
</html>