<?php
/**
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

// print_r($_GET['action']); die;
switch ($_GET['action']) 
{
	default: 
		action_notfound();
	
	case 'ajaxGetKabupatenByIdPropinsi' :
		$result = [];
		if (!empty($_GET['id']) && is_numeric($_GET['id'])) {
			$sql = 'SELECT * FROM wilayah_kabupaten WHERE id_wilayah_propinsi = ?';
			$query = $db->query($sql, $_GET['id'])->getResultArray();
			foreach ($query as $val) {
				$result[$val['id_wilayah_kabupaten']] = $val['nama_kabupaten'];
			}
		}
		
		echo json_encode($result);
		break;

	case 'ajaxGetKecamatanByIdKabupaten' :
		$result = [];
		if (!empty($_GET['id']) && is_numeric($_GET['id'])) {
			$sql = 'SELECT * FROM wilayah_kecamatan WHERE id_wilayah_kabupaten = ?';
			$query = $db->query($sql, $_GET['id'])->getResultArray();
			foreach ($query as $val) {
				$result[$val['id_wilayah_kecamatan']] = $val['nama_kecamatan'];
			}
		}

		echo json_encode($result);
		break;
		
	case 'ajaxGetKelurahanByIdKecamatan' :
		$result = [];
		if (!empty($_GET['id']) && is_numeric($_GET['id'])) {
			$sql = 'SELECT * FROM wilayah_kelurahan WHERE id_wilayah_kecamatan = ?';
			$query = $db->query($sql, $_GET['id'])->getResultArray();
			foreach ($query as $val) {
				$result[$val['id_wilayah_kelurahan']] = $val['nama_kelurahan'];
			}
		}

		echo json_encode($result);
		break;		
}