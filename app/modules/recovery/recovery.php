<?php
/**
*	PHPAdmin Template
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2022
*/

$site_title = 'Reset Password';
$site_desc = 'Reset password akun Anda';
$title = 'Reset Password';

$js[] = $config['base_url'] . 'public/vendors/jquery/jquery-3.3.1.min.js';
$js[] = $config['base_url'] . 'public/vendors/bootstrap/js/bootstrap.min.js';

$styles[] = $config['base_url'] . 'public/vendors/bootstrap/css/bootstrap.min.css';
$styles[] = $config['base_url'] . 'public/themes/modern/css/register.css';

$js[] = $config['base_url'] . 'public/vendors/jquery.pwstrength.bootstrap/pwstrength-bootstrap.min.js';
$js[] =	$config['base_url'] . 'public/themes/modern/js/password-meter.js';

switch ($_GET['action']) 
{
	default:
		action_notfound();
		
	case 'index':
	
		csrf_settoken();
		$error = false;
		
		$message = [];
		
		if (!empty($_POST['submit'])) 
		{
			// Cek isian form
			array_map('trim', $_POST);
			$form_error = validate_form();
			
			$message['status'] = 'error';
			if ($form_error) {
				$message['message'] = $form_error;
				$error = true;
			}
			
			// Submit data
			if (!$error) {
				$sql = 'SELECT * FROM user WHERE email = ?';
				$user = $db->query($sql, $_POST['email'])->getRowArray();
				
				$db->beginTrans();
				
				$db->delete('user_token', ['action' => 'recovery', 'id_user' => $user['id_user']]);
				$token = $app_auth->generateDbToken();
				$data_db['selector'] = $token['selector'];
				$data_db['token'] = $token['db'];
				$data_db['action'] = 'recovery';
				$data_db['id_user'] = $user['id_user'];
				$data_db['created'] = date('Y-m-d H:i:s');
				$data_db['expires'] = date('Y-m-d H:i:s', strtotime('+1 hour'));
				
				$insert_token = $db->insert('user_token', $data_db);
				
					// $save = true;
				if ($insert_token)
				{
					$url_token = $token['selector'] . ':' . $token['external'];
					$url = $config['base_url'].'recovery/reset?token='.$url_token;
					
					helper('email');
					require_once 'app/config/email.php';
					$email_config = new EmailConfig;
					$email_data = array('from_email' => $email_config->from
									, 'from_title' => 'Jagowebdev'
									, 'to_email' => $_POST['email']
									, 'to_name' => $_POST['email']
									, 'email_subject' => 'Reset Password'
									, 'email_content' => str_replace('{{url}}', $url, email_recovery_content() )
									, 'images' => ['logo_text' => BASEPATH . 'public/images/logo_text.png']
					);
					
					require_once('app/libraries/SendEmail.php');

					$emaillib = new \App\Libraries\SendEmail;
					$emaillib->init();
					$emaillib->setProvider($email_config->provider);
					$send_email =  $emaillib->send($email_data);
				
					if ($send_email['status'] == 'ok')
					{
						$db->commitTrans();
						
						$message['status'] = 'ok';
						$message['message'] = '
						Link reset password berhasil dikirim ke alamat email: <strong>'. $_POST['email'] . '</strong>, silakan gunakan link tersebut untuk mereset password Anda<br/></br>Biasanya, email akan sampai kurang dari satu menit, namun jika lebih dari lima menit email belum sampai, coba cek folder spam. Jika email benar benar tidak sampai, silakan hubungi kami di <a href="mailto:'.$config['email_support'].'" target="_blank">'.$config['email_support'].'</a>';
					} else {
						$message['message'] = 'Error: Link reset password gagal dikirim... <strong>' . $send_email['message'] . '</strong>';
						$error = true;
					}
				} else {
					$message['message'] = 'Gagal menyimpan data token, silakan hubungi kami di: <a href="mailto:'.$config['email_support'].'" target="_blank">'.$config['email_support'].'</a>';	
				}
			}
		}
		
		$page_content = 'views/form_recovery.php';
		
		if (!empty($_POST['submit']) && !$error) {
			$page_content = 'app/themes/modern/show-message-register.php';
		}
		
		include 'app/themes/modern/header-register.php';
		include $page_content;
		include 'app/themes/modern/footer-register.php';
		break;
	
	case 'reset': 

		$error = false;
		$message = [];
		
		if (empty($_GET['token'])) {
			$message['message'] = 'Token tidak ditemukan';
			$error = true;
		} else {
		
			@list($selector, $url_token) = explode(':', $_GET['token']);
			if (!$selector || !$url_token) {
				$message['message'] = 'Token tidak ditemukan';
				$error = true;
			}
		}
		
		if (!$error) {
			
			$sql = 'SELECT * FROM user_token
				WHERE selector = ?';
			$dbtoken = $db->query($sql, $selector)->getRowArray();
			
			if ($dbtoken) 
			{
				$error = false;
				if ($dbtoken['expires'] < date('Y-m-d H:i:s')) {
					$message['message'] = 'Link expired, silakan request <a href="'. $config['base_url'].'recovery">link reset password</a> yang baru';
					$error = true;
				} 
				else if (!$app_auth->validateToken($url_token, $dbtoken['token'])) {
					$message['message'] = 'Token invalid, silakan request <a href="'. $config['base_url'].'recovery">link reset password</a> yang baru';
					$error = true;
				}
				
			} else {
				$message['message'] = 'Token tidak ditemukan, silakan request <a href="'. $config['base_url'].'recovery">link reset password</a> yang baru';
				$error = true;
			}
		}
		

		if (!$error)
		{			
			if (!empty($_POST['submit'])) {
				// Cek isian form
				array_map('trim', $_POST);
				$form_error = validate_form_reset();

				if ($form_error) {
					$message['message'] = $form_error;
					$error = true;
				}
				
				// Submit data
				if (!$error) {
					
					$db->delete('user_token', ['selector' => $selector]);
					$update = $db->update('user', ['password' => password_hash($_POST['password'], PASSWORD_DEFAULT)] , ['id_user' => $dbtoken['id_user']]);
					if ($update) {
						$message['status'] = 'ok';
						$message['message'] = 'Password Anda berhasil diupdate, sekarang Anda dapat <a href="'.$config['base_url'].'login">Login</a> menggunakan password baru Anda';
					} else {
						$message['message'] = 'Password gagal diupdate, silakan coba dilain waktu, atau hubungi <a href="mailto:' . $config['contact_email'] . '" title="Hubungi kami via email">' . $config['contact_email'] . '</a>';
						$error = true;
					}		
					
				}
			}
		}
		
		$page_content = 'views/form_reset_password.php';
		
		if (!empty($_POST['submit']) && !$error) {
			$page_content = 'app/themes/modern/show-message-register.php';
		}
		
		if ($error) {
			$message['status'] = 'error';
		}
		
		include 'app/themes/modern/header-register.php';
		include $page_content;
		include 'app/themes/modern/footer-register.php';
}

