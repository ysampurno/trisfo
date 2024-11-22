<?php
/**
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2022
*/

// Template

function load_view($view, $data = [], $view_only = false, $view_module = false) 
{
	global $config;
	global $is_loggedin;
	global $current_module;
	global $breadcrumb;
	global $db;
	global $app_layout;
	
	extract($data);
	ob_start();
		
	if (!$view_module) {
		$view_module = $current_module['nama_module'] ;
	}
	
	if ($view_only) {
		include BASE_PATH . 'app/modules/' . $current_module['nama_module'] . '/' . $view;
		return ob_get_clean();
	}
	
	if (!$view_only) 
	{
		$theme_header = BASE_PATH . 'app/themes/' . $config['theme'] . '/header.php';
		include backup($theme_header);
	}

	include BASE_PATH . 'app/modules/' . $view_module . '/' . $view;
	
	if (!$view_only) {
		$theme_footer = BASE_PATH . 'app/themes/' . $config['theme'] . '/footer.php';
		include backup($theme_footer);
	}
	
	exit();
}

function set_value($field_name, $default = '') 
{
	$request = array_merge($_GET, $_POST);
	$search = $field_name;
	if (!$search) {
		return $default;
	}

	// If Array
	$is_array = false;
	
	if (strpos($search, '[')) {
		$is_array = true;
		$exp = explode('[', $field_name);
		$field_name = $exp[0];
		
	}
	
	if (isset($request[$field_name])) {
		
		if ($is_array) {
			
			$exp_close = explode(']', $exp[1]);
			$index = $exp_close[0];

			return $request[$field_name][$index];
		}
		return $request[$field_name];
	}
	return $default;
}

// Error
function exit_error($content) 
{
	if (ENVIRONMENT == 'production') {
		include BASE_PATH . 'system/views/error_production.php';
	} else {
		include BASE_PATH . 'system/views/error.php';
	}
	exit();
}

function action_notfound() 
{
	$content = '<div class="alert alert-danger">Action not found</div>';
	exit_error($content);
}

function delete_file($path) 
{
	if (file_exists($path)) {
		$unlink = unlink($path);
		if ($unlink) {
			return true;
		}
		return false;
	}
	
	return true;
}

function data_notfound($addData = []) 
{
	$data['title'] = 'Error';
	$data['status'] = 'error';
	$data['message'] = 'Data tidak ditemukan';
	
	if ($addData)
		$data = array_merge ($data, $addData);
	
	return load_view('views/error.php', $data, false, 'error');
	exit();
}

if (empty($_GET['action'])) {
	$_GET['action'] = 'index';
}

function login_required() {
	global $app_auth;
	global $config;
	if (!$app_auth->isLoggedIn() && @$_GET['module'] !== 'login') {
		header('location:'. BASE_URL . 'login'); 
	}
}

function login_restricted() 
{
	global $app_auth;
	global $config;
	if ($app_auth->isLoggedIn()) {
		if ($_GET['action'] !== 'logout') {
			header('location:'. BASE_URL); 
		}
	}
}

function all_parents($id_menu, &$list_parent = []) {
	global $db;
	
	$query = $db->query('SELECT * FROM menu')->result();
	foreach($query as $val) {
		$menu[$val['id_menu']] = $val;
	}

	if (key_exists($id_menu, $menu)) {
		$parent = $menu[$id_menu]['id_parent'];
		if ($parent) {
			$list_parent[$parent] = &$parent;
			all_parents($parent, $list_parent);
		}
	}
	
	return $list_parent;
}

function helper($helpers) {
	if (is_string($helpers)) {
		$helpers = explode(',', $helpers);
		$helpers = array_map('trim', $helpers);
	}
	
	foreach ($helpers as $helper_file) {
		require_once('app/helpers/' . $helper_file . '_helper.php');
	}
}

