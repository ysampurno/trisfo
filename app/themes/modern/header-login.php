<!DOCTYPE HTML>
<html lang="en">
<title><?=$site_title?></title>
<meta name="descrition" content="<?=$site_title?>"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="<?=$config['base_url'] . 'public/images/favicon.png?r='.time()?>" />
<link rel="stylesheet" type="text/css" href="<?=$config['base_url'] . 'public/vendors/bootstrap/css/bootstrap.min.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config['base_url'] . 'public/vendors/font-awesome/css/font-awesome.min.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=THEME_URL . 'css/login.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=THEME_URL . 'css/login-header.css?r='.time()?>"/>
<?php
if (!empty($styles)) {
	foreach($styles as $file) {
		echo '<link rel="stylesheet" type="text/css" href="'.$file.'?r='.time().'"/>';
	}
}
?>
<style>
.login-header {
    background-color: <?=$setting_app['background_logo'];?>
}
</style>
<script type="text/javascript" src="<?=$config['base_url'] . 'public/vendors/jquery/jquery.min.js?r='.time()?>"></script>
<script type="text/javascript" src="<?=$config['base_url'] . 'public/vendors/bootstrap/js/bootstrap.min.js?r='.time()?>"></script>
<?php
if (!empty($js)) {
	foreach($js as $file) {
		echo '<script type="text/javascript" src="'.$file.'?r='.time().'"></script>';
	}
}
?>
</html>
<body>
	<div class="background"></div>
	<div class="backdrop"></div>
	<div class="login-container">
		<div class="login-header">
			<div class="logo">
				<img src="<?php echo $config['base_url'] . 'public/images/' . $setting_app['logo_login']?>?r=<?=time()?>">
			</div>
			
			<?php if (!empty($desc)) {
				echo '<p>' . $desc . '</p>';
			}?>
		</div>