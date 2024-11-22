<?php
/**
* PHP Admin Template
* Author	: Agus Prawoto Hadi
* Website	: https://jagowebdev.com
* Year		: 2021-2022
*/

$js[] = BASE_URL . 'public/vendors/chartjs/chart.js';
$styles[] = BASE_URL . 'public/vendors/material-icons/css.css';

$js[] = BASE_URL . 'public/vendors/datatables/extensions/Buttons/js/dataTables.buttons.min.js';
$js[] = BASE_URL . 'public/vendors/datatables/extensions/Buttons/js/buttons.bootstrap5.min.js';
$js[] = BASE_URL . 'public/vendors/datatables/extensions/JSZip/jszip.min.js';
$js[] = BASE_URL . 'public/vendors/datatables/extensions/pdfmake/pdfmake.min.js';
$js[] = BASE_URL . 'public/vendors/datatables/extensions/pdfmake/vfs_fonts.js';
$js[] = BASE_URL . 'public/vendors/datatables/extensions/Buttons/js/buttons.html5.min.js';
$js[] = BASE_URL . 'public/vendors/datatables/extensions/Buttons/js/buttons.print.min.js';
$styles[] = BASE_URL . 'public/vendors/datatables/extensions/Buttons/css/buttons.bootstrap5.min.css';

$styles[] = BASE_URL . 'public/themes/modern/css/dashboard.css';
$js[] = BASE_URL . 'public/themes/modern/js/dashboard.js';

helper('format');

