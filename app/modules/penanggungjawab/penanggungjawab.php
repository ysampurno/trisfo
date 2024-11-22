<?php
/**
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2022
*/

$site_title = 'Daftar Penanggung Jawab';

switch ($_GET['action']) 
{
	default: 
		action_notfound();
		
	// INDEX 
	case 'index':
		
		if (!empty($_POST['delete'])) 
		{
			$result = $db->delete('penanggung_jawab', ['id_penanggung_jawab' => $_POST['id']]);
			// $result = true;
			if ($result) {
				$data['msg'] = ['status' => 'ok', 'message' => 'Data penanggung_jawab berhasil dihapus'];
			} else {
				$data['msg'] = ['status' => 'error', 'message' => 'Data penanggung_jawab gagal dihapus'];
			}
		}
		$sql = 'SELECT * FROM penanggung_jawab';
		$data['result'] = $db->query($sql)->result();
		
		if (!$data['result']) {
			$data['msg']['status'] = 'error';
			$data['msg']['content'] = 'Data tidak ditemukan';
		}
		
		load_view('views/result.php', $data);
	
	case 'add': 
	
	case 'edit': 
			
		$data['title'] = 'Tambah Data Penanggung Jawab';
		$breadcrumb['Add'] = '';
	
		// Submit
		$data['msg'] = [];
		if (isset($_POST['submit'])) 
		{
			require_once('app/libraries/FormValidation.php');
			$validation = new FormValidation();
			$validation->setRules('nama_penanggung_jawab', 'Nama Penanggung Jawab', 'required');
						
			$validation->validate();
			$form_errors =  $validation->getMessage();
			
							
			if ($form_errors) {
				$data['msg']['status'] = 'error';
				$data['msg']['content'] = $form_errors;
			} else {
		
				$data_db['nama_penanggung_jawab'] = $_POST['nama_penanggung_jawab'];
				$query = false;
				
				// EDIT
				if (!empty($_POST['id'])) 
				{
					$query = $db->update('penanggung_jawab', $data_db, 'id_penanggung_jawab = ' . $_POST['id']);
					
				} else {
					$query = $db->insert('penanggung_jawab', $data_db);		
				}
				
			
				if ($query) {
					$data['msg']['status'] = 'ok';
					$data['msg']['content'] = 'Data berhasil disimpan';
				} else {
					$data['msg']['status'] = 'error';
					$data['msg']['content'] = 'Data gagal disimpan';
				}
				
				$data['title'] = 'Edit Data Penanggung Jawab';
			}
		}
		
		if (!empty($_GET['id'])) 
		{
			$breadcrumb['Edit'] = '';
			$sql = 'SELECT * FROM penanggung_jawab WHERE id_penanggung_jawab = ?';
			$result = $db->query($sql, trim($_GET['id']))->result();
			foreach ($result as $arr) {
				foreach ($arr as $key => $val) {
					$data[$key]	= $val;
				}
			}
			
			$data['title'] = 'Edit Data Penanggung Jawab';
		}
	// echo '<pre>'; print_r($data); die;
		load_view('views/form.php', $data);
}