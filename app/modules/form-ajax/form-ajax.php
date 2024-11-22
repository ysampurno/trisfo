<?php
/**
*	App Name	: PHP Admin Template Dashboard	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2022
*/

$js[] = BASE_URL . 'public/themes/modern/js/image-upload.js';
$js[] = BASE_URL . 'public/themes/modern/js/form-ajax.js';

// Data Tables - Script utama Data Tables ada di app/themes/modern/header.php
// $js[] = BASE_URL . 'public/themes/modern/js/data-tables-ajax.js';

$js[] = BASE_URL . 'public/vendors/flatpickr/dist/flatpickr.js';
$styles[] = BASE_URL . 'public/vendors/flatpickr/dist/flatpickr.min.css';

$site_title = 'Form Ajax';

switch ($_GET['action']) 
{
	default: 
		action_notfound();
		
	// INDEX 
	case 'index':
	
		$sql = 'SELECT * FROM mahasiswa';
		$data['result'] = $db->query($sql)->getResultArray();
		
		if (!$data['result']) {
			$data['msg']['status'] = 'error';
			$data['msg']['content'] = 'Data tidak ditemukan';
		}
		
		load_view('views/result.php', $data);
	
	case 'add': 
		
		$breadcrumb['Add'] = '';
		$data['title'] = 'Tambah Data Mahasiswa';
		
		// Submit
		$data['message'] = [];
		if (isset($_POST['submit']))
		{
			$error = false;
			$form_errors = validate_form();
			
			if ($form_errors) {
				$data['message'] = ['status' => 'error', 'message' => $form_errors];
			} else {
				$data['message'] = save_data();
			}
		}
		load_view('views/form.php', $data);
	
	case 'edit': 
	
		cek_hakakses('update_data', 'mahasiswa');
		
		$breadcrumb['Edit'] = '';
	
		$data['title'] = 'Edit ' . $current_module['judul_module'];
		
		// Submit
		$data['message'] = [];
		if (isset($_POST['submit']))
		{
			$error = false;
			$form_errors = validate_form();
			
			if ($form_errors) {
				$data['message'] = ['status' => 'error', 'message' => $form_errors];
			} else {
				$data['message'] = save_data();
			}
		}
		
		// Updated image
		$sql = 'SELECT * FROM mahasiswa WHERE id_mahasiswa = ?';
		$result = $db->query($sql, trim($_GET['id']))->getRowArray();
		$data = array_merge($data, $result);
		load_view('views/form.php', $data);
		break;
	
	case 'ajax-get-form':
		$data = [];
		if (isset($_GET['id'])) {
			if ($_GET['id']) {
				$sql = 'SELECT * FROM mahasiswa WHERE id_mahasiswa = ?';
				$data = $db->query($sql, trim($_GET['id']))->getRowArray();
				if (!$data)
					break;
			}
		}
		
		echo load_view('views/form-ajax.php', $data, true);
		break;
	
	case 'ajax-save-data':
		
		if (!isset($_POST))
		{
			$message = ['status' =>' error', 'message' => 'invalid request'];
		} else {
					
			$error = false;
			$form_errors = validate_form();
			
			if ($form_errors) {
				$message = ['status' => 'error', 'message' => $form_errors];
			} else {
				$message = save_data();
			}
		}
		echo json_encode($message);
		exit;
	
	case 'ajax-delete-data':
	
		$sql = 'SELECT foto FROM mahasiswa WHERE id_mahasiswa = ?';
		$img = $db->query($sql, $_POST['id'])->getRowArray();
		if ($img['foto']) {
			if (file_exists($config['foto_path'] . $img['foto'])) {
				$del = delete_file ($config['foto_path'] . $img['foto']);
				if (!$del) {
					return false;
				}
			}
		}
		
		$result = $db->delete('mahasiswa', ['id_mahasiswa' => $_POST['id']]);
		// $result = true;
		if ($result) {
			$message = ['status' => 'ok', 'message' => 'Data Mahasiswa berhasil dihapus'];
		} else {
			$message = ['status' => 'error', 'message' => 'Data Mahasiswa gagal dihapus'];
		}
		echo json_encode($message);
		exit;
		
	case 'getDataDT':
		
		$result['draw'] = $start = $_POST['draw'] ?: 1;
		
		$data_table = getListData();
		$result['recordsTotal'] = $data_table['total_data'];
		$result['recordsFiltered'] = $data_table['total_filtered'];
				
		helper('html');
		$id_user = $_SESSION['user']['id_user'];
		
		$no = $_POST['start'] + 1 ?: 1;
		foreach ($data_table['content'] as $key => &$val) 
		{
			$foto = 'noimage.png';
			if ($val['foto']) {
				if (file_exists('public/images/foto/' . $val['foto'])) {
					$foto = $val['foto'];
				} else {
					$foto = 'noimage.png';
				}
			}
			
			$val['ignore_search_foto'] = '<div class="list-foto"><img src="'. BASE_URL.'public/images/foto/' . $foto . '"/></div>';
			
			$val['tgl_lahir'] = $val['tempat_lahir'] . ', '. format_tanggal($val['tgl_lahir']);
			
			$val['ignore_search_urut'] = $no;
			$val['ignore_search_action'] = '<div class="form-inline btn-action-group">'
										. btn_label(
												['icon' => 'fas fa-edit'
													, 'url' => $config['base_url'] . 'form-ajax/edit?id=' . $val['id_mahasiswa']
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1', 'data-id' => $val['id_mahasiswa']]
													, 'label' => 'Edit'
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'url' => '#'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_mahasiswa']
																	, 'data-delete-title' => 'Hapus data mahasiswa: <strong>' . $val['nama'] . '</strong>'
																]
													, 'label' => 'Delete'
												]) . 
										
										'</div>';
					
			
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
		// Additional Search
		$columns[]['data'] = 'tempat_lahir';
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
	$sql = 'SELECT COUNT(*) AS jml_data FROM mahasiswa' . where_own();
	$query = $db->query($sql)->getRowArray();
	$total_data = $query['jml_data'];
	
	// Query Filtered
	$sql = 'SELECT COUNT(*) AS jml_data FROM mahasiswa' . $where;
	$query = $db->query($sql)->getRowArray();
	$total_filtered = $query['jml_data'];
	
	// Query Data
	$start = $_POST['start'] ?: 0;
	$length = $_POST['length'] ?: 10;
	$sql = 'SELECT * FROM mahasiswa 
			' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
	$content = $db->query($sql)->getResultArray();
	
	return ['total_data' => $total_data, 'total_filtered' => $total_filtered, 'content' => $content];
}