switch ($_GET['action']) 
{
    default: 
        action_notfound();
	
	case 'getDataDTPenjualanTerbesar':

		$result['draw'] = $start = $_POST['draw'] ?: 1;
		
		$data_table = getListData($_GET['tahun']);
		$result['recordsTotal'] = $data_table['total_data'];
		$result['recordsFiltered'] = $data_table['total_filtered'];
				
		helper('html');
		$id_user = $_SESSION['user']['id_user'];
		
		$no = $_POST['start'] + 1 ?: 1;
		
		foreach ($data_table['data'] as $key => &$val) 
		{
			$val['ignore_search_urut'] = $no;
			$val['harga_satuan'] = format_number($val['harga_satuan']);
			$val['jml_terjual'] = format_number($val['jml_terjual']);
			$val['total_harga'] = format_number($val['total_harga']);
			$val['kontribusi'] = $val['kontribusi'] . '%';
			$no++;
		}
					
		$result['data'] = $data_table['data'];
		echo json_encode($result); 
		exit();
	
	case 'ajaxGetPenjualanTerbaru':

		$sql = 'SELECT nama_pelanggan, SUM(jml_barang) AS jml_barang, MAX(total_harga) AS total_harga, MAX(tgl_transaksi) AS tgl_transaksi FROM toko_penjualan 
				LEFT JOIN toko_penjualan_detail USING(id_penjualan)
				LEFT JOIN toko_pelanggan USING(id_pelanggan)
				WHERE tgl_transaksi LIKE "' . $_GET['tahun'] . '%"
				GROUP BY id_penjualan
				ORDER BY tgl_transaksi DESC LIMIT 50';
		
		$result = $db->query($sql)->getResultArray();
		if (!$result)
			exit;
		
		foreach ($result as &$val) {
			$val['total_harga'] = format_number($val['total_harga']);
			$val['jml_barang'] = format_number($val['jml_barang']);
			$val['status'] = 'selesai';
		}
		
		echo json_encode($result);
		exit();
	
	case 'ajaxGetItemTerjual':
	
		$sql = 'SELECT id_barang, nama_barang, COUNT(id_barang) AS jml
				FROM toko_penjualan_detail
				LEFT JOIN toko_penjualan USING(id_penjualan)
				LEFT JOIN toko_barang USING(id_barang)
				WHERE tgl_transaksi >= "' . $_GET['tahun'] . '-01-01" AND tgl_transaksi <= "' . $_GET['tahun'] . '-12-31"
				GROUP BY id_barang
				ORDER BY jml DESC LIMIT 7';
				
		$result = $db->query($sql)->getResultArray();
		if (!$result)
			return;
	
		$total = [];
		$nama_item = [];
		foreach ($result as $val) {
			$total[] = $val['jml'];
			$nama_item[] = $val['nama_barang'];
		}
		
		echo json_encode(['total' => $total, 'nama_item' => $nama_item]);
		exit;
	
	case 'ajaxGetKategoriTerjual':
	
		$sql = 'SELECT id_kategori, nama_kategori, COUNT(id_barang) AS jml, SUM(harga) AS nilai
				FROM toko_penjualan_detail
				LEFT JOIN toko_penjualan USING(id_penjualan)
				LEFT JOIN toko_barang USING(id_barang)
				LEFT JOIN toko_barang_kategori USING(id_kategori)
				WHERE tgl_transaksi >= "' . $_GET['tahun'] . '-01-01" AND tgl_transaksi <= "' . $_GET['tahun'] . '-12-31"
				GROUP BY id_kategori
				ORDER BY nilai DESC LIMIT 7';
				
        $result = $db->query($sql)->getResultArray();
		if (!$result)
			return;
		
		$total = [];
		$nama_kategori = [];
		foreach ($result as &$val) {
			$total[] = $val['jml'];
			$nama_kategori[] = $val['nama_kategori'];
			$val['jml'] = format_number($val['jml']);
			$val['nilai'] = format_number($val['nilai']);
		}
		
		echo json_encode(['total' => $total, 'nama_kategori' => $nama_kategori, 'item_terjual' => $result]);
		exit;
		
	case 'ajaxGetPelangganTerbesar':
	
		$sql = 'SELECT id_pelanggan, foto, nama_pelanggan, SUM(total_harga) AS total_harga FROM toko_penjualan
				LEFT JOIN toko_pelanggan USING(id_pelanggan)
				WHERE YEAR(tgl_transaksi) = ' . $_GET['tahun'] . '
				GROUP BY id_pelanggan
				ORDER BY total_harga DESC
				LIMIT 5';
				
		$result = $db->query($sql)->getResultArray();
		if (!$result)
			return;
		
		foreach ($result as &$val) {
			$val['total_harga'] = format_number($val['total_harga']);
			$val['foto'] = '<img src="' . BASE_URL . 'public/images/pelanggan/' . $val['foto'] . '">';
		}
		
		echo json_encode($result);
		exit;
		
    	// INDEX 
    case 'index':
		
		helper('format');
		
		// list tahun
		$sql= 'SELECT YEAR(tgl_transaksi) AS tahun
				FROM toko_penjualan
				GROUP BY tahun';
		$result = $db->query($sql)->getResultArray();
		
		$list_tahun = [];
		foreach ($result as $val) {
			$list_tahun[$val['tahun']] = $val['tahun'];
		}
		
		$tahun = max($list_tahun);
		
		$data['list_tahun'] = $list_tahun;
		$data['tahun'] = $tahun;
		
		// Baris pertama	
		$sql = 'SELECT jml, jml_prev, ROUND((jml - jml_prev)/ jml_prev * 100, 2) AS growth
				FROM (
					SELECT COUNT(IF(tgl_transaksi LIKE "' . $tahun . '%", id_barang, NULL)) AS jml,
							COUNT(IF(tgl_transaksi LIKE "' . ($tahun - 1) . '%", id_barang, NULL)) AS jml_prev	
					FROM toko_penjualan_detail
					LEFT JOIN toko_penjualan USING(id_penjualan)
					WHERE tgl_transaksi LIKE "' . $tahun . '%" OR tgl_transaksi LIKE "' . ($tahun - 1) . '%"
				) AS tabel';
		$result = $db->query($sql)->getRowArray();
		$data['total_item_terjual'] = $result;
		
		$sql = 'SELECT jml, jml_prev, ROUND((jml - jml_prev)/ jml_prev * 100, 2) AS growth
				FROM (
					SELECT COUNT(IF(tgl_transaksi LIKE "' . $tahun . '%", id_penjualan, NULL)) AS jml,
							COUNT(IF(tgl_transaksi LIKE "' . ($tahun - 1) . '%", id_penjualan, NULL)) AS jml_prev
					FROM toko_penjualan
					WHERE tgl_transaksi LIKE "' . $tahun . '%" OR tgl_transaksi LIKE "' . ($tahun - 1) . '%"
				) AS tabel';
		$result = $db->query($sql)->getRowArray();
		$data['total_jumlah_transaksi'] = $result;
		
		$sql = 'SELECT jml, jml_prev, ROUND((jml - jml_prev)/ jml_prev * 100, 2) AS growth
				FROM (
					SELECT SUM(IF(tgl_transaksi LIKE "' . $tahun . '%", total_harga, NULL)) AS jml,
							SUM(IF(tgl_transaksi LIKE "' . ($tahun - 1) . '%", total_harga, NULL)) AS jml_prev
					FROM toko_penjualan
					WHERE tgl_transaksi LIKE "' . $tahun . '%" OR tgl_transaksi LIKE "' . ($tahun - 1) . '%"
				) AS tabel';
		$result = $db->query($sql)->getRowArray();
		$data['total_nilai_penjualan'] = $result;
		
		$sql = 'SELECT jml, jml_prev, ROUND( (jml-jml_prev) / jml_prev * 100 ) AS  growth, total FROM (
					SELECT COUNT(jml) AS jml, COUNT(jml_prev) AS jml_prev, (SELECT COUNT(*) FROM toko_pelanggan) AS total
					FROM (
						SELECT MAX(IF(tgl_transaksi LIKE "' . $tahun . '%", 1, NULL)) AS jml,
								MAX(IF(tgl_transaksi LIKE "' . ( $tahun - 1 ) . '%", 1, NULL)) AS jml_prev
						 FROM toko_penjualan
						WHERE tgl_transaksi LIKE "' . $tahun . '%" OR tgl_transaksi LIKE "' . ($tahun - 1) . '%"
						GROUP BY id_pelanggan
					) AS tabel
				) tabel_utama';
				
		$result = $db->query($sql)->getRowArray();
		$data['total_pelanggan_aktif'] = $result;
		
		// Baris berikutnya
		$result = [];
		foreach ($list_tahun as $tahun) {
			 $sql = 'SELECT MONTH(tgl_transaksi) AS bulan, COUNT(id_penjualan) as JML, SUM(total_harga) total
					FROM toko_penjualan
					WHERE tgl_transaksi >= "' . $tahun . '-01-01" AND tgl_transaksi <= "' . $tahun . '-12-31"
					GROUP BY MONTH(tgl_transaksi)';
			
			$result[$tahun] = $db->query($sql)->getResultArray();
		}
		$data['penjualan'] = $result;
		
		$result = [];
		foreach ($list_tahun as $tahun) {
			 $sql = 'SELECT SUM(total_harga) AS total
					FROM toko_penjualan
					WHERE tgl_transaksi >= "' . $tahun . '-01-01" AND tgl_transaksi <= "' . $tahun . '-12-31"';
			
			$result[$tahun] = $db->query($sql)->getResultArray();
		}
		$data['total_penjualan'] = $result;
		
		$sql = 'SELECT id_barang, nama_barang, COUNT(id_barang) AS jml
				FROM toko_penjualan_detail
				LEFT JOIN toko_penjualan USING(id_penjualan)
				LEFT JOIN toko_barang USING(id_barang)
				WHERE tgl_transaksi >= "' . $tahun . '-01-01" AND tgl_transaksi <= "' . $tahun . '-12-31"
				GROUP BY id_barang
				ORDER BY jml DESC LIMIT 7';
				
        $result = $db->query($sql)->getResultArray();
		$data['item_terjual'] = $result;
		
		$sql = 'SELECT id_kategori, nama_kategori, COUNT(id_barang) AS jml, SUM(harga) AS nilai
				FROM toko_penjualan_detail
				LEFT JOIN toko_penjualan USING(id_penjualan)
				LEFT JOIN toko_barang USING(id_barang)
				LEFT JOIN toko_barang_kategori USING(id_kategori)
				WHERE tgl_transaksi >= "' . $tahun . '-01-01" AND tgl_transaksi <= "' . $tahun . '-12-31"
				GROUP BY id_kategori
				ORDER BY nilai DESC LIMIT 7';
				
        $result = $db->query($sql)->getResultArray();
		$data['kategori_terjual'] = $result;
		
		$sql = 'SELECT id_pelanggan, foto, nama_pelanggan, SUM(total_harga) AS total_harga FROM toko_penjualan
				LEFT JOIN toko_pelanggan USING(id_pelanggan)
				WHERE YEAR(tgl_transaksi) = ' . $tahun . '
				GROUP BY id_pelanggan
				ORDER BY total_harga DESC
				LIMIT 5';
		$result = $db->query($sql)->getResultArray();
		$data['pelanggan_terbesar'] = $result;
		
		$sql = 'SELECT * FROM toko_barang ORDER BY tgl_input DESC LIMIT 5';
		$result = $db->query($sql)->getResultArray();
		foreach ($result as &$val) {
			$val['harga_jual'] = format_number($val['harga_jual']);
		}
		
		$data['item_terbaru'] = $result;
		
		$data['message']['status'] = 'ok';
        if (empty($data['penjualan'])) {
            $data['message']['status'] = 'error';
            $data['message']['message'] = 'Data tidak ditemukan';
		}
		
		load_view('views/result.php', $data);
}

