<?php
/**
*	PHP Admin Template Jagowebdev
* 	Author	: Agus Prawoto Hadi
*	Website	: https://jagowebdev.com
*	Year	: 2021
*/

$js[] = THEME_URL . 'js/module.js';

$sql = 'SELECT * FROM module';
$data['module'] = $db->query($sql)->result();

$sql = 'SELECT * FROM role';
$db->query($sql);
while($row = $db->fetch()) {
	$data['role'][$row['id_role']] = $row;
}

$sql = 'SELECT * FROM module_role';
$db->query($sql);
while($row = $db->fetch()) {
	$data['module_role'][$row['id_module']][] = $row['id_role'];
}


$fields = ['nama_module', 'judul_module', 'deskripsi', 'id_module_status', 'login'];
switch ($_GET['action']) 
{
	default: 
		action_notfound();
		
	// INDEX 
	case 'index':
		load_view('views/result.php', $data);

	case 'add':
		$data['title'] = 'Tambah Module';
		$breadcrumb['Add'] = '';
		
		$sql = 'SELECT * FROM module_status';
		$data['module_status'] = $db->query($sql)->result();
		
		if (isset($_POST['submit'])) 
		{
			
			$form_errors = validate_form();
			if ($form_errors) {
				$data['message'] = ['status' => 'error', 'message' => $form_errors];
			} else {
				$data_db = prepare_datadb($fields);
				$query = $db->insert('module', $data_db);
				if ($query) {
					$last_id = $db->lastInsertId();
					$data['message'] = ['status' => 'ok', 'message' => 'Data berhasil disimpan', 'id_module' => $last_id];
				} else {
					$data['message'] = ['status' => 'error', 'message' => 'Data gagal disimpan'];
				}
			}
		}
		load_view('views/form.php', $data);
		
	case 'edit':
		
		$data['title'] = 'Edit Data Module';		
	
		// Submit data
		if (isset($_POST['submit'])) 
		{
			require_once('app/libraries/FormValidation.php');
			$validation = new FormValidation();
			$unique = false;
			if ($_POST['nama_module'] != $_POST['nama_module_old']) {
				$unique = true;
			}
			
			$form_errors = validate_form(true);
			
			if ($form_errors) {
				$data['message'] = ['status' => 'error', 'message' => $form_errors];
			} else {
				$data_db = prepare_datadb($fields);
				$query = $db->update('module', $data_db, 'id_module = ' . $_POST['id']);
				
				if ($query) {
					$data['message'] = ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
				} else {
					$data['message'] = ['status' => 'error', 'message' => 'Data gagal disimpan'];
				}	
			}
		}
		
		$sql = 'SELECT * FROM module WHERE id_module = ?';
		$result = $db->query($sql, trim($_REQUEST['id']))->row();
		if (!$result)
			data_notfound();
		
		$data = array_merge($data, $result);
		
		// List module status
		$sql = 'SELECT * FROM module_status';
		$data['module_status'] = $db->query($sql)->result();
		
		$breadcrumb['Edit'] = '';
		load_view('views/form.php', $data);
		break;
	
	case 'ajax-delete':
	
		$result = $db->delete('module', ['id_module' => $_POST['id']]);
		if ($result) {
			$message = ['status' => 'ok', 'message' => 'Data role berhasil dihapus'];
		} else {
			$message = ['status' => 'error', 'message' => 'Data role gagal dihapus'];
		}
		echo json_encode($message);
		break;
	
	case 'ajax-switch-module-status':
	
		// Module Aktif/Nonaktif/Login
		$field = $_POST['switch_type'] == 'aktif' ? 'id_module_status' : 'login';
		$update_status = $db->update('module', 
					[$field => $_POST['id_result']], 
					['id_module' => $_POST['id_module']]
				);
				
		if (!empty($_POST['ajax'])) {
			if ($update_status) {
				echo 'ok';
			} else {
				echo 'error';
			}
			die();
		}
		break;
		
	case 'getDataDT':
		
		$result['draw'] = $start = $_POST['draw'] ?: 1;
		
		$data_table = getListData();
		$result['recordsTotal'] = $data_table['total_data'];
		$result['recordsFiltered'] = $data_table['total_filtered'];
				
		helper('html');
		$login = ['Y' => 'Ya', 'N' => 'Tidak', 'R' => 'Restrict'];
		$files = scandir('app/modules/');
		$files = array_map('strtolower', $files);
		
		$no = $_POST['start'] + 1 ?: 1;
		$content = $db->query($sql)->getResultArray();
		foreach ($data_table['content'] as $key => &$val) 
		{
			$checked = $val['id_module_status'] == 1 ? 'checked' : '';
			// Disbled module builtin/module
			$disabled = $current_module == $val['nama_module'] ? ' disabled' : '';
			$file_exists = in_array( str_replace('-', '_', $val['nama_module']) . '.php', $files) ? 'Ada' : 'Tidak Ada';
			
			$val['login'] = $login[$val['login']];
			$val['ignore_file_exists'] = $file_exists;
			$val['ignore_aktif'] = '<div class="form-switch">
								<input name="aktif" type="checkbox" class="form-check-input switch" data-module-id="'.$val['id_module'].'" ' . $checked . $disabled . '>
							</div>';
			$val['ignore_no_urut'] = $no;
			$val['ignore_action'] = '<div class="form-inline btn-action-group">'
									. btn_label(
											['icon' => 'fas fa-edit'
												, 'url' => $config['base_url'] . 'module/edit?id=' . $val['id_module']
												, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1', 'data-id' => $val['id_module']]
												, 'label' => 'Edit'
											])
									. btn_label(
											['icon' => 'fas fa-times'
												, 'url' => '#'
												, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																, 'data-id' => $val['id_module']
																, 'data-delete-title' => 'Hapus data module: <strong>' . $val['judul_module'] . '</strong>'
															]
												, 'label' => 'Delete'
											]) . 
									
									'</div>';
			$no++;
		}
					
		$result['data'] = $data_table['content'];
		echo json_encode($result);
		break;
}

