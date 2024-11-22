<?php
/**
*	PHP Admin Template Jagowebdev
* 	Author	: Agus Prawoto Hadi
*	Website	: https://jagowebdev.com
*	Year	: 2021
*/

$site_title = 'Home Page';
$restrict_function = array('checkForm');

$styles[] = $config['base_url'] . 'public/vendors/jquery-nestable/jquery.nestable.min.css?r='.time();
$styles[] = $config['base_url'] . 'public/vendors/wdi/wdi-modal.css?r=' . time();
$styles[] = $config['base_url'] . 'public/vendors/wdi/wdi-fapicker.css?r=' . time();
$styles[] = $config['base_url'] . 'public/vendors/wdi/wdi-loader.css?r=' . time();
$js[] = $config['base_url'] . 'public/vendors/wdi/wdi-fapicker.js?r=' . time();
$js[] = THEME_URL . 'js/admin-menu.js';
$js[] = $config['base_url'] . 'public/vendors/jquery-nestable/jquery.nestable.js?r=' . time();
$js[] = $config['base_url'] . 'public/vendors/js-yaml/js-yaml.min.js?r=' . time();
$js[] = $config['base_url'] . 'public/vendors/jquery-nestable/jquery.wdi-menueditor.js?r=' . time();

$js[] = $config['base_url'] . 'public/vendors/jquery.select2/js/select2.full.min.js' ;
$styles[] = $config['base_url'] . 'public/vendors/jquery.select2/css/select2.min.css';
$styles[] = $config['base_url'] . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css';

$js[] = $config['base_url'] . 'public/vendors/dragula/dragula.min.js';
$styles[] = $config['base_url'] . 'public/vendors/dragula/dragula.min.css';


include 'functions.php';

function get_kategori() {
	global $db;
	$sql = 'SELECT * FROM menu_kategori ORDER BY urut';
	$result = $db->query($sql)->getResultArray();
	return $result;
}

function get_menu_by_id_kategori($id_menu_kategori = null) 
{
	global $db;
	$result = [];
	if ($id_menu_kategori) {
		$where_id_menu_kategori = 'id_menu_kategori = '. $id_menu_kategori;
	} else {
		$where_id_menu_kategori = '( id_menu_kategori = 0 OR id_menu_kategori = "" OR id_menu_kategori IS NULL )';
	}
	
	$sql = 'SELECT * FROM menu 
				LEFT JOIN menu_role USING (id_menu)
				LEFT JOIN module USING (id_module)
			WHERE 1 = 1 
			AND ' . $where_id_menu_kategori . '
			ORDER BY urut';
	
	$query = $db->query($sql)->getResultArray();
	return $query;
}

function all_Child($id, $list, &$result = []) 
{
	if (!key_exists($id, $list)) {
		return $result;
	}
	
	$result[$id] = $id;
	foreach ($list[$id] as $val) 
	{
		$result[$val] = $val;
		if (key_exists($val, $list)) {
			all_Child($val, $list, $result);
		}
	}
	return $result;
}

function list_modules() {
	global $db;
	$sql = 'SELECT * FROM module LEFT JOIN module_status USING(id_module_status) ORDER BY nama_module';
	return $db->query($sql)->getResultArray();
}

