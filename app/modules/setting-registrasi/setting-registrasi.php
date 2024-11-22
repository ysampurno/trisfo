<?php
/**
*	PHP Admin Template
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2022
*/

$js[] = BASE_URL . 'public/themes/modern/js/setting-registrasi.js';

$sql = 'SELECT * FROM module LEFT JOIN module_status USING(id_module_status) ORDER BY nama_module';
$data['list_module'] = $db->query($sql)->getResultArray();

switch ($_GET['action']) 
{
	default: 
		action_notfound();
		
	// INDEX 
	case 'index':
		
		$data['message'] = [];
		
		if (!empty($_POST['submit'])) 
		{
			cek_hakakses('update_data');
			
			$form_errors = validate_form();
			$error = false;
						
			if ($form_errors) {
				$data['message']['content'] = $form_errors;
				$error = true;
			} else {
				$db->beginTrans();
				
				$sql = 'DELETE FROM setting WHERE type="register"';
				$db->query($sql);
				
				$param_value = ['enable', 'metode_aktivasi', 'id_role', 'id_module'];
				foreach ($param_value as $value) {
					$data_db[] = ['type' => 'register', 'param' => $value, 'value' => $_POST[$value]];
				}
				
				$db->insertBatch('setting', $data_db);
				
				$result = $db->completeTrans();
				
				if ($result) {
					$data['message']['status'] = 'ok';
					$data['message']['content'] = 'Data berhasil disimpan';
				} else {
					$data['message']['content'] = 'Data gagal disimpan';
					$error = true;
				}
				
			}
			
			if ($error) {
				$data['message']['status'] = 'error';
			}
		}
		
		$sql = 'SELECT * FROM setting WHERE type="register"';
		$query = $db->query($sql)->getResultArray();
		foreach($query as $val) {
			$data['setting'][$val['param']] = $val['value'];
		}
		
		$sql = 'SELECT * FROM role';
		$role = $db->query($sql)->result();
		$data['role'] = $role;

		$data['title'] = $current_module['judul_module'];
		load_view('views/form.php', $data);
}

function validate_form() 
{
	require_once('app/libraries/FormValidation.php');
	$validation = new FormValidation();
	$validation->setRules('enable', 'Diperbolehkan', 'trim|required');
	$validation->setRules('metode_aktivasi', 'Metode Aktivasi', 'trim|required');
	
	$validation->validate();
	$form_errors =  $validation->getMessage();
		
	return $form_errors;
}