<?php
/**
*	PHP Admin Template Jagowebdev
* 	Author	: Agus Prawoto Hadi
*	Website	: https://jagowebdev.com
*	Year	: 2021
*/

include ('functions.php');
$js[] = THEME_URL . 'js/module-role.js';
$styles[] = $config['base_url'] . 'public/vendors/wdi/wdi-loader.css';

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
		
switch ($_GET['action']) 
{
	default: 
		action_notfound();
		
	// INDEX 
	case 'index':
	
		// Delete
		if (!empty($_POST['delete'])) {
			$result = $db->delete('role', ['id_role' => $_POST['id']]);
			// $result = false;
			if ($result) {
				$data['message'] = ['status' => 'ok', 'message' => 'Data module-role berhasil dihapus'];
			} else {
				$data['message'] = ['status' => 'error', 'message' => 'Data module-role gagal dihapus'];
			}
		}
		
		$sql = 'SELECT * FROM module
				LEFT JOIN module_status USING(id_module_status)';
		$data['result'] = $db->query($sql)->result();
		
		load_view('views/result.php', $data);
	
	// DELETE ROLE - AJAX
	case 'delete':
		if (isset($_POST['id_module'])) 
		{
			$query = $db->delete('module_role', ['id_module' => $_POST['id_module'], 'id_role' => $_POST['id_role']]);
			if ($query) {
				$message = ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
			} else {
				$message = ['status' => 'error', 'message' => 'Data gagal dihapus'];
			}
			echo json_encode($message);
			exit;
		}
		break;
		
	// EDIT
	case 'detail':
		$breadcrumb['Detail'] = '';
		
		$data['title'] = 'Edit ' . $current_module['judul_module'];
		$sql = 'SELECT * FROM module WHERE id_module = ' . $_GET['id'];
		$data['module'] = $db->query($sql)->row();
		
		$sql = 'SELECT * FROM role';
		$data['role'] = $db->query($sql)->result();
		
		$sql = 'SELECT * FROM role_detail';
		$data['role_detail'] = $db->query($sql)->result();
		
		$sql = 'SELECT * FROM module_role WHERE id_module = ' . $_GET['id'];
		$data['module_role'] = $db->query($sql)->result();
		
		load_view('views/detail.php', $data);
	
	// EDIT
	case 'edit':
		$breadcrumb['Edit'] = '';
		
		// Submit data
		if (isset($_POST['submit'])) 
		{
			require_once('app/libraries/FormValidation.php');
			$error = checkForm();
			
			if ($error) {
				$message['status'] = 'error';
				$message['content'] = $error;
			} else {
				
				foreach ($_POST as $key => $val) {
					$exp = explode('_', $key);
					if ($exp[0] == 'role') {
						$id_role = $exp[1];
						$insert[] = ['id_module' => $_POST['id']
										, 'id_role' => $id_role
										, 'read_data' => $_POST['akses_read_data_' . $id_role]
										, 'create_data' => $_POST['akses_create_data_' . $id_role]
										, 'update_data' => $_POST['akses_update_data_' . $id_role]
										, 'delete_data' => $_POST['akses_delete_data_' . $id_role]
									];
					}
				}
				
				// INSERT - UPDATE
				$db->beginTrans();
				$db->delete('module_role', ['id_module' => $_POST['id']]);
				$db->insertBatch('module_role', $insert);
				$query = $db->completeTrans();
				
				if ($query) {
					$message = ['status' => 'ok', 'content' => 'Data berhasil disimpan'];
				} else {
					$message = ['status' => 'error', 'content' => 'Data gagal disimpan'];
				}
			}
			$data['message'] = $message;
		}
		
		$data['title'] = 'Edit ' . $current_module['judul_module'];
		$sql = 'SELECT * FROM module WHERE id_module = ' . $_GET['id'];
		$data['module'] = $db->query($sql)->row();
		
		$sql = 'SELECT * FROM role';
		$data['role'] = $db->query($sql)->result();
		
		$sql = 'SELECT * FROM role_detail';
		$data['role_detail'] = $db->query($sql)->result();
		
		$sql = 'SELECT * FROM module_role WHERE id_module = ' . $_GET['id'];
		$data['module_role'] = $db->query($sql)->result();
		
		
		load_view('views/form-add.php', $data);
		break;
	
	case 'getDataDT':
		
		$result['draw'] = $start = $_POST['draw'] ?: 1;
		
		$data_table = getListData();
		$result['recordsTotal'] = $data_table['total_data'];
		$result['recordsFiltered'] = $data_table['total_filtered'];
				
		helper('html');
		
		$no = $_POST['start'] + 1 ?: 1;
		$module_role = [];
		$sql = 'SELECT module_role.*, nama_role, judul_role FROM module_role LEFT JOIN role USING(id_role)';
		$module_role_all = $db->query($sql)->getResultArray();
		foreach($module_role_all as $row) {
			$module_role[$row['id_module']][] = $row;
		}
		
		$no = $_POST['start'] + 1 ?: 1;
		$content = $db->query($sql)->getResultArray();
		foreach ($data_table['content'] as $key => &$val) 
		{
			$list_role = '';
			if (key_exists($val['id_module'], $module_role)) {
				$roles = $module_role[$val['id_module']];
				foreach ($roles as $role) 
				{
					$list_role .= '<span class="badge badge-secondary badge-role px-3 py-2 me-1 mb-1 pe-4">' . $role['judul_role'] . '<a data-action="remove-role" data-id-module="'.$val['id_module'].'" data-id-role="'.$role['id_role'].'" href="javascript:void(0)" class="text-danger"><i class="fas fa-times"></i></a></span>';
				}
			}
			
			$val['ignore_role'] = $list_role;
			$val['ignore_no_urut'] = $no;
			$val['ignore_action'] =  '<div class="btn-action-group">'.
									btn_label(['url' => BASE_URL . 'module-role/edit?id=' . $val['id_module'], 'label' => 'Edit', 'icon' => 'fas fa-edit', 
												'attr' => ['class' => 'btn btn-success btn-xs me-2', 'target' => '_blank']]
											). 
									btn_label(['url' => BASE_URL . 'module-role/detail?id=' . $val['id_module'], 'label' => 'Detail', 'icon' => 'fas fa-eye', 
												'attr' => ['class' => 'btn btn-primary btn-xs', 'target' => '_blank']]
											).
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