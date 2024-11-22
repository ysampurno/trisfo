<?php
/**
* PHP Admin Template
* Author	: Agus Prawoto Hadi
* Website	: https://jagowebdev.com
* Year		: 2021-2022
*/

function cek_hakakses($action, $table_column = false, $column_check = false) 
{
	global $list_action
			, $check_role_action
			, $current_module
			, $db;
	
	$action_title = ['create_data' => 'menambah data', 'update_data' => 'mengubah data', 'delete_data' => 'menghapus data'];
	$allowed = $list_action[$action];
	
	if ($allowed == 'no') {
		$current_module['nama_module'] = 'error';
		
		load_view('views/error.php', ['status' => 'error', 'message' => 'Role Anda tidak diperkenankan untuk ' . $action_title[$action]]);
	} 
	else if ($allowed == 'own') {
		
		// Read -> go to where_own()
		if ($action == 'read_data') 
			return true;
		
		// Update and delete
		$column = '';
		if ($table_column) {
			$exp = explode('|', $table_column);
			$table = $exp[0];
			$column = @$exp[1];
		} else {
			$table = $current_module['nama_module'];
		}
		
		if (!$column) {
			$column = 'id_' . $table;
		}
		
		if (!$column_check) {
			$column_check = $check_role_action['field'];
		}
			
		$sql = 'SELECT * FROM ' . $table . ' WHERE ' . $column . ' = ?';
		$result = $db->query($sql, trim($_REQUEST['id']))->getResultArray();
		if ($result) {
			$data = $result[0];
			
			if ($data[$column_check] != $_SESSION['user']['id_user']) {
				$message = ['status' => 'error', 'message' => 'Role Anda tidak diperkenankan untuk ' . $action_title[$action] . ' ini'];
				load_view('views/error.php', $message, false, 'error');
			}
		}
	}
}

function where_own($field = null) 
{
	global $list_action, $check_role_action;
	
	if (!$field)
		$field = $check_role_action['field'];
		
	if ($list_action['read_data'] == 'own') {
		return ' WHERE ' . $field . ' = ' . $_SESSION['user']['id_user'];
	}
	
	return ' WHERE 1 = 1 ';
}