function set_data() {
	$exp = explode('-', $_POST['tgl_lahir']);
	$tgl_lahir = $exp[2].'-'.$exp[1].'-'.$exp[0];
	$data_db['nama'] = $_POST['nama'];
	$data_db['tempat_lahir'] = $_POST['tempat_lahir'];
	$data_db['tgl_lahir'] = $tgl_lahir;
	$data_db['npm'] = $_POST['npm'];
	$data_db['prodi'] = $_POST['prodi'];
	$data_db['fakultas'] = $_POST['fakultas'];
	$data_db['alamat'] = $_POST['alamat'];
	return $data_db;
}

function validate_form() {
	
	require_once('app/libraries/FormValidation.php');
	$validation = new FormValidation();
	$validation->setRules('nama', 'Nama Siswa', 'required');
	$validation->setRules('tempat_lahir', 'Tempat Lahir', 'trim|required');
	$validation->setRules('tgl_lahir', 'Tanggal Lahir', 'trim|required');
	$validation->setRules('npm', 'NPM', 'trim|required');
	$validation->setRules('prodi', 'Prodi', 'trim|required');
	$validation->setRules('fakultas', 'Fakultas', 'trim|required');
	$validation->setRules('alamat', 'Alamat', 'trim|required');
	
	$validation->validate();
	$form_errors =  $validation->getMessage();
			
	if ($_FILES['foto']['name']) {
		
		$file_type = $_FILES['foto']['type'];
		$allowed = ['image/png', 'image/jpeg', 'image/jpg'];
		
		if (!in_array($file_type, $allowed)) {
			$form_errors['foto'] = 'Tipe file harus ' . join($allowed, ', ');
		}
		
		if ($_FILES['foto']['size'] > 300 * 1024) {
			$form_errors['foto'] = 'Ukuran file maksimal 300Kb';
		}
		
		$info = getimagesize($_FILES['foto']['tmp_name']);
		if ($info[0] < 100 || $info[1] < 100) { //0 Width, 1 Height
			$form_errors['foto'] = 'Dimensi file minimal: 100px x 100px';
		}
	}
	
	return $form_errors;
}

