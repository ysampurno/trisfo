<?php
/**
*	App Name	: PHP Admin Template Dashboard	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2022
*/

$js[] = BASE_URL . 'public/vendors/bootstrap-datepicker/js/bootstrap-datepicker.js';
$js[] = BASE_URL . 'public/themes/modern/js/date-picker.js';
$js[] = BASE_URL . 'public/themes/modern/js/image-upload.js';

$js[] = BASE_URL . 'public/vendors/datatables/datatables.min.js';
$js[] = BASE_URL . 'public/vendors/datatables/js/dataTables.bootstrap4.min.js';

$styles[] = BASE_URL . 'public/vendors/datatables/css/dataTables.bootstrap4.min.css';
$js[] = BASE_URL . 'public/themes/modern/js/data-tables.js';
$styles[] = BASE_URL . 'public/vendors/bootstrap-datepicker/css/bootstrap-datepicker3.css';

$site_title = 'Data Tables';

switch ($_GET['action']) 
{
	default: 
		action_notfound();
		
	// INDEX 
	case 'index':
	
		if (!empty($_POST['delete'])) 
		{
			
			$sql = 'SELECT foto FROM mahasiswa WHERE id_mahasiswa = ?';
			$img = $db->query($sql, $_POST['id'])->getRowArray();
			if ($img) {
				if (file_exists($config['foto_path'] . $img['foto'])) {
					$del = delete_file($config['foto_path'] . $img['foto']);
					if (!$del) {
						return false;
					}
				}
			}
			
			// $result = $db->delete('mahasiswa', ['id_mahasiswa' => $_POST['id']]);
			$result = true;
			if ($result) {
				$data['msg'] = ['status' => 'ok', 'message' => 'Data akta berhasil dihapus'];
			} else {
				$data['msg'] = ['status' => 'error', 'message' => 'Data akta gagal dihapus'];
			}
		}
		
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
		$data['msg'] = [];
		if (isset($_POST['submit'])) 
		{
			$form_errors = validate_form();
			if (!$_FILES['foto']['name']) {
				$form_errors['foto'] = 'Foto belum dipilih';
			}
			
			if ($form_errors) {
				$data['msg']['status'] = 'error';
				$data['msg']['content'] = $form_errors;
			} else {
				
				$data_db = set_data();
				$data_db['tgl_input'] = date('Y-m-d');
				$data_db['id_user_input'] = $_SESSION['user']['id_user'];
				
				$path = $config['foto_path'];
				
				if (!is_dir($path)) {
					if (!mkdir($path, 0777, true)) {
						$data['msg']['status'] = 'error';
						$data['msg']['content'] = 'Unable to create a directory: ' . $path;
					}
				}
				
				$query = false;
				$new_name = upload_image($path, $_FILES['foto']);
	
				if ($new_name) {
					$data_db['foto'] = $new_name;
					$query = $db->insert('mahasiswa', $data_db);
					
					if ($query) {
						$newid = $db->lastInsertId();
						$data['msg']['status'] = 'ok';
						$data['msg']['content'] = 'Data berhasil disimpan';
						$sql = 'SELECT foto FROM mahasiswa WHERE id_mahasiswa = ?';
						$result = $db->query($sql, $newid)->row();
						$data['foto'] = $result['foto'];
					} else {
						$data['msg']['status'] = 'error';
						$data['msg']['content'] = 'Data gagal disimpan';
					}
				} else {
					$data['msg']['status'] = 'error';
					$data['msg']['content'] = 'Error saat memperoses gambar';
				}
					
				
				
			}
		}
		load_view('views/form.php', $data);
	
	case 'edit': 
		cek_hakakses('update_data');
		cek_action('update_data');
		
		$breadcrumb['Edit'] = '';
	
		$data['title'] = 'Edit ' . $current_module['judul_module'];
		
		// Submit
		$data['msg'] = [];
		if (isset($_POST['submit'])) 
		{
			
			$form_errors = validate_form();
			
			$sql = 'SELECT foto FROM mahasiswa WHERE id_mahasiswa = ?';
			$img_db = $db->query($sql, $_POST['id'])->row();
			
			if (!$_FILES['foto']['name'] && $img_db['foto'] == '') {
				$form_errors['foto'] = 'Foto belum dipilih';
			}
			
			if ($form_errors) {
				$data['msg']['status'] = 'error';
				$data['msg']['content'] = $form_errors;
			} else {
				
				$data_db = set_data();
				$data_db['tgl_edit'] = date('Y-m-d');
				$data_db['id_user_edit'] = $_SESSION['user']['id_user'];
				$path = 'public/images/foto/';
				
				$query = false;

				$new_name = $img_db['foto'];
				if ($_FILES['foto']['name']) 
				{
					//old file
					if ($img_db['foto']) {
						$del = delete_file($path . $img_db['foto']);
						if (!$del) {
							$data['msg']['status'] = 'error';
							$data['msg']['content'] = 'Gagal menghapus gambar lama';
						}
					}
					
					$new_name = upload_image($path, $_FILES['foto'], 300,300);
				}
	
				if ($new_name) {
					$data_db['foto'] = $new_name;
					$query = $db->update('mahasiswa', $data_db, 'id_mahasiswa = ' . $_POST['id']);
					if ($query) {
						$data['msg']['status'] = 'ok';
						$data['msg']['content'] = 'Data berhasil disimpan';
					} else {
						$data['msg']['status'] = 'error';
						$data['msg']['content'] = 'Data gagal disimpan';
					}
				} else {
					$data['msg']['status'] = 'error';
					$data['msg']['content'] = 'Error saat memperoses gambar';
				}
			}
		}
		
		// Updated image
		$sql = 'SELECT * FROM mahasiswa WHERE id_mahasiswa = ?';
		$result = $db->query($sql, trim($_GET['id']))->getRowArray();
		$data = array_merge($data, $result);
		load_view('views/form.php', $data);
}

function cek_action($action) 
{
	global $list_action;
	global $db;
	
	$sql = 'SELECT * FROM mahasiswa WHERE id_mahasiswa = ?';
	$result = $db->query($sql, trim($_REQUEST['id']))->result();
	$data = $result[0];

	if ($list_action[$action] == 'own') {
		if ($data['id_user_input'] != $_SESSION['user']['id_user']) {
			echo 'Anda tidak diperkenankan mengakses halaman ini';
			die;
		}
	}
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