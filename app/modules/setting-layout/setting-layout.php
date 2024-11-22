<?php
/**
*	PHP Admin Template Jagowebdev
* 	Author	: Agus Prawoto Hadi
*	Website	: https://jagowebdev.com
*	Year	: 2021
*/

$js[] = BASE_URL . 'public/themes/modern/js/setting.js';
$styles[] = BASE_URL . 'public/themes/modern/css/setting-layout.css';
$site_title = 'Home Page';
$params = ['color_scheme' => 'Color Scheme'
			, 'sidebar_color' => 'Sidebar Color'
			, 'logo_background_color' => 'Background Logo'
			, 'font_family' => 'Font Family'
			, 'font_size' => 'Font Size'
		];

switch ($_GET['action']) 
{
	default: 
		action_notfound();
		
	// INDEX 
	case 'index':

		if (!empty($_POST['submit'])) 
		{
			$query = false;
			$role_error = false;
			foreach ($params as $param => $title) {
				$data_db[] = ['type' => 'layout', 'param' => $param, 'value' => $_POST[$param]];
				$arr[$param] = $_POST[$param];
			}
			
			if ($list_action['update_data'] == 'all')
			{
				$db->beginTrans();
				$db->delete('setting', ['type' => 'layout']);
				$result = $db->insertBatch('setting', $data_db);
				$query = $db->completeTrans();
				
				if ($query) {
					$file_name = THEME_PATH . 'css/fonts/font-size-' . $_POST['font_size'] . '.css';
					if (!file_exists($file_name)) {
						file_put_contents($file_name, 'html, body { font-size: ' . $_POST['font_size'] . 'px }');
					}						
				}
				// $query = true;
				
			} else if ($list_action['update_data'] == 'own') 
			{
				$db->beginTrans();
				$db->delete('setting_user', $_SESSION['user']['id_user']);
				$result = $db->insert('setting_user', ['id_user' => $_SESSION['user']['id_user']
																, 'param' => json_encode($arr)
															]
								);
				$query = $db->completeTrans();
				
			} else {
				$data['status'] = 'error';
				$data['message'] = 'Role anda tidak diperbolehkan melakukan perubahan';
				$role_error = true;
			}
			
			if (!$role_error) {
				if ($query) {
					$data['status'] = 'ok';
					$data['message'] = 'Data berhasil disimpan';
				} else {
					$data['status'] = 'error';
					$data['message'] = 'Data gagal disimpan';
				}
			}
			
			if (!empty($_POST['ajax'])) {
				echo json_encode($data); die;
			}
		}
		
		$user_setting = $db->query('SELECT * FROM setting_user WHERE id_user = ?', $_SESSION['user']['id_user'])
					->row();
					
		if ($user_setting) {
			$data = json_decode($user_setting['param'], true);
		} else {
			$sql = 'SELECT * FROM setting WHERE type="layout"';
			$query = $db->query($sql)->result();
			foreach($query as $val) {
				$data[$val['param']] = $val['value'];
			}
		}
		
		$data['title'] = 'Edit ' . $current_module['judul_module'];
		load_view('views/form.php', $data);
}

function validate_form() 
{
	global $params;
	require_once('app/libraries/FormValidation.php');
	$validation = new FormValidation();
	
	foreach($params as $param => $title) {
		$validation->setRules($param, $title, 'required');
	}
	
	$validation->validate();
	$form_errors =  $validation->getMessage();
	
	return $form_errors;
}