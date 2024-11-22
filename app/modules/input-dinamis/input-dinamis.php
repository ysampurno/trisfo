<?php
/**
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2022
*/

$site_title = 'Daftar Penghadap';
$js[] = BASE_URL . 'public/themes/modern/js/penghadap.js';

switch ($_GET['action']) 
{
	default: 
		action_notfound();
		
	// INDEX 
	case 'index':
		
		if (!empty($_POST['delete'])) 
		{
			$result = $db->delete('penghadap', ['id_penghadap' => $_POST['id']]);
			// $result = true;
			if ($result) {
				$data['msg'] = ['status' => 'ok', 'message' => 'Data penghadap berhasil dihapus'];
			} else {
				$data['msg'] = ['status' => 'error', 'message' => 'Data penghadap gagal dihapus'];
			}
		}
		$sql = 'SELECT * FROM penghadap';
		$data['result'] = $db->query($sql)->result();
		
		if (!$data['result']) {
			$data['msg']['status'] = 'error';
			$data['msg']['content'] = 'Data tidak ditemukan';
		}
		
		load_view('views/result.php', $data);
	
	case 'add': 
		
	case 'edit': 
			
		$data['title'] = 'Tambah Data Penghadap';
		if (empty($_GET['id'])) 
		{
			$breadcrumb['Add'] = '';
		}
		
		// Submit
		$data['msg'] = [];
		if (isset($_POST['submit'])) 
		{
			require_once('app/libraries/FormValidation.php');
			
			$validation = new FormValidation();
			foreach ($_POST['nama_penghadap'] as $key => $val) {
				$validation->setRules('nama_penghadap[' . $key . ']', 'Nama Penghadap', 'required');
			}
						
			$validation->validate();
			$form_errors =  $validation->getMessage();
			
							
			if ($form_errors) {
				$data['msg']['status'] = 'error';
				$data['msg']['content'] = $form_errors;
			} else {
				
				foreach ($_POST['nama_penghadap'] as $key => $val) {
					$data_db[] = ['nama_penghadap' => $val
								, 'gelar_depan' => $_POST['gelar_depan'][$key]
								, 'gelar_belakang' => $_POST['gelar_belakang'][$key]
								, 'jenis_kelamin' => $_POST['jenis_kelamin'][$key]
							];
				}
				
				$query = false;
				// echo '<pre>'; print_r($data_db); die;
				// EDIT
				if (!empty($_POST['id'])) 
				{
					$query = $db->update('penghadap', $data_db[0], 'id_penghadap = ' . $_POST['id']);
					
				} else {
					$query = $db->insertBatch('penghadap', $data_db);		
				}
				
			
				if ($query) {
					$data['msg']['status'] = 'ok';
					$data['msg']['content'] = 'Data berhasil disimpan';
				} else {
					$data['msg']['status'] = 'error';
					$data['msg']['content'] = 'Data gagal disimpan';
				}
				
				$data['title'] = 'Edit Data Penghadap';
			}
		}
		
		if (!empty($_GET['id'])) 
		{
			if (empty($_POST['nama_penghadap'])) {
				$sql = 'SELECT * FROM penghadap WHERE id_penghadap = ?';
				$result = $db->query($sql, trim($_GET['id']))->result();
				foreach ($result as $arr) {
					foreach ($arr as $key => $val) {
						$_POST[$key][]	= $val;
					}
				}
			}
			// echo '<pre>'; print_r($_POST);
			
			$breadcrumb['Edit'] = '';
			$data['title'] = 'Edit Data Penghadap';
		}
	// echo '<pre>'; print_r($data); die;
		load_view('views/form.php', $data);
}