function validate_form_reset() 
{
	$error = [];
	
	$validation = csrf_validation();
	// Cek CSRF token
	if ($validation['status'] == 'error') {
		return [$validation['message']];
	}
	
	$form_field = ['password' => 'Password'
				, 'password_confirm' => 'Confirm Password'
			];
	
	foreach ($form_field as $field => $field_title) {
		if (empty($_POST[$field])) {
			$error[] = 'Field ' . $field_title . ' harus diisi';
		}
	}
	
	if (!$error) {
		
		helper('form_requirement');
		if ($_POST['password'] !== $_POST['password_confirm']) {
			$error[] = 'Password dengan confirm password tidak sama';
		}
		
		$invalid = password_requirements($_POST['password']);
		if ($invalid) {
			$error = array_merge($error, $invalid);
		}
	}
	return $error;
}
	
function validate_form() 
{
	global $db;
	global $config;
	
	$error = [];
	
	$validation = csrf_validation();
	// Cek CSRF token
	if ($validation['status'] == 'error') {
		return [$validation['message']];
	}
	
	if (empty($_POST['email'])) {
		$error[] = 'Email harus diisi';
	} 
	else if (!strpos($_POST['email'], '@')) {
		$error[] = 'Format email tidak benar';
	}
	
	if (!$error) 
	{		
		$sql = 'SELECT * FROM user WHERE email = "' . $_POST['email'] . '"';
		$result = $db->query($sql)->getRowArray();
		if ($result) {
			if ($result['verified'] == 0) {
				$error[] = 'Email belum diaktifkan, silakan <a href="' . $config['base_url'] . 'resendlink" title="Kirim ulang link aktivasi">aktifkan disini</a>';
			}
		} else {
			$error[] = 'Email belum terdaftar, silakan <a href="' . $config['base_url'] . 'register" title="Register Akun">register akun disini</a>';
		}
	}
	return $error;
}