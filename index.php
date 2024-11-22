<?php
/**
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2022
*/

session_start();
/** ERROR HANDLER */
include 'system/error_handler.php';
include 'app/config/constant.php';

if (ENVIRONMENT == 'production') {
	error_reporting( 0 );
} else {
	error_reporting( E_ALL );
}

include 'app/config/config.php';
include 'app/config/database.php';
include 'app/includes/functions.php';
include 'app/libraries/Auth.php';
include 'system/libraries/database/'.strtolower($database['driver']).'.php';
include 'system/functions.php';
include 'system/libraries/csrf.php';
include 'system/libraries/rbac.php';

define ('BASE_URL', $config['base_url']);
define ('BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define ('THEME_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'public/themes/' . $config['theme'] . '/');
define ('THEME_URL', $config['base_url'] . 'public/themes/' . $config['theme'] . '/');
define ('BASEPATH', __DIR__ . '/');

date_default_timezone_set('Asia/Jakarta');

$db = new Database();

$app_auth = new Auth();
$app_auth->checkLogin();

// CSRF
if ($csrf_token['enable']) {
	if ($csrf_token['auto_check']) {
		csrf_verify();
	}
	csrf_settoken();
}

// Token for login form
$is_loggedin = $app_auth->isLoggedIn();

if (!$is_loggedin) {
	$form_token = $app_auth->generateFormToken();
}

// Module
$default_module = $config['default_module'];
// echo '<pre>'; print_r($_SESSION['user']); die;
if ($is_loggedin) {
	$query = $db->query('SELECT nama_module 
					FROM module 
					LEFT JOIN user USING(id_module)
					WHERE id_module = ? '
					, $_SESSION['user']['default_module']['id_module']
				)->row();
	$default_module = $query['nama_module'];
}

$nama_module = !empty($_GET['module']) ? $_GET['module'] : $default_module;

// Module Detail
$current_module = $db->query('SELECT * FROM module WHERE nama_module = ? ', $nama_module)->row();
if (!$current_module) {
	require_once ('system/error_handler.php');
	appErrorHandler('Module ' . $nama_module . ' tidak ditemukan di database');
	die;
}

// Login ? Yes, No, Restrict
if ($current_module['login'] == 'Y') {
	login_required();
} else if ($current_module['login'] == 'R') {
	login_restricted();
}

// Setting logo
$sql = 'SELECT * FROM setting WHERE type="app"';
$query = $db->query($sql)->result();
foreach($query as $val) {
	$setting_app[$val['param']] = $val['value'];
}

// Setting tampilan
$layout_loaded = false;
if ($is_loggedin){
	$user_setting = $db->query('SELECT * FROM setting_user WHERE id_user = ?', $_SESSION['user']['id_user'])
						->row();
	if ($user_setting) {
		$app_layout = json_decode($user_setting['param'], true);
		$layout_loaded = true;
	}
}

if(!$layout_loaded ) {
	$query = $db->query('SELECT * FROM setting WHERE type="layout"')->result();
	foreach ($query as $val) {
		$app_layout[$val['param']] = $val['value'];
	}
}

// Breadcrumb
$breadcrumb = [];
$breadcrumb['Home'] = $config['base_url'];
$breadcrumb[$current_module['judul_module']] = module_url();

// Role of all users for the current module 
$query = $db->query('SELECT * FROM module_role WHERE id_module = ? ', $current_module['id_module'])->result();
$module_role = [];
foreach ($query as $val) {
	$module_role[$val['id_role']] = $val;
}
// echo '<pre>'; print_r($_SESSION['user']); die;
// List action granted for current user to current module
$list_action = [];
$module_exception = ['login', 'register', 'recovery', 'resendlink'];
if ($is_loggedin && !in_array($current_module['nama_module'], $module_exception)) {
	
	if ($module_role) {
		foreach ($_SESSION['user']['role'] as $id_role => $arr) 
		{
			if (key_exists($id_role, $module_role)) {
				$list_action = $module_role[$id_role];
			}
		}
		
		if ($current_module['nama_module'] != 'login' ) {
			// echo '<pre>'; print_r($module_role); die;
			if (!$list_action) {
				// echo 'Anda tidak diperbolehkan mengakses halaman ' . $current_module['judul_module']; die;
				$current_module['nama_module'] = 'error';
				load_view('views/error.php', ['status' => 'error', 'message' => 'Anda tidak berhak mengakses halaman ini (' . $current_module['judul_module'] . ')']);
			}
		}
	} else {
		$current_module['nama_module'] = 'error';
		load_view('views/error.php', ['status' => 'error', 'message' => 'Anda tidak berhak mengakses halaman ini (' . $current_module['judul_module'] . '). Role untuk module ini belum diatur']);
	}
}

// Display page
if ($current_module) {
	
	if ($current_module['id_module_status'] == 3) {
		exit_error('Module tidak aktif');
	} elseif ($current_module['id_module_status'] == 2) {
		exit_error('Module dalam pengembangan. Hanya user dengan Role Developer yang dapat mengakses halaman ini');
	}
	
	$module_file = 'app/modules/' . $current_module['nama_module'] . '/' . $current_module['nama_module'] . '.php';
	
	if (file_exists($module_file)) 
	{
		if (empty($_GET['action'])) {
			$_GET['action'] = 'index';
		}
		
		if ($check_role_action['enable_global']) {
			if ($_GET['action'] == 'add') {
				if ($list_action['create_data'] == 'no') {
					exit_error('Role Anda tidak diperkenankan untuk menambah data');
				}
			} else if ($_GET['action'] == 'edit') {
				if ($list_action['update_data'] == 'no') {
					exit_error('Role Anda tidak diperkenankan untuk mengubah data');
				}
			} else {
				if (!empty($_POST['delete'])) {
					if ($list_action['delete_data'] == 'no') {
						exit_error('Role Anda tidak diperkenankan untuk menghapus data');
					}
				}
			}
		}
		
		include( $module_file );
	} else {
		$content = 'File module <strong>' . $module_file . '</strong> tidak ditemukan...';
		exit_error($content);
	}
	
} else {
	exit_error('Module <strong>' . $nama_module . '</strong> tidak terdaftar di database');
}