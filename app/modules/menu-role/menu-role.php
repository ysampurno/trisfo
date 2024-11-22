<?php
/**
*	PHP Admin Template Jagowebdev
* 	Author	: Agus Prawoto Hadi
*	Website	: https://jagowebdev.com
*	Year	: 2021
*/

$js[] = THEME_URL . 'js/menu-role.js';
$styles[] = $config['base_url'] . 'public/vendors/wdi/wdi-loader.css';

$sql = 'SELECT * FROM menu';
$data['menu_all'] = $db->query($sql)->getResultArray();

$sql = 'SELECT * FROM role';
$db->query($sql);
while($row = $db->fetch()) {
	$data['roles'][$row['id_role']] = $row;
}

$data['menu_role'] = [];
$sql = 'SELECT * FROM menu_role';
$db->query($sql);
while($row = $db->fetch()) {
	$data['menu_role'][$row['id_menu']][] = $row['id_role'];
}

switch ($_GET['action']) 
{
	default: 
		action_notfound();
		
	// INDEX 
	case 'index':
		load_view('views/result.php', $data);
		break;
	
	// CHECKBOX - AJAX
	case 'checkbox':

		$sql = 'SELECT * FROM menu_role WHERE id_menu = ?';
		$menu_role = $db->query($sql, $_GET['id'])->getResultArray();
		$checked = [];
		foreach ($menu_role as $row) {
			$checked[] = $row['id_role'];
		}
	
		$data['checked'] = $checked;
		
		echo load_view('views/form-edit.php', $data, true);
		break;
	
	// DELETE ROLE - AJAX
	case 'delete':
		if (isset($_POST['id_menu'])) 
		{
			$query = $db->delete('menu_role', ['id_menu' => $_POST['id_menu'], 'id_role' => $_POST['id_role']]);
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
		if (isset($_POST['id_menu'])) 
		{
			// Find all parent
			$menu_parent = all_parents($_POST['id_menu']);
			$role_del = [];
				
			$insert_parent = [];
			if ($menu_parent) 
			{
				// Cek apakah parent telah diassign di role yang tercentang, jika belum buat insert nya
				foreach($menu_parent as $id_menu_parent) {
					foreach ($_POST['id_role'] as $id_role) {
						$sql = 'SELECT * FROM menu_role WHERE id_menu = ? AND id_role = ?';
						$data = [$id_menu_parent, $id_role];
						$query = $db->query($sql, $data)->row();
						if (!$query) {
							$insert_parent[] = ['id_menu' => $id_menu_parent, 'id_role' => $id_role];
						}
					}
				}
			}

			// INSERT - DELETE
			$db->beginTrans();
			
			if ($insert_parent) {
				$db->insertBatch('menu_role', $insert_parent);
			}
			
			// Hapus role pada menu
			$db->delete('menu_role', ['id_menu' => $_POST['id_menu']]);

			// Insert role yang tercentang
			$data_db = [];
			foreach ($_POST['id_role'] as $id_role) {
				$data_db[] = ['id_menu' => $_POST['id_menu'], 'id_role' => $id_role];
			}
			$db->insertBatch('menu_role', $data_db);
		
			$query = $db->completeTrans();
			
			if ($query) {
				$message = ['status' => 'ok', 'message' => 'Data berhasil disimpan', 'data_parent' => json_encode($insert_parent)];
			} else {
				$message = ['status' => 'error', 'message' => 'Data gagal disimpan'];
			}

			echo json_encode($message);
			exit;
		}
		break;
		
	case 'getDataDT':
		
		$result['draw'] = $start = $_POST['draw'] ?: 1;
		
		$data_table = getListData();
		$result['recordsTotal'] = $data_table['total_data'];
		$result['recordsFiltered'] = $data_table['total_filtered'];
				
		helper('html');
		
		$menu_role = [];
		$sql = 'SELECT * FROM menu_role LEFT JOIN role USING(id_role)';
		$menu_role_all = $db->query($sql)->getResultArray();
		foreach($menu_role_all as $row) {
			$menu_role[$row['id_menu']][] = $row;
		}
		
		$no = $_POST['start'] + 1 ?: 1;
		$content = $db->query($sql)->getResultArray();
		foreach ($data_table['content'] as $key => &$val) 
		{
			$list_role = '';
			if (key_exists($val['id_menu'], $menu_role)) {
				$roles = $menu_role[$val['id_menu']];
				foreach ($roles as $role) 
				{
					$list_role .= '<span class="badge badge-secondary badge-role px-3 py-2 me-1 mb-1 pe-4">' . $role['judul_role'] . '<a data-action="remove-role" data-id-menu="'.$val['id_menu'].'" data-id-role="'.$role['id_role'].'" href="javascript:void(0)" class="text-danger"><i class="fas fa-times"></i></a></span>';
				}
			}
			
			$val['ignore_role'] = $list_role;
			$val['ignore_no_urut'] = $no;
			$val['ignore_action'] = btn_label(['url' => '#', 'label' => 'Edit', 'icon' => 'fas fa-edit', 'attr' => ['data-id-menu' => $val['id_menu'], 'class' => 'btn btn-edit btn-success btn-xs']]);
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
	$sql = 'SELECT COUNT(*) AS jml_data FROM menu ' . where_own();
	$query = $db->query($sql)->getRowArray();
	$total_data = $query['jml_data'];
	
	// Query Filtered
	$sql = 'SELECT COUNT(*) AS jml_data FROM menu ' . $where;	
	$query = $db->query($sql)->getRowArray();
	$total_filtered = $query['jml_data'];
		
	// Query Data
	$start = $_POST['start'] ?: 0;
	$length = $_POST['length'] ?: 10;
	$sql = 'SELECT * FROM menu 
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
	
	$content = $db->query($sql)->getResultArray();	
	return ['total_data' => $total_data, 'total_filtered' => $total_filtered, 'content' => $content];
}