<?php
/**
*	PHP Admin Template Jagowebdev
* 	Author	: Agus Prawoto Hadi
*	Website	: https://jagowebdev.com
*	Year	: 2021
*/

$js[] = BASE_URL . 'public/vendors/jquery.select2/js/select2.full.min.js';
$styles[] = BASE_URL . 'public/vendors/jquery.select2/css/select2.min.css';
$js[] = BASE_URL . 'public/themes/modern/js/options-dinamis.js';
$js[] = BASE_URL . 'public/vendors/bootstrap-datepicker/js/bootstrap-datepicker.js';
$js[] = THEME_URL . 'js/date-picker.js';
$styles[] = BASE_URL . 'public/vendors/bootstrap-datepicker/css/bootstrap-datepicker3.css';

$js[] = $config['base_url'] . 'public/vendors/jquery.select2/js/select2.full.min.js' ;
$styles[] = $config['base_url'] . 'public/vendors/jquery.select2/css/select2.min.css';
$styles[] = $config['base_url'] . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css';

$site_title = 'Akta';

switch ($_GET['action']) 
{
	default: 
		action_notfound();
		
	// INDEX 
	case 'index':
		
		if (!empty($_POST['delete'])) 
		{
			
			$sql = 'SELECT * FROM akta_file WHERE id_akta = ' . $_POST['id'];
			$query_file = $db->query($sql)->result();
						
			if ($query_file) {
				foreach ($query_file as $val) {
					delete_file($config['dokumen_path'] . $val['nama_file']);
				}
			}
			
			$db->beginTrans();
			$result = $db->delete('akta', ['id_akta' => $_POST['id']]);
			$result = $db->delete('akta_penghadap', ['id_akta' => $_POST['id']]);
			$result = $db->delete('akta_file', ['id_akta' => $_POST['id']]);
			
			$result = $db->completeTrans();
						
			// $result = true;
			if ($result) {
				$data['msg'] = ['status' => 'ok', 'message' => 'Data akta berhasil dihapus'];
			} else {
				$data['msg'] = ['status' => 'error', 'message' => 'Data akta gagal dihapus'];
			}
		}
		
		//  DISTINCT nama_file => Link nama file nya meleset 
		$sql = 'SELECT *
						, GROUP_CONCAT(DISTINCT nama_penghadap) AS nama_penghadap
						, GROUP_CONCAT(DISTINCT nama_penanggung_jawab) AS nama_penanggung_jawab
				FROM akta
				LEFT JOIN akta_penghadap USING (id_akta)
				LEFT JOIN akta_file USING (id_akta)
				LEFT JOIN penghadap USING (id_penghadap)
				LEFT JOIN akta_penanggung_jawab USING (id_akta)
				LEFT JOIN penanggung_jawab USING (id_penanggung_jawab)
				
				GROUP BY id_akta ORDER BY id_akta DESC';
				
		$result = $db->query($sql)->result();
		$data['result'] = $result;
		$data['akta_file'] = [];

		if ($result) {
			foreach ($result as $val) {
				$id_akta[] = $val['id_akta'];
			}
			
			$sql = 'SELECT *
					FROM akta_file
					WHERE id_akta IN (' . join(',', $id_akta) . ')';
			
			$file = $db->query($sql)->result();
			
			foreach ($file as $val) {
				$akta_file[$val['id_akta']][$val['id_akta_file']] = $val;
			}
			
			$data['akta_file'] = $akta_file;
		}
		
		if (!$data['result']) {
			$data['msg']['status'] = 'error';
			$data['msg']['content'] = 'Data tidak ditemukan';
		}
		
		load_view('views/result.php', $data);
	
	case 'add': 
		if (empty($_GET['id'])) 
		{
			$breadcrumb['Add'] = '';
		}
	
	
	case 'edit': 

		$data['title'] = 'Tambah Data Akta';
		
		// Submit
		$data['msg'] = [];
		
		$sql = 'SELECT * FROM penghadap';
		$query = $db->query($sql)->result();
		$penghadap = [];
		foreach($query as $val) {
			$penghadap[$val['id_penghadap']] = $val['nama_penghadap'];
		}
			
		$data['penghadap'] = $penghadap;
		
		$sql = 'SELECT * FROM penanggung_jawab';
		$query = $db->query($sql)->result();
		$penanggung_jawab = [];
		foreach($query as $val) {
			$penanggung_jawab[$val['id_penanggung_jawab']] = $val['nama_penanggung_jawab'];
		}
		
		if (isset($_POST['submit'])) 
		{
			$form_errors = validate_form();
							
			if ($form_errors) {
				$data['msg']['status'] = 'error';
				$data['msg']['content'] = $form_errors;
			} else {
				
				$query = false;
				$db->beginTrans();
				
				list($tanggal, $bulan, $tahun) = explode('-', $_POST['tgl_akta']);
				$data_db['no_akta'] = $_POST['no_akta'];
				$data_db['tgl_akta'] = $tahun . '-' . $bulan . '-' . $tanggal;
				$data_db['nama_akta'] = $_POST['nama_akta'];
				$data_db['nama_customer'] = $_POST['nama_customer'];
				
				// EDIT
				if (!empty($_POST['id'])) 
				{
					if (@$_POST['delete_current_file']) {
						foreach ($_POST['delete_current_file'] as $key => $val) {
							if ($val) {
								delete_file($config['dokumen_path'] . $_POST['delete_current_file_nama_file'][$key]);
								$result = $db->delete('akta_file', ['id_akta_file' => $_POST['delete_current_file_id_akta_file'][$key]] );
							}
						}
						
					}
					
					$query = $db->update('akta', $data_db, 'id_akta = ' . $_POST['id']);
					$result = $db->delete('akta_penghadap', ['id_akta' => $_POST['id']]);
					$result = $db->delete('akta_penanggung_jawab', ['id_akta' => $_POST['id']]);
					
					$id_akta = $_POST['id'];
					
				} else {
					$query = $db->insert('akta', $data_db);
					$id_akta = $newid = $db->lastInsertId();
				}
				
				foreach ($_POST['id_penghadap'] as $val) {
					$data_db_penghadap[] = ['id_akta' => $id_akta, 'id_penghadap' => $val];
				}
				
				$query = $db->insertBatch('akta_penghadap', $data_db_penghadap);
				foreach ($_POST['id_penanggung_jawab'] as $val) {
					$data_db_pj[] = ['id_akta' => $id_akta, 'id_penanggung_jawab' => $val];
				}
				$query = $db->insertBatch('akta_penanggung_jawab', $data_db_pj);
				
				$query = $db->completeTrans();
			
				if ($query) {
					$data['msg']['status'] = 'ok';
					$data['msg']['content'] = 'Data berhasil disimpan';
				} else {
					$data['msg']['status'] = 'error';
					$data['msg']['content'] = 'Data gagal disimpan';
				}
				
				$data['title'] = 'Edit Data Repotarium';
			}
		}
		
		if (!empty($_GET['id'])) 
		{
			$breadcrumb['Edit'] = '';
			
			$data['title'] = 'Edit Data Repotarium';
			
			$sql = 'SELECT * FROM akta WHERE id_akta = ?';
			$result = $db->query($sql, trim($_GET['id']))->result();
			
			if ($result) {
				
				foreach ($result as $arr) {
					foreach ($arr as $key => $val) {
						$data[$key]	= $val;
					}
				}
				
				$sql = 'SELECT id_penanggung_jawab FROM akta_penanggung_jawab WHERE id_akta = ?';
				$result = $db->query($sql, trim($data['id_akta']))->result();
				foreach ($result as $arr) {
					foreach ($arr as $key => $val) {
						$data['id_penanggung_jawab'][]	= $val;
					}
				}
				
				$sql = 'SELECT id_penghadap FROM akta_penghadap WHERE id_akta = ?';
				$result = $db->query($sql, trim($data['id_akta']))->result();
				foreach ($result as $arr) {
					foreach ($arr as $key => $val) {
						$data['id_penghadap'][]	= $val;
					}
				}
				
				$sql = 'SELECT * FROM akta_file WHERE id_akta = ?';
				$result = $db->query($sql, trim($data['id_akta']))->result();
				
				foreach ($result as $key => $arr) {
					foreach ($arr as $key_data => $val_data) {
						$data[$key_data] = [];
					}
				}
				
				foreach ($result as $key => $arr) {
					foreach ($arr as $key_data => $val_data) {
						$data[$key_data][$key]	= $val_data;
					}
				}
			} else {
				$error['status'] = 'error';
				$error['message'] = 'Data tidak ditemukan';
				load_view('views/error.php', $error, false, 'error');
				exit;
			}
		}
			
		$data['penanggungjawab'] = $penanggung_jawab;
	// echo '<pre>'; print_r($data); die;
		load_view('views/form.php', $data);
}

function validate_form() {
	require_once('app/libraries/FormValidation.php');
	$validation = new FormValidation();
	
	$validation->setRules('no_akta', 'Nomor Akta', 'trim|required');
	$validation->setRules('tgl_akta', 'Tanggal Akta', 'trim|required');
	$validation->setRules('nama_akta', 'Nama Akta', 'trim|required');
	$validation->setRules('nama_customer', 'Nama Customer', 'trim|required');
	// $validation->setRules('nama_customer', 'Nama Customer', 'trim|required');
		
	$validation->validate();
	$form_errors =  $validation->getMessage();
	
	return $form_errors;
}