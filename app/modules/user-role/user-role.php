<?php
/**
*	PHP Admin Template Jagowebdev
* 	Author	: Agus Prawoto Hadi
*	Website	: https://jagowebdev.com
*	Year	: 2021
*/

$js[] = THEME_URL . 'js/user-role.js';
$styles[] = $config['base_url'] . 'public/vendors/wdi/wdi-loader.css';

$sql = 'SELECT * FROM role';
$db->query($sql);
while($row = $db->fetch()) {
	$data['roles'][$row['id_role']] = $row;
}

$data['user_role'] = [];
$sql = 'SELECT * FROM user_role';
$db->query($sql);
while($row = $db->fetch()) {
	$data['user_role'][$row['id_user']][] = $row['id_role'];
}
		
switch ($_GET['action']) 
{
	default: 
		action_notfound();
		
	// INDEX 
	case 'index':
	
		// Get user
		$sql = 'SELECT * FROM user ';
		$data['users'] = $db->query($sql)->result();
		load_view('views/result.php', $data);
	
	
	// DELETE ROLE - AJAX
	case 'delete-role':
		if (isset($_POST['pair_id'])) 
		{
			$query = $db->delete('user_role', ['id_user' => $_POST['pair_id'], 'id_role' => $_POST['id_role']]);
			if ($query) {
				$message = ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
			} else {
				$message = ['status' => 'error', 'message' => 'Data gagal dihapus'];
			}
			echo json_encode($message);
			exit;
		}
	
	// EDIT - AJAX
	case 'edit':
		
		// Submit data
		if (isset($_POST['id_user'])) 
		{				
			foreach ($_POST['id_role'] as $id_role) {
				$insert[] = ['id_user' => $_POST['id_user'], 'id_role' => $id_role];
			}
			
			// INSERT - UPDATE
			$db->beginTrans();
			$db->delete('user_role', ['id_user' => $_POST['id_user']]);
			$db->insertBatch('user_role', $insert);
			$query = $db->completeTrans();
			
			if ($query) {
				$message = ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
			} else {
				$message = ['status' => 'error', 'message' => 'Data gagal disimpan'];
			}
			
			echo json_encode($message);
			exit;
		}
		break;
	
	case 'checkbox':
	
		$sql = 'SELECT * FROM user_role WHERE id_user = ?';
		$user_role = $db->query($sql, $_GET['id'])->getResultArray();
		$data['user_role'] = $user_role;
		
		echo load_view('views/form-edit.php', $data, true);
		break;
	
	case 'delete':
		
		$result = $db->delete('user_role', ['id_user' => $_POST['id_user'], 'id_role' => $_POST['id_role']]);
		if ($result) {
			$message = ['status' => 'ok', 'message' => 'Data user-role berhasil dihapus'];
		} else {
			$message = ['status' => 'error', 'message' => 'Data user-role gagal dihapus'];
		}
		
		echo json_encode($message);
		break;
	
	case 'getDataDT':
		
		$result['draw'] = $start = $_POST['draw'] ?: 1;
		
		$data_table = getListData();
		$result['recordsTotal'] = $data_table['total_data'];
		$result['recordsFiltered'] = $data_table['total_filtered'];
				
		helper('html');
		
		$no = $_POST['start'] + 1 ?: 1;
		$user_role = [];
		$sql = 'SELECT * FROM user_role LEFT JOIN role USING(id_role)';
		$user_role_all = $db->query($sql)->getResultArray();
		foreach($user_role_all as $row) {
			$user_role[$row['id_user']][] = $row;
		}
		
		$no = $_POST['start'] + 1 ?: 1;
		$content = $db->query($sql)->getResultArray();
		foreach ($data_table['content'] as $key => &$val) 
		{
			$list_role = '';
			if (key_exists($val['id_user'], $user_role)) {
				$roles = $user_role[$val['id_user']];
				foreach ($roles as $role) 
				{
					$list_role .= '<span class="badge badge-secondary badge-role px-3 py-2 me-1 mb-1 pe-4">' . $role['judul_role'] . '<a data-action="remove-role" data-id-user="'.$val['id_user'].'" data-role-id="'.$role['id_role'].'" href="javascript:void(0)" class="text-danger"><i class="fas fa-times"></i></a></span>';
				}
			}
			
			$val['ignore_role'] = $list_role;
			$val['ignore_no_urut'] = $no;
			$val['ignore_action'] = btn_label(['url' => '#', 'label' => 'Edit', 'icon' => 'fas fa-edit', 'attr' => ['data-id-user' => $val['id_user'], 'class' => 'btn btn-edit btn-success btn-xs']]);
			$no++;
		}
					
		$result['data'] = $data_table['content'];
		echo json_encode($result); exit();
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
	$sql = 'SELECT COUNT(*) AS jml_data FROM user ' . where_own();
	$query = $db->query($sql)->getRowArray();
	$total_data = $query['jml_data'];
	
	// Query Filtered
	$sql = 'SELECT COUNT(*) AS jml_data FROM user ' . $where;	
	$query = $db->query($sql)->getRowArray();
	$total_filtered = $query['jml_data'];
		
	// Query Data
	$start = $_POST['start'] ?: 0;
	$length = $_POST['length'] ?: 10;
	$sql = 'SELECT * FROM user 
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
	
	$content = $db->query($sql)->getResultArray();	
	return ['total_data' => $total_data, 'total_filtered' => $total_filtered, 'content' => $content];
}