function getListData($tahun) {
	
	global $db;
	$columns = $_POST['columns'];
	$order_by = '';
	
	// Search
	$search_all = @$_POST['search']['value'];
	$where = ' WHERE 1=1 ';
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
	$sql = 'SELECT COUNT(*) as jml_data
				FROM (SELECT id_barang FROM toko_penjualan_detail
					LEFT JOIN toko_penjualan USING(id_penjualan)
					WHERE tgl_transaksi >= "' . $tahun . '-01-01" AND tgl_transaksi <= "' . $tahun . '-12-31"
					GROUP BY id_barang) AS tabel';
				
	$query = $db->query($sql)->getRowArray();
	$total_data = $query['jml_data'];
	
	// Query Filtered
	$sql = '
			SELECT tabel_utama.*, COUNT(*) AS jml_data 
			FROM (
				SELECT tabel.*, ROUND(total_harga / total_penjualan * 100, 0) AS kontribusi 
				FROM (
					SELECT id_barang, nama_barang, harga_satuan, COUNT(id_barang) AS jml_terjual, SUM(harga) AS total_harga,
						(SELECT SUM(harga) FROM toko_penjualan_detail LEFT JOIN toko_penjualan USING(id_penjualan) WHERE tgl_transaksi >= "'. $tahun . '-01-01" AND tgl_transaksi <= "' . $tahun . '-12-31") AS total_penjualan
					FROM toko_penjualan_detail
					LEFT JOIN toko_penjualan USING(id_penjualan)
					LEFT JOIN toko_barang USING(id_barang)
					 
					GROUP BY id_barang
				) AS tabel
			) AS tabel_utama
			' . $where;
	$total_filtered = $db->query($sql)->getRowArray()['jml_data'];
	
	// Query Data
	$start = $_POST['start'] ?: 0;
	$length = $_POST['length'] ?: 10;
	$sql = '
			SELECT * FROM (
				SELECT tabel.*, ROUND(total_harga / total_penjualan * 100, 0) AS kontribusi 
				FROM (
					SELECT id_barang, nama_barang, harga_satuan, COUNT(id_barang) AS jml_terjual, SUM(harga) AS total_harga,
						(SELECT SUM(harga) FROM toko_penjualan_detail LEFT JOIN toko_penjualan USING(id_penjualan) WHERE tgl_transaksi >= "' . $tahun . '-01-01" AND tgl_transaksi <= "' . $tahun . '-12-31") AS total_penjualan
					FROM toko_penjualan_detail
					LEFT JOIN toko_penjualan USING(id_penjualan)
					LEFT JOIN toko_barang USING(id_barang)
					WHERE tgl_transaksi >= "' . $tahun . '-01-01" AND tgl_transaksi <= "' . $tahun . '-12-31"
					GROUP BY id_barang
				) AS tabel
			) AS tabel_utama
			' . $where . $order . ' LIMIT ' . $start . ', ' . $length;

	$data = $db->query($sql)->getResultArray();
	
	return ['total_data' => $total_data, 'total_filtered' => $total_filtered, 'data' => $data];
}