function getListData() {
	
	global $db;
	$columns = $_POST['columns'];
	$order_by = '';
	
	// Search
	$search_all = @$_POST['search']['value'];
	$where = where_own();
	if ($search_all) {
		foreach ($columns as $val) {
			
			if (strpos($val['data'], 'ignore_search') !== false) 
				continue;
			
			if (strpos($val['data'], 'ignore') !== false)
				continue;
			
			$where_col[] = $val['data'] . ' LIKE "%' . $search_all . '%"';
		}
		 $where .= ' AND (' . join(' OR ', $where_col) . ') ';
	}
	
	// Order	
	$order_data = $_POST['order'];
	$order = '';
	if (strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore') === false) {
		$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
		$order = ' ORDER BY ' . $order_by;
	}

	// Query Total
	$sql = 'SELECT COUNT(*) AS jml_data FROM module ' . where_own();
	$query = $db->query($sql)->getRowArray();
	$total_data = $query['jml_data'];
	
	// Query Filtered
	$sql = 'SELECT COUNT(*) AS jml_data FROM module ' . $where;	
	$query = $db->query($sql)->getRowArray();
	$total_filtered = $query['jml_data'];
		
	// Query Data
	$start = $_POST['start'] ?: 0;
	$length = $_POST['length'] ?: 10;
	$sql = 'SELECT * FROM module 
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
	
	$content = $db->query($sql)->getResultArray();	
	return ['total_data' => $total_data, 'total_filtered' => $total_filtered, 'content' => $content];
}

function validate_form($check_unique = false) 
{
	require_once('app/libraries/FormValidation.php');
	$validation = new FormValidation();
	$unique = '';
	if ($check_unique) {
		$unique = '|unique[module.nama_module]';
	}
	$validation->setRules('nama_module', 'Nama Module', 'trim|required' . $unique);
	$validation->setRules('judul_module', 'Judul Module', 'trim|required');
	$validation->setRules('deskripsi', 'Judul Module', 'trim|required');
	$validation->setRules('id_module_status', 'Judul Module', 'trim|required');
	$validation->validate();
	return $validation->getMessage();
}