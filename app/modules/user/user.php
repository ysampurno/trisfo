<?php
/**
*	PHP Admin Template
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2022
*/

$js[] =	$config['base_url'] . 'public/themes/modern/js/image-upload.js';
$js[] =	$config['base_url'] . 'public/themes/modern/js/user.js';

$js[] = $config['base_url'] . 'public/vendors/jquery.select2/js/select2.full.min.js';
$styles[] =  $config['base_url'] . 'public/vendors/jquery.select2/css/select2.min.css';
$styles[] =  $config['base_url'] . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css';

// Set Data
$sql = 'SELECT * FROM role';
$data['roles'] = $db->query($sql)->getResultArray();
$data['list_action'] = $list_action;

$sql = 'SELECT * FROM module LEFT JOIN module_status USING(id_module_status) ORDER BY nama_module';
$data['list_module'] = $db->query($sql)->getResultArray();

switch ($_GET['action']) 
{
	default: 
		action_notfound();
		
	// INDEX 
	case 'index':
	
		cek_hakakses('read_data');

		if (!empty($_POST['delete'])) 
		{
			cek_hakakses('delete_data', 'user', 'id_user');
			
			$id_user = $_POST['id'];
			$sql = 'SELECT * FROM user WHERE id_user = ?';
			$user = $db->query($sql, $id_user)->getRowArray();
			if ($user) {
				$db->beginTrans();
				$db->delete('user', ['id_user' => $id_user]);
				$db->delete('user_role', ['id_user' => $id_user]);
				
				$trans = $db->completeTrans();
				
				if ($trans) {
					if (!empty($user['avatar'])) {
						delete_file(BASE_PATH . 'public/images/user/' . $user['avatar']);
					}
					$data['message'] = ['status' => 'ok', 'message' => 'Data user berhasil dihapus'];
				} else {
					$data['message'] = ['status' => 'error', 'message' => 'Data user gagal dihapus'];
				}
			}
		}
		
		$data['title'] = 'Data User';
		$sql = 'SELECT * FROM user ' . where_own('id_user');
		
		$data['users'] = $db->query($sql)->result();
		
		if (!$data['users']) {
			$data['message'] = ['status' => 'error', 'message' => 'Data user tidak ditemukan'];
		}
		
		$data['form'] = load_view('views/form-cari.php', $data, true);
		load_view('views/result.php', $data);
	
	case 'add':
		cek_hakakses('create_data');
		
		$breadcrumb['Add'] = '';
	
		$data['title'] = 'Tambah ' . $current_module['judul_module'];
		
		$sql = 'SELECT * FROM setting WHERE type = "register"';
		$result = $db->query($sql)->getResultArray();
		$data['setting_registrasi'] = [];
		foreach ($result as $val) {
			$data['setting_registrasi'][$val['param']] = $val['value'];
		}
		
		$data['message'] = [];
		$error = false;
		if (isset($_POST['submit'])) 
		{
			$data['message'] = save_data();
			if ($data['message']['status'] == 'ok') {
				$sql = 'SELECT * FROM user WHERE id_user = ?';
				$result = $db->query($sql, trim($data['message']['id_user']))->getRowArray();
			}
		}
		load_view('views/form.php', $data);
		
	case 'edit': 
		
		cek_hakakses('update_data', null, 'id_user');
		
		$data['title'] = 'Edit ' . $current_module['judul_module'];
		$breadcrumb['Edit'] = '';
			
		// Submit
		$data['message'] = [];
		if (isset($_POST['submit'])) {
			$data['message'] = save_data();
		}
		
		
		$sql = 'SELECT * FROM user WHERE id_user = ?';
		$result = $db->query($sql, trim($_GET['id']))->getRowArray();
		if (!$result)
			data_notfound();
		
		$sql = 'SELECT * FROM user_role WHERE id_user = ?';
		$data['role_selected'] = $db->query($sql, trim($_GET['id']))->getResultArray();
			
		$data = array_merge($data, ['user_edit' => $result]);
		
		load_view('views/form.php', $data);
		
	case 'edit-password':
		
		$sql = 'SELECT * FROM user WHERE id_user = ?';
		$user = $db->query($sql, trim($_SESSION['user']['id_user']))->row();
		$data = $user;
		$data['title'] = 'Edit Password';
		$breadcrumb['Edit Password'] = '';
		
		// Submit
		$data['message'] = [];
		if (isset($_POST['submit'])) 
		{
			require_once('app/libraries/FormValidation.php');
			$validation = new FormValidation();
			$validation->setRules('password_lama', 'Password Lama', 'trim|required');
			$validation->setRules('password_baru', 'Password Baru', 'trim|required');
			$validation->setRules('ulangi_password_baru', 'Ulangi Password Baru', 'trim|required');
			
			$valid = $validation->validate();
			
			$error = false;
			if (!$valid) {
				$data['message'] = ['status' => 'error', 'message' => $validation->getMessage()];
				$error = true;
			}
			
			if (!password_verify($_POST['password_lama'],$user['password'])) {
				$data['message'] = ['status' => 'error', 'message' => 'Password lama tidak cocok']; 
				$error = true;
			}
			
			if ($_POST['password_baru'] !== $_POST['ulangi_password_baru']) {
				$data['message'] = ['status' => 'error', 'message' => 'Password baru dengan ulangi password baru tidak sama']; 
				$error = true;
			}
			
			if (!$error) {
				$data_db['password'] = password_hash($_POST['password_baru'], PASSWORD_DEFAULT);

				$query = $db->update('user', $data_db, 'id_user = ' . $user['id_user']);
				
				if ($query) {
					$data['message'] = ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
				} else {
					$data['message'] = ['status' => 'error', 'message' => 'Data gagal disimpan'];
				}
				
				$data['title'] = 'Edit Password';
			}
		}
		load_view('views/form-edit-password.php', $data);
		break;
		
	case 'getDataDT':
		
		$result['draw'] = $start = $_POST['draw'] ?: 1;
		
		$data_table = getListData();
		$result['recordsTotal'] = $data_table['total_data'];
		$result['recordsFiltered'] = $data_table['total_filtered'];
				
		helper('html');
		$id_user = $_SESSION['user']['id_user'];
		$avatar_path = BASE_PATH . 'public/images/user/';
		
		$no = $_POST['start'] + 1 ?: 1;
		foreach ($data_table['content'] as $key => &$val) 
		{
			if ($val['avatar']) {
				if (file_exists($avatar_path . $val['avatar'])) {
					$avatar = $val['avatar'];
				} else {
					$avatar = 'default.png';
				}
				
			} else {
				$avatar = 'default.png';
			}
			
			$role = '';
			if ($val['judul_role']) {
				$split = explode(',', $val['judul_role']);
				foreach ($split as $judul_role) {
					$role .= '<span class="badge bg-secondary me-2">' . $judul_role . '</span>';
				}	
			}
			
			$val['judul_role'] = '<div style="white-space:break-spaces">' . $role . '</div>';
			$val['verified'] =  $val['verified'] == 1 ? 'Ya' : 'Tidak' ;
			$val['ignore_avatar'] = '<img src="'. BASE_URL . 'public/images/user/' . $avatar . '">';
			$val['ignore_search_urut'] = $no;
			
			$btn['edit'] = ['url' => BASE_URL . 'user/edit?id='. $val['id_user']];
			if ($list_action['delete_data'] == 'own' || $list_action['delete_data'] == 'all') {
				$btn['delete'] = ['url' => ''
									, 'id' =>  $val['id_user']
									, 'delete-title' => 'Hapus data user: <strong>'.$val['nama'].'</strong> ?'
								];
			}
			$val['ignore_action'] = btn_action($btn);
			
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
	$where = where_own('id_user');
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
	if (strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
		$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
		$order = ' ORDER BY ' . $order_by;
	}

	// Query Total
	$sql = 'SELECT COUNT(*) AS jml_data FROM user ' . where_own('id_user');
	$query = $db->query($sql)->getRowArray();
	$total_data = $query['jml_data'];
	
	// Query Filtered
	$sql = 'SELECT COUNT(*) as jml FROM
			(SELECT user.*, GROUP_CONCAT(judul_role) AS judul_role FROM user 
			LEFT JOIN user_role USING(id_user) 
			LEFT JOIN role ON user_role.id_role = role.id_role
			' . $where . '
			GROUP BY id_user) AS tabel';
			
	$query = $db->query($sql)->getRowArray();
	$total_filtered = $query['jml'];
		
	// Query Data
	$start = $_POST['start'] ?: 0;
	$length = $_POST['length'] ?: 10;
	$sql = 'SELECT user.*, GROUP_CONCAT(judul_role) AS judul_role FROM user 
				LEFT JOIN user_role USING(id_user) 
				LEFT JOIN role ON user_role.id_role = role.id_role
				' . $where . '
				GROUP BY id_user
				' . $order . ' LIMIT ' . $start . ', ' . $length;

	$content = $db->query($sql)->getResultArray();
	
	return ['total_data' => $total_data, 'total_filtered' => $total_filtered, 'content' => $content];
}

function save_data() 
{
	global $list_action, $db;
	
	$form_errors = validate_form();
	$error = false;	
	$result = [];
	
	if ($form_errors) {
		$data['form_errors'] = $form_errors;
		$error_message = $form_errors;
		$error = true;
	}
	
	if (!$error) {
		
		$fields = ['nama', 'email', ];
		if ($list_action['update_data'] == 'all') {
			$add_field = ['username', 'status', 'verified', 'id_module'];
			$fields = array_merge($fields, $add_field);
		}

		foreach ($fields as $field) {
			$data_db[$field] = $_POST[$field];
		}
		
		$db->beginTrans();
		
		if (!$_POST['id']) {
			$data_db['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
		}
		
		// Save database
		if ($_POST['id']) {
			$id_user = $_POST['id'];
			$db->update('user', $data_db, ['id_user' => $id_user]);
		} else {
			$db->insert('user', $data_db);
			$id_user = $db->lastInsertId();
		}
		
		if ($list_action['update_data'] == 'all') {
			$data_db = [];
			foreach ($_POST['id_role'] as $id_role) {
				$data_db[] = ['id_user' => $id_user, 'id_role' => $id_role];
			}
			
			$db->delete('user_role', ['id_user' => $id_user]);
			$db->insertBatch('user_role', $data_db);
		}
		
		$trans = $db->completeTrans();
		
		$save = false;
		if ($trans) {
			
			$path = BASE_PATH . 'public/images/user/';
			
			$sql = 'SELECT avatar FROM user WHERE id_user = ?';
			$img_db = $db->query($sql, $id_user)->getRowArray();
			$new_name = $img_db['avatar'];
			
			if (!empty($_POST['avatar_delete_img'])) 
			{
				$del = delete_file($path . $img_db['avatar']);
				$new_name = '';
				if (!$del) {
					$error_message = 'Gagal menghapus gambar lama';
					$error = true;
				}
			}
					
			if ($_FILES['avatar']['name']) 
			{
				//old file
				if ($img_db['avatar']) {
					if (file_exists($path . $img_db['avatar'])) {
						$unlink = delete_file($path . $img_db['avatar']);
						if (!$unlink) {
							$result['status'] = 'error';
							$error_message = 'Gagal menghapus gambar lama';
						}
					}
				}
							
				$new_name = upload_image($path, $_FILES['avatar'], 300,300);
				
				if (!$new_name) {
					$error_message = 'Error saat memperoses gambar';
					$error = true;
				}
			}
			
			// Update avatar
			if (!$error) {
				$data_db = [];
				$data_db['avatar'] = $new_name;
				$save = $db->update('user', $data_db, ['id_user' => $id_user]);
				if (!$save) {
					$error = true;
				}
			}
		}

		if (!$error) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
			$result['id_user'] = $id_user;
			
			if ($_SESSION['user']['id_user'] == $id_user) {
				// Reload data user
				require_once('app/modules/login/functions.php');
				$_SESSION['user'] = set_user($id_user);
			}
		}
		
		return $result;
	}
	
	if ($error) {
		$result['status'] = 'error';
		$result['message'] = $error_message;
	}
	
	return $result;
}
	
function validate_form() {
	
	global $list_action;
	require_once('app/libraries/FormValidation.php');
	$validation = new FormValidation();
	$validation->setRules('nama', 'Nama', 'trim|required');
	$validation->setRules('email', 'Email', 'trim|required|valid_email');
	
	if ($_POST['id']) {
		if ($_POST['email'] != $_POST['email_lama']) {
			$validation->setRules('email', 'Email', 'trim|required|valid_email|unique[user.email]');
		}
	} else {
		if ($list_action['update_data'] == 'all') {
			$validation->setRules('username', 'Username', 'trim|required');
			$validation->setRules('id_role', 'Role', 'required');
			$validation->setRules('password', 'Password', 'trim|required|min_length[3]');
			$validation->setRules('email', 'Email', 'trim|required|valid_email|unique[user.email]');
			$validation->setRules('ulangi_password', 'Ulangi Password', 'trim|required|matches[password]');
		}
	}
		
	$validation->validate();
	$form_errors =  $validation->getMessage();
			
	if ($_FILES['avatar']['name']) {
		
		$file_type = $_FILES['avatar']['type'];
		$allowed = ['image/png', 'image/jpeg', 'image/jpg'];
		
		if (!in_array($file_type, $allowed)) {
			$form_errors['avatar'] = 'Tipe file harus ' . join(', ', $allowed);
		}
		
		if ($_FILES['avatar']['size'] > 300 * 1024) {
			$form_errors['avatar'] = 'Ukuran file maksimal 300Kb';
		}
		
		$info = getimagesize($_FILES['avatar']['tmp_name']);
		if ($info[0] < 100 || $info[1] < 100) { //0 Width, 1 Height
			$form_errors['avatar'] = 'Dimensi file minimal: 100px x 100px';
		}
	}
	
	return $form_errors;
}