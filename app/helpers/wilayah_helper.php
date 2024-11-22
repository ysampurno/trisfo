<?php
function get_data_wilayah($id_wilayah_kelurahan =null) {
	
	global $db, $config;
	
	// ID Kelurahan
	if ($id_wilayah_kelurahan) {
		$data['id_wilayah_kelurahan'] = $id_wilayah_kelurahan;
	} else {
		if (!empty($config['id_wilayah_kelurahan'])) {
			$data['id_wilayah_kelurahan'] = $config['id_wilayah_kelurahan'];
		} else {
			$data['id_wilayah_kelurahan'] = '';
		}
	}
	
	// ID Kecamatan
	if (empty($data['id_wilayah_kelurahan'])) {
			
		$sql = 'SELECT COUNT(*) as jml FROM wilayah_kecamatan';
		$result = $db->query($sql)->getRowArray();
		
		$sql = 'SELECT * FROM wilayah_kecamatan LIMIT ' . ceil($result['jml']/2) . ',1';
		$kecamatan = $db->query($sql)->getRowArray();
		
	} else {
		$sql = 'SELECT * FROM wilayah_kecamatan 
					LEFT JOIN wilayah_kelurahan USING(id_wilayah_kecamatan) 
					WHERE id_wilayah_kelurahan = ?';
		$kecamatan = $db->query($sql, $data['id_wilayah_kelurahan'])->getRowArray();
	}

	$data['id_wilayah_kecamatan'] = $kecamatan['id_wilayah_kecamatan'];
	
	// ID Kabupaten
	$sql = 'SELECT * FROM wilayah_kabupaten 
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kabupaten) 
				WHERE id_wilayah_kecamatan = ?';
	$kabupaten = $db->query($sql, $data['id_wilayah_kecamatan'])->getRowArray();
	$data['id_wilayah_kabupaten'] = $kabupaten['id_wilayah_kabupaten'];

	// ID Propinsi
	$sql = 'SELECT * FROM wilayah_propinsi 
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_propinsi) 
				WHERE id_wilayah_kabupaten = ?';
	$propinsi = $db->query($sql, $data['id_wilayah_kabupaten'])->getRowArray();
	$data['id_wilayah_propinsi'] = $propinsi['id_wilayah_propinsi'];
	
	// Default
	// $default_propinsi = set_value('id_wilayah_propinsi', $data['id_wilayah_propinsi']);
	// $default_kabupaten = set_value('id_wilayah_kabupaten', $data['id_wilayah_kabupaten']);
	// $default_kecamatan = set_value('id_wilayah_kecamatan', $data['id_wilayah_kecamatan']);
	
	// Data Propinsi
	$sql = 'SELECT * FROM wilayah_propinsi';
	$query = $db->query($sql)->getResultArray();
	foreach ($query as $val) {
		$data_propinsi[$val['id_wilayah_propinsi']] = $val['nama_propinsi'];
	}
	$data['propinsi'] =  $data_propinsi;
	
	// Data kabupaten
	$sql = 'SELECT * FROM wilayah_kabupaten WHERE id_wilayah_propinsi = ?';
	$query = $db->query($sql, $data['id_wilayah_propinsi'])->getResultArray();
	foreach ($query as $val) {
		$data_kabupaten[$val['id_wilayah_kabupaten']] = $val['nama_kabupaten'];
	}
	$data['kabupaten'] =  $data_kabupaten;
	
	// Data Kecamatan
	$sql = 'SELECT * FROM wilayah_kecamatan WHERE id_wilayah_kabupaten = ?';
	$query = $db->query($sql, $data['id_wilayah_kabupaten'])->getResultArray();
	foreach ($query as $val) {
		$data_kecamatan[$val['id_wilayah_kecamatan']] = $val['nama_kecamatan'];
	}
	$data['kecamatan'] = $data_kecamatan;
	
	// Data Kelurahan
	$sql = 'SELECT * FROM wilayah_kelurahan WHERE id_wilayah_kecamatan = ?';
	$query = $db->query($sql, $data['id_wilayah_kecamatan'])->getResultArray();
	foreach ($query as $val) {
		$data_kelurahan[$val['id_wilayah_kelurahan']] = $val['nama_kelurahan'];
	}
	$data['kelurahan'] = $data_kelurahan;
	
	$data['default_propinsi'] = $data['id_wilayah_propinsi'];
	$data['default_kabupaten'] = $data['id_wilayah_kabupaten'];
	$data['default_kecamatan'] = $data['id_wilayah_kecamatan'];
	$data['default_kelurahan'] = $data['id_wilayah_kelurahan'];

	return $data;
}