switch ($_GET['action']) 
{
	default: 
		action_notfound();
		
	// INDEX 
	case 'index':
		
		// SUBMIT
		$menu_updated = [];
		$message = [];
		if (!empty($_POST['submit'])) 
		{
			$json = json_decode(trim($_POST['data']), true);
			$array = build_child($json);
			
			foreach ($array as $id_parent => $arr) {
				foreach ($arr as $key => $id_menu) {
					$list_menu[$id_menu] = ['id_parent' => $id_parent, 'urut' => ($key + 1)];
				}
			}
			
			$result = $db->query('SELECT * FROM menu')->result();
			foreach ($result as $key => $row) 
			{
				$update = [];
				if ($list_menu[$row['id_menu']]['id_parent'] != $row['id_parent']) {
					$id_parent =  $list_menu[$row['id_menu']]['id_parent'] == 0 ? NULL : $list_menu[$row['id_menu']]['id_parent'];
					$update['id_parent'] = $id_parent;
				}
				
				if ($list_menu[$row['id_menu']]['urut'] != $row['urut']) {
					$update['urut'] = $list_menu[$row['id_menu']]['urut'];
				}
				
				if ($update) {
					$query = $db->update('menu', $update, 'id_menu=' . $row['id_menu']);
					if ($query) {
						$menu_updated[$row['id_menu']] = $row['id_menu'];
					}
				}
			}
			
			if ($menu_updated) {
				$message['status'] = 'ok';
				$message['message'] = 'Menu berhasil diupdate';
			} else {
				$message['status'] = 'warning';
				$message['message'] = 'Tidak ada menu yang diupdate';
			}
		}
		// End Submit
		
		$data['menu_kategori'] = get_kategori();
		require_once('app/includes/functions.php');
		
		$result = get_menu_by_id_kategori($data['menu_kategori'][0]['id_menu_kategori']);
		$list_menu = menu_list($result);
		$data['list_menu'] = build_menu_list($list_menu); 
		
		$data['role'] = 	$db->query('SELECT * FROM role')->result();
		$data['message'] = $message;
		
		$bredcrumb['Home'] = $config['base_url'];
		$bredcrumb['Data Gedung'] = $config['base_url'];
		$bredcrumb['Add'] = '';
		
		load_view('views/menu.php', $data);
		break;

	// EDIT
	case 'edit':
	
		global $db;	
		
		// Submit
		$data['msg'] = [];
		if (isset($_POST['nama_menu'])) 
		{
			$error = checkForm();
			if ($error) {
				$data['msg']['status'] = 'error';
				$data['msg']['message'] = '<ul class="list-error"><li>' . join($error, '</li><li>') . '</li></ul>';
			} else {
				$data_db['nama_menu'] = $_POST['nama_menu'];
				$data_db['id_module'] = $_POST['id_module'];
				$data_db['url'] = $_POST['url'];
				if (empty($_POST['aktif'])) {
					$data_db['aktif'] = 0;
				} else {
					$data_db['aktif'] = 1;
				}
				
				if ($_POST['use_icon']) {
					$data_db['class'] = $_POST['icon_class'];
				} else {
					$data_db['class'] = NULL;
				}
				
				if (empty($_POST['id'])) {
					$query = $db->insert('menu', $data_db);
					$last_id = $db->lastInsertId();
					$message = 'Menu berhasil ditambahkan';
					$data['msg']['id_menu'] = $last_id;
				} else {
					$query = $db->update('menu', $data_db, 'id_menu = ' . $_POST['id']);
					$message = 'Menu berhasil diupdate';
				}
				
				$query = true;
				if ($query) {
					$data['msg']['status'] = 'ok';
					$data['msg']['message'] = $message;
					// $data['msg']['message'] = 'Menu berhasil diupdate';
				} else {
					$data['msg']['status'] = 'error';
					$data['msg']['message'] = 'Data gagal disimpan';
					$data['msg']['error_query'] = true;
				}	
			}
			echo json_encode($data['msg']);
			exit();
		}
		break;
	
	case 'ajax-save-menu':
		
		$error = checkForm();
		$message = [];
		if ($error) {
			$data['status'] = 'error';
			$message = ['status' => 'error', 'message' => $error];
		} else {
				
			$data_db['nama_menu'] = $_POST['nama_menu'];
			$data_db['id_module'] = $_POST['id_module'] ?: NULL;
			$data_db['url'] = $_POST['url'];
			
			if (trim($_POST['id_menu_kategori']) == '') {
				$id_menu_kategori = NULL;
			} else {
				$id_menu_kategori = $_POST['id_menu_kategori'];
			}
			$data_db['id_menu_kategori'] = $id_menu_kategori;
				
			if (empty($_POST['aktif'])) {
				$data_db['aktif'] = 0;
			} else {
				$data_db['aktif'] = 1;
			}
			
			if ($_POST['use_icon']) {
				$data_db['class'] = $_POST['icon_class'];
			} else {
				$data_db['class'] = NULL;
			}
			
			if (!empty($_POST['id'])) 
			{
				$db->beginTrans();
				
				// Cek ganti group
				$sql = 'SELECT id_menu_kategori FROM menu WHERE id_menu = ?';
				$query = $db->query($sql, $_POST['id'])->getRowArray();
				if ($query['id_menu_kategori'] != $id_menu_kategori) {
					$data_db['id_parent'] = NULL;
				}
				
				$db->update('menu', $data_db, ['id_menu' => $_POST['id']]);
				$message_content = 'Menu berhasil diupdate';
				
				// Update group to all child
				$json = json_decode(trim($_POST['menu_tree']), true);
				$array = build_child($json);
				$all_child = all_child($_POST['id'], $array);
				foreach ($all_child as $val) {
					$db->update('menu', ['id_menu_kategori' => $id_menu_kategori], ['id_menu' => $val]);
				}
				
				// Update role
				$data_db = [];
				foreach ($_POST['id_role'] as $val) {
					$data_db[] = ['id_menu' => $_POST['id'], 'id_role' => $val];
				}
				$db->delete('menu_role', ['id_menu' => $_POST['id']]);
				$db->insertBatch('menu_role', $data_db);
				
				$save = $db->completeTrans();
		
			} else {
					
				$save = $db->insert('menu', $data_db);
				$insert_id = $db->lastInsertId();
				$data_db = [];
				foreach ($_POST['id_role'] as $val) {
					$data_db[] = ['id_menu' => $insert_id, 'id_role' => $val];
				}
				$db->insertBatch('menu_role', $data_db);
				$message_content = 'Menu berhasil ditambahkan';
				$message['id_menu'] = $insert_id;
			}
			
			
			if ($save) {
				$message['status'] = 'ok';
				$message['message'] = $message_content;
			} else {
				$message['status'] = 'error';
				$message['message'] = 'Data gagal disimpan';
				$message['error_query'] = true;
			}
		}
		
		echo json_encode($message);
		
		break;
	
	case 'ajax-delete-kategori':
		
		$id = $_POST['id'];
		if ($id) {
			$db->beginTrans();
			$db->delete('menu_kategori', ['id_menu_kategori' => $id]);
			$db->update('menu', ['id_menu_kategori' => null], ['id_menu_kategori' => $id]);
			$delete = $db->completeTrans();
			
			if ($delete) {
				$message['status'] = 'ok';
				$message['message'] = 'Data berhasil dihapus';
			} else {
				$message['status'] = 'error';
				$message['message'] = 'Data gagal dihapus';
			}
			echo json_encode($message);
		}
		break;
	
	case 'ajax-delete-menu':
		$db->beginTrans();
		
		// Delete all parent and child
		$json = json_decode(trim($_POST['menu_tree']), true);
		$array = build_child($json);
		$all_child = all_child($_POST['id'], $array);
		if ($all_child) {
			foreach ($all_child as $id_menu) {
				$db->delete('menu', ['id_menu' => $id_menu]);
			}
		} else {
			$db->delete('menu', ['id_menu' => $_POST['id']]);
		}
		
		$result = $db->completeTrans();
		
		if ($result) {
			$message = ['status' => 'ok', 'message' => 'Data menu berhasil dihapus'];
			
		} else {
			$message = ['status' => 'error', 'message' => 'Data menu gagal dihapus'];
		}
		
		echo json_encode($message);
		break;
	
	case 'ajax-update-kategori-urut':
	
		$list_kategori = json_decode($_POST['id'], true);
		$db->beginTrans();
		$urut = 1;
		foreach ($list_kategori as $id_kategori) {
			$db->update('menu_kategori', ['urut' => $urut], ['id_menu_kategori' => $id_kategori]); 
			$urut++;
		}
		
		$result = $db->completeTrans();
		if ($result) {
			$message = ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
		} else {
			$message = ['status' => 'error', 'message' => 'Data gagal disimpan'];
		}
		echo json_encode($message);
		break;
	
	case 'ajax-update-menu-urut':
	
		$json = json_decode(trim($_POST['data']), true);
		$array = build_child($json);
		
		foreach ($array as $id_parent => $arr) {
			foreach ($arr as $key => $id_menu) {
				$list_menu[$id_menu] = ['id_parent' => $id_parent, 'urut' => ($key + 1)];
			}
		}
	
		$id_menu_kategori = trim($_POST['id_menu_kategori']);
		if (empty($id_menu_kategori)) {
			$where_id_menu_kategori = ' id_menu_kategori = "" OR id_menu_kategori IS NULL';
		} else {
			$where_id_menu_kategori = ' id_menu_kategori = ' . $id_menu_kategori;
		}
		
		$sql = 'SELECT * FROM menu WHERE ' . $where_id_menu_kategori;
		$result = $db->query($sql)->getResultArray();
		
		$db->beginTrans();
		$menu_updated = [];
		
		// echo '<pre>'; print_r($json); die;
		foreach ($result as $key => $row) 
		{
			$data_db = [];
			if ($list_menu[$row['id_menu']]['id_parent'] != $row['id_parent']) {
				$id_parent =  $list_menu[$row['id_menu']]['id_parent'] == 0 ? NULL : $list_menu[$row['id_menu']]['id_parent'];
				$data_db['id_parent'] = $id_parent;
			}
			
			if ($list_menu[$row['id_menu']]['urut'] != $row['urut']) {
				$data_db['urut'] = $list_menu[$row['id_menu']]['urut'];
			}
			
			if ($data_db) {
				$result = $db->update('menu', $data_db, ['id_menu' => $row['id_menu']]);
				if ($result) {
					$menu_updated[$row['id_menu']] = $row['id_menu'];
				}
			}
		}
		
		$result = $db->completeTrans();
		if ($result) {
			$message['status'] = 'ok';
			$message['message'] = 'Menu berhasil diupdate';
		} else {
			$message['status'] = 'warning';
			$message['message'] = 'Tidak ada menu yang diupdate';
		}
		
		echo json_encode($message);
		break;
	
	case 'ajax-save-kategori':
		
		$data_db['nama_kategori'] = $_POST['nama_kategori'];
		$data_db['deskripsi'] = $_POST['deskripsi'];
		$data_db['aktif'] = $_POST['aktif'];
		$data_db['show_title'] = $_POST['show_title'];

		if (@$_POST['id']) {
			$save = $db->update('menu_kategori', $data_db, ['id_menu_kategori' => $_POST['id']]);
		} else {
			$sql = 'SELECT MAX(urut) AS urut FROM menu_kategori';
			$last_urut = $db->query($sql)->getRowArray();
			$data_db['urut'] = $last_urut['urut'] + 1;
			$save = $db->insert('menu_kategori', $data_db);
		}
		
		if ($save) {
			$message['status'] = 'ok';
			$message['message'] = 'Menu berhasil diupdate';
			$message['id_kategori'] = $db->lastInsertID();
		} else {
			$message['status'] = 'warning';
			$message['message'] = 'Tidak ada menu yang diupdate';
		}
		
		echo json_encode($message);
		break;
	
	case 'ajax-get-menu-by-id-kategori':
		$result = get_menu_by_id_kategori($_GET['id_menu_kategori']);
		
		if ($result) {
			$list_menu = menu_list($result);
			echo build_menu_list($list_menu); 
		} else {
			echo '';
		}
		break;
	
	case 'ajax-get-kategori-form':
		
		$id = $_GET['id'];
		$data = [];
		if ($id) {
			$sql = 'SELECT * FROM menu_kategori WHERE id_menu_kategori = ?';
			$data['kategori'] = $db->query($sql, $id)->getRowArray();
		}
		echo load_view('views/kategori-form.php', $data, true);
		break;
	
	case 'ajax-get-menu-form':
	
		$data['menu_kategori'] = get_kategori();
		$data['list_module'] = 	$db->query('SELECT * FROM module LEFT JOIN module_status USING(id_module_status) ORDER BY nama_module')->result();
		$data['roles'] = $db->query('SELECT * FROM role')->getResultArray();
		$data['menu'] =[];
		if (!empty($_GET['id'])) {
			$sql = 'SELECT menu.*, GROUP_CONCAT(id_role) AS id_role
				FROM menu 
				LEFT JOIN menu_role USING(id_menu) 
				WHERE id_menu = ? GROUP BY id_menu';
			$result = $db->query($sql, $_GET['id'])->getRowArray();
			$data['menu'] = $result;
		}
		echo load_view('views/menu-form.php', $data, true);
		break;
}