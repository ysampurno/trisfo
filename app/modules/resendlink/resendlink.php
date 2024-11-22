<?php
/**
*	PHPAdmin Template
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2022
*/

$site_title = 'Kirim Ulang Link Aktivasi';
$site_desc = 'Kirim ulang link aktivasi akun Anda';
$title = 'Kirim Ulang Link Aktivasi';

$js[] = $config['base_url'] . 'public/vendors/jquery/jquery-3.3.1.min.js';
$js[] = $config['base_url'] . 'public/vendors/bootstrap/js/bootstrap.min.js';

$styles[] = $config['base_url'] . 'public/vendors/bootstrap/css/bootstrap.min.css';
$styles[] = $config['base_url'] . 'public/themes/modern/css/register.css';

switch ($_GET['action']) 
{
	default:
		action_notfound();
		
	case 'index':
	
		csrf_settoken();
		$error = false;
		
		$message = [];
		
		helper('registrasi');
		$setting_register = get_setting_registrasi();
		if ($setting_register['metode_aktivasi'] != 'email') {
			$message['status'] = 'error';
			$message['message'] = 'Metode aktivasi yang digunakan bukan melalui email. Untuk mengaktifkan akun, silakan hubungi administrator di: <a href="mailto:' . $config['email_support'] . '" title="Hubungi Support">' . $config['email_support'] . '</a>';
		
		} else {
		
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
					
					$db->delete('user_token', ['action' => 'activation', 'id_user' => $user['id_user']]);

					$token = $app_auth->generateDbToken();
					$data_db['selector'] = $token['selector'];
					$data_db['token'] = $token['db'];
					$data_db['action'] = 'activation';
					$data_db['id_user'] = $user['id_user'];
					$data_db['created'] = date('Y-m-d H:i:s');
					$data_db['expires'] = date('Y-m-d H:i:s', strtotime('+1 hour'));
					
					$insert_token = $db->insert('user_token', $data_db);
					
						// $save = true;
					if ($insert_token)
					{
						helper('email');
						$url_token = $token['selector'] . ':' . $token['external'];
						$url = $config['base_url'].'register/confirm?token='.$url_token;
						$email_content = str_replace('{{NAME}}'
													, $user['nama']
													, email_resendlink_content()
												);
												
						$email_content = str_replace('{{url}}', $url, $email_content);
						
						require_once 'app/config/email.php';
						$email_config = new EmailConfig;
						$email_data = array('from_email' => $email_config->from
										, 'from_title' => 'Jagowebdev'
										, 'to_email' => $_POST['email']
										, 'to_name' => $user['nama']
										, 'email_subject' => 'Link Aktivasi Akun'
										, 'email_content' => $email_content
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
							Link aktivasi berhasil dikirim ke alamat email: <strong>'. $_POST['email'] . '</strong>, silakan gunakan link tersebut untuk aktivasi akun Anda<br/></br>Biasanya, email akan sampai kurang dari satu menit, namun jika lebih dari lima menit email belum sampai, coba cek folder spam. Jika email benar benar tidak sampai, silakan hubungi kami di <a href="mailto:'.$config['email_support'].'" target="_blank">'.$config['email_support'].'</a>';
						} else {
							$message['message'] = 'Error: Link aktivasi gagal dikirim... <strong>' . $send_email['message'] . '</strong>';
							$error = true;
						}
					} else {
						$message['message'] = 'Gagal menyimpan data token, silakan hubungi kami di: <a href="mailto:'.$config['email_support'].'" target="_blank">'.$config['email_support'].'</a>';
						$error = true;
					}
					
					if ($error) {
						$db->rollbackTrans();
					}
				}
			}
		}
		
		$page_content = 'views/form.php';
		
		if ($setting_register['metode_aktivasi'] != 'email' || (!$error && !empty($_POST['submit'])) ) {
			$page_content = 'app/themes/modern/show-message-register.php';
		}
		
		include 'app/themes/modern/header-register.php';
		include $page_content;
		include 'app/themes/modern/footer-register.php';
		break;
}
	
function validate_form() 
{
	global $db;
	global $config;
	
	$error = false;
	
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
			if ($result['verified'] == 1) {
				$error[] = 'Akun sudah pernah diaktifkan, silakan <a href="' . $config['base_url'] . 'login" title="Login">login</a> ke akun Anda';
			}
		} else {
			$error[] = 'Email belum terdaftar, silakan <a href="' . $config['base_url'] . 'register" title="Register Akun">register akun disini</a>';
		}
	}
	return $error;
}