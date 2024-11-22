<?php
/**
*	PHP Admin Template
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2022
*/

$js[] = BASE_URL . 'public/vendors/spectrum/spectrum.min.js';
$js[] = BASE_URL . 'public/themes/modern/js/setting-logo.js';
// $js[] = BASE_URL . 'public/themes/modern/js/setting-web.js';
$js[] = BASE_URL . 'public/themes/modern/js/image-upload.js';
$styles[] = BASE_URL . 'public/vendors/spectrum/spectrum.css';
$styles[] = BASE_URL . 'public/themes/modern/css/login-header.css';
$styles[] = BASE_URL . 'public/themes/modern/css/setting-app.css';

switch ($_GET['action']) 
{
	default: 
		action_notfound();
		
	// INDEX 
	case 'index':

		if (!empty($_POST['submit'])) 
		{
			if ($list_action['update_data'] == 'all')
			{
				$form_errors = validate_form();
				
				$sql = 'SELECT * FROM setting WHERE type="app"';
				$query = $db->query($sql)->result();
				foreach($query as $val) {
					$curr_db[$val['param']] = $val['value'];
				}
		
				if (!$_FILES['logo_app']['name']) {
					if ($curr_db['logo_app'] == '') {
						$form_errors['logo_app'] = 'Logo aplikasi belum dipilih';
					}
				} 
				
				if ($form_errors) {
					$data['msg']['status'] = 'error';
					$data['msg']['message'] = $form_errors;
				} else {
					// Logo Login
					$logo_login = $curr_db['logo_login'];
					$path = $config['images_path'];
					if ($_FILES['logo_login']['name']) 
					{
						//old file
						if ($curr_db['logo_login']) {
							$del = delete_file($path . $curr_db['logo_login']);
							if (!$del) {
								$data['msg']['status'] = 'error';
								$data['msg']['message'] = 'Gagal menghapus logo login lama';
							}
						}
						
						$logo_login = upload_file($path, $_FILES['logo_login']);
					}
					
					// Logo App
					$logo_app = $curr_db['logo_app'];
					if ($_FILES['logo_app']['name']) 
					{
						//old file
						if ($curr_db['logo_app']) {
							$del = delete_file($path . $curr_db['logo_app']);
							if (!$del) {
								$data['msg']['status'] = 'error';
								$data['msg']['message'] = 'Gagal menghapus gambar logo aplikasi lama';
							}
						}
						
						$logo_app = upload_file($path, $_FILES['logo_app']);
					}
					
					// Favicon
					$favicon = $curr_db['favicon'];
					if ($_FILES['favicon']['name']) 
					{
						//old file
						if ($curr_db['favicon']) {
							$del = delete_file($path . $curr_db['favicon']);
							if (!$del) {
								$data['msg']['status'] = 'error';
								$data['msg']['message'] = 'Gagal menghapus logo favicon lama';
							}
						}
						
						$favicon = upload_file($path, $_FILES['favicon']);
					}
					
					// Register
					$logo_register = $curr_db['logo_register'];
					if ($_FILES['logo_register']['name']) 
					{
						//old file
						if ($curr_db['logo_register']) {
							$del = delete_file($path . $curr_db['logo_register']);
							if (!$del) {
								$data['msg']['status'] = 'error';
								$data['msg']['message'] = 'Gagal menghapus logo register lama';
							}
						}
						
						$logo_register = upload_file($path, $_FILES['logo_register']);
					}
					
					if ($logo_login && $logo_app && $favicon && $logo_register) 
					{
						$data_db[] = ['type' => 'app', 'param' => 'logo_login', 'value' => $logo_login];
						$data_db[] = ['type' => 'app', 'param' => 'logo_app', 'value' => $logo_app];
						$data_db[] = ['type' => 'app', 'param' => 'footer_login', 'value' => htmlentities($_POST['footer_login'])];
						$data_db[] = ['type' => 'app', 'param' => 'btn_login', 'value' => $_POST['btn_login']];
						$data_db[] = ['type' => 'app', 'param' => 'footer_app', 'value' => htmlentities($_POST['footer_app'])];
						$data_db[] = ['type' => 'app', 'param' => 'background_logo', 'value' => $_POST['background_logo']];
						$data_db[] = ['type' => 'app', 'param' => 'judul_web', 'value' => htmlentities($_POST['judul_web'])];
						$data_db[] = ['type' => 'app', 'param' => 'deskripsi_web', 'value' => htmlentities($_POST['deskripsi_web'])];
						$data_db[] = ['type' => 'app', 'param' => 'favicon', 'value' => $favicon];
						$data_db[] = ['type' => 'app', 'param' => 'logo_register', 'value' => $logo_register];
						
						$db->beginTrans();
						$db->delete('setting WHERE type="app"');
						$result = $db->insertBatch('setting', $data_db);
						$query = $db->completeTrans();
						
						if ($query) {
							$file_name = THEME_PATH . 'css/login-header.css';
							$css = '.login-header {background-color: '.$_POST['background_logo'].';}.edit-logo-login-container {background: '.$_POST['background_logo'].';}';
							if (file_exists($file_name)) {
								file_put_contents($file_name, $css);
							}
							$data['msg']['status'] = 'ok';
							$data['msg']['message'] = 'Data berhasil disimpan';
						} else {
							$data['msg']['status'] = 'error';
							$data['msg']['message'] = 'Data gagal disimpan';
						}
						
					} else {
						$data['msg']['status'] = 'error';
						$data['msg']['message'] = 'Error saat memperoses gambar';
					}
				}
				
			} else {
				$data['msg']['status'] = 'error';
				$data['msg']['message'] = 'Role anda tidak diperbolehkan melakukan perubahan';
			}
		}
		
		$sql = 'SELECT * FROM setting WHERE type="app"';
		$query = $db->query($sql)->result();
		foreach($query as $val) {
			$data[$val['param']] = $val['value'];
		}

		$data['title'] = 'Edit ' . $current_module['judul_module'];
		load_view('views/form.php', $data);
}