function current_url() {
	return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function theme_url() {
	global $config;
	return $config['base_url'] . 'public/themes/modern' ;
}

function module_url($action = false) {
	global $config;
	
	$url = $config['base_url'];
	if (empty($_GET['module'])) {
		$module = $config['default_module'];
	} else {
		$module = $_GET['module'];
	}
	$url .= $module;
		
	if (!empty($_GET['action']) && $_GET['action'] != 'index' && $action) {
		$url .= $_GET['action'];
	}
	// return $config['base_url'] . '?module=' . $_GET['module']; 
	return $url;
}

/*
	$message = ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
	show_message($message);
	
	$msg = ['status' => 'ok', 'content' => 'Data berhasil disimpan'];
	show_message($msg['content'], $msg['status']);
	
	$error = ['role_name' => ['Data sudah ada di database', 'Data harus disi']];
	show_message($error, 'error');
	
	$error = ['Data sudah ada di database', 'Data harus disi'];
	show_message($error, 'error');
*/
function show_message($message, $type = null, $dismiss = true) {
	//<ul class="list-error">
	if (is_array($message)) {
		
		// $message = ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
		if (key_exists('status', $message)) 
		{
			$type = $message['status'];
			if (key_exists('message', $message)) {
				$message_source = $message['message'];
			} else if (key_exists('content', $message)) {
				$message_source = $message['content'];
			}
			
			
			if (is_array($message_source)) {
				$message_content = $message_source;
			} else {
				$message_content[] = $message_source;
			}
		
		} else {
			if (is_array($message)) {
				foreach ($message as $key => $val) {
					if (is_array($val)) {
						foreach ($val as $key2 => $val2) {
							$message_content[] = $val2;
						}
					} else {
						$message_content[] = $val;
					}
				}
			}
		}
		// print_r($message_content);
		if (count($message_content) > 1) {
			
			$message_content = recursive_loop($message_content);
			$message = '<ul><li>' . join('</li><li>', $message_content) . '</li></ul>';
		}
		else {
			// echo '<pre>'; print_r($message_content);
			$message_content = recursive_loop($message_content);
			// echo '<pre>'; print_r($message_content);
			$message = $message_content[0];
		}
	}
	
	switch ($type) {
		case 'error' :
			$alert_type = 'danger';
			break;
		case 'warning' :
			$alert_type = 'danger';
			break;
		default:
			$alert_type = 'success';
			break;
	}
	
	$close_btn = '';
	if ($dismiss) {
		$close_btn = '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'; 
	}

	echo '<div class="alert alert-dismissible fade show alert-'.$alert_type.'" role="alert">'. $message . $close_btn . '</div>';
}

function recursive_loop($array, $result = []) {
	// echo '<pre>'; print_r($array);
	foreach ($array as $val) {
		if (is_array($val)) {
			$result = recursive_loop($val, $result);
		} else {
			$result[] = $val;
		}
		// echo '<pre>'; print_r($result);
	}
	return $result;
}


function show_alert($message, $title = null, $dismiss = true) {

	if (is_array($message)) 
	{
		// $message = ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
		if (key_exists('status', $message)) {
			$type = $message['status'];
		}

		if (key_exists('message', $message)) {
			$message = $message['message'];
		}
		
		if (is_array($message)) {
			foreach ($message as $key => $val) {
				if (is_array($val)) {
					foreach ($val as $key2 => $val2) {
						$message_content[] = $val2;
					}
				} else {
					$message_content[] = $val;
				}
			}
			
			if (count($message_content) > 1) {
				$message = '<ul><li>' . join('</li><li>', $message_content) . '</li></ul>';
			}
			else {
				$message = $message_content[0];
			}
		}
	}
	
	if (!$title) {
		switch ($type) {
			case 'error' :
				$title = 'ERROR !!!';
				$icon_type = 'error';
				break;
			case 'warning' :
				$title = 'WARNIG !!!';
				$icon_type = 'error';
				break;
			default:
				$title = 'SUKSES !!!';
				$icon_type = 'success';
				break;
		}
	}
	
	echo '<script type="text/javascript">
			Swal.fire({
				title: "'.$title.'",
				html: "'.$message.'",
				icon: "'.$icon_type.'",
				showCloseButton: '.$dismiss.',
				confirmButtonText: "OK"
			})
		</script>';
}

function current_action_url() {
	global $config;
	return $config['base_url'] . '?module=' . $_GET['module'] . '&action=' . $_GET['action']; 
}

function redirectto_login() {
	header('location:'. BASE_URL . 'login'); 
}

function backup($file) {
	/* $backup = str_replace('.php', '.backup', $file);
	if (!file_exists($backup)) {
		$content = file_get_contents($file);
		$fh = fopen($backup, 'w');
		fwrite($fh, base64_decode('PCEtLSBqYWdvd2ViZGV2LmNvbSAtLT4=') . $content);
		fclose($fh);
	}
	
	return $backup; */
	return $file;
}
?>