function save_data() 
{
	global $db;
	
	$exp = explode('-', $_POST['tgl_lahir']);
	$tgl_lahir = $exp[2].'-'.$exp[1].'-'.$exp[0];
	$data_db['nama'] = $_POST['nama'];
	$data_db['tempat_lahir'] = $_POST['tempat_lahir'];
	$data_db['tgl_lahir'] = $tgl_lahir;
	$data_db['npm'] = $_POST['npm'];
	$data_db['prodi'] = $_POST['prodi'];
	$data_db['fakultas'] = $_POST['fakultas'];
	$data_db['alamat'] = $_POST['alamat'];
	
	$query = false;
	
	$new_name = '';
	$img_db['foto'] = '';
	
	$path = BASE_PATH . 'public/images/foto/';
	
	if ($_POST['id']) {
		$sql = 'SELECT foto FROM mahasiswa WHERE id_mahasiswa = ?';
		$img_db = $db->query($sql, $_POST['id'])->getRowArray();
		$new_name = $img_db['foto'];
		
		if ($_POST['foto_delete_img']) {
			$del = delete_file($path . $img_db['foto']);
			$new_name = '';
			if (!$del) {
				$data['message'] = 'Gagal menghapus gambar lama';
				$error = true;
			}
		}
	}
		
	if ($_FILES['foto']['name'])
	{
		//old file
		if ($_POST['id']) {
			if ($img_db['foto']) {
				if (file_exists($path . $img_db['foto'])) {
					$unlink = delete_file($path . $img_db['foto']);
					if (!$unlink) {
						$result['status'] = 'error';
						$result['message'] = 'Gagal menghapus gambar lama';
						return $result;
					}
				}
			}
		}
	
		$new_name = upload_image($path, $_FILES['foto'], 300,300);
			
		if (!$new_name) {
			$result['status'] = 'error';
			$result['message'] = 'Error saat memperoses gambar';
			return $result;
		}
	}
	
	$data_db['foto'] = $new_name;
	
	if ($_POST['id']) 
	{
		$data_db['tgl_edit'] = date('Y-m-d');
		$data_db['id_user_edit'] = $_SESSION['user']['id_user'];
		$query = $db->update('mahasiswa', $data_db, ['id_mahasiswa' => $_POST['id']]);	
	} else {
		$data_db['tgl_input'] = date('Y-m-d');
		$data_db['id_user_input'] = $_SESSION['user']['id_user'];
		$query = $db->insert('mahasiswa', $data_db);
		$result['id_mahasiswa'] = '';
		if ($query) {
			$result['id_mahasiswa'] = $db->lastInsertId();
		} 
	}
	
	if ($query) {
		$result['status'] = 'ok';
		$result['message'] = 'Data berhasil disimpan';
	} else {
		$result['status'] = 'error';
		$result['message'] = 'Data gagal disimpan';
	}
	
	return $result;
}