function validate_form() 
{
	require_once('app/libraries/FormValidation.php');
	$validation = new FormValidation();
	$validation->setRules('footer_app', 'Footer Aplikasi', 'trim|required');
	$validation->setRules('background_logo', 'Background Logo', 'trim|required');
	$validation->setRules('judul_web', 'Judul Website', 'trim|required');
	$validation->setRules('deskripsi_web', 'Deskripsi Web', 'trim|required');
	
	$validation->validate();
	$form_errors =  $validation->getMessage();
					
	// $form_errors = [];
	if ($_FILES['logo_login']['name']) {
		
		$file_type = $_FILES['logo_login']['type'];
		$allowed = ['image/png', 'image/jpeg', 'image/jpg'];
		
		if (!in_array($file_type, $allowed)) {
			$form_errors['logo_login'] = 'Tipe file harus ' . join(', ', $allowed);
		}
		
		if ($_FILES['logo_login']['size'] > 300 * 1024) {
			$form_errors['logo_login'] = 'Ukuran file maksimal 300Kb';
		}
		
		$info = getimagesize($_FILES['logo_login']['tmp_name']);
		if ($info[0] < 20 || $info[1] < 20) { //0 Width, 1 Height
			$form_errors['logo_login'] = 'Dimensi logo login minimal: 20px x 20px, dimensi anda ' . $info[0] . 'px x ' . $info[1] . 'px';
		}
	}
	
	if ($_FILES['logo_app']['name']) {
		
		$file_type = $_FILES['logo_app']['type'];
		$allowed = ['image/png', 'image/jpeg', 'image/jpg'];
		
		if (!in_array($file_type, $allowed)) {
			$form_errors['logo_app'] = 'Tipe file harus ' . join(', ', $allowed);
		}
		
		if ($_FILES['logo_app']['size'] > 300 * 1024) {
			$form_errors['logo_app'] = 'Ukuran file maksimal 300Kb';
		}
		
		$info = getimagesize($_FILES['logo_app']['tmp_name']);
		if ($info[0] < 20 || $info[1] < 20) { //0 Width, 1 Height
			$form_errors['logo_app'] = 'Dimensi logo aplikasi minimal: 20px x 20px, dimensi anda ' . $info[0] . 'px x ' . $info[1] . 'px';
		}
	}
	
	if ($_FILES['logo_register']['name']) {
		
		$file_type = $_FILES['logo_register']['type'];
		$allowed = ['image/png', 'image/jpeg', 'image/jpg'];
		
		if (!in_array($file_type, $allowed)) {
			$form_errors['logo_register'] = 'Tipe file harus ' . join(', ', $allowed);
		}
		
		if ($_FILES['logo_register']['size'] > 300 * 1024) {
			$form_errors['logo_register'] = 'Ukuran file maksimal 300Kb';
		}
		
		$info = getimagesize($_FILES['logo_register']['tmp_name']);
		if ($info[0] < 20 || $info[1] < 20) { //0 Width, 1 Height
			$form_errors['logo_register'] = 'Dimensi logo aplikasi minimal: 20px x 20px, dimensi anda ' . $info[0] . 'px x ' . $info[1] . 'px';
		}
	}
	
	if ($_FILES['favicon']['name']) {
		
		$file_type = $_FILES['favicon']['type'];
		$allowed = ['image/png'];
		
		if (!in_array($file_type, $allowed)) {
			$form_errors['favicon'] = 'Tipe file harus ' . join(', ', $allowed) . ' tipe file Anda: ' . $file_type;
		}
		
		if ($_FILES['favicon']['size'] > 300 * 1024) {
			$form_errors['favicon'] = 'Ukuran file maksimal 300Kb';
		}
	}
	
	return $form_errors;
}