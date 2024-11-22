<?php

login_restricted();

$site_title = 'Login Into Your Account';
include 'functions.php';
// helper('login');

switch ($_GET['action']) 
{
	default:
		action_notfound();
		
	case 'logout':
		
		delete_auth_cookie($_SESSION['user']['id_user']);
		session_destroy();
		header('location:'. BASE_URL); 
		
	case 'index' :
		csrf_settoken();
		
		if (isset($_POST['submit'])) 
		{
			$validation = csrf_validation();
			if ($validation['status'] == 'ok') {
				$message = check_login();
			} else {
				$message = $validation['message'];
			}
		}
		
		global $config;
		global $site_title;

		// Theme header
		include BASE_PATH . 'app/themes/modern/header-login.php';

		include 'views/form.php';
		include BASE_PATH . 'app/themes/modern/footer-login.php';
}