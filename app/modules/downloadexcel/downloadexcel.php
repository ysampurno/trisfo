<?php
/**
*	PHP Admin Template Jagowebdev
* 	Author	: Agus Prawoto Hadi
*	Website	: https://jagowebdev.com
*	Year	: 2021
*/

include_once(BASE_PATH . "/app/libraries/vendors/php_xlsxwriter/xlsxwriter.class.php");

$data['max_data'] = 100000;
$data['used_tabel'] = 'transaksi';
$data['list_tabel'] = ['mahasiswa' => 'Mahasiswa', 'transaksi' => 'Transaksi'];

if (!empty($_REQUEST['nama_tabel'])) {
	
	if (key_exists($_REQUEST['nama_tabel'], $data['list_tabel'])) {
		$data['used_tabel'] = $_REQUEST['nama_tabel'];
	}
}

$total_data = 0;
if (key_exists($data['used_tabel'], $data['list_tabel'])) {
	$sql = 'SELECT COUNT(*) AS total_data FROM ' . $data['used_tabel'];
	$query = $db->query($sql)->getRowArray();
	$total_data = $query['total_data'];
}

$js[] = BASE_URL . 'public/themes/modern/js/downloadexcel.js';

$site_title = 'Eskpor data Excel';

$js[] = ['print' => true, 'script' => 'var max_data = ' . $data['max_data'] . ';
									var total_data = ' . $total_data . ';'
		];
$js[] = THEME_URL . 'js/image-upload.js';

switch ($_GET['action']) 
{
	default: 
		action_notfound();
		
	case 'max-data' : 
		echo $data['max_data'];
		break;
		
	case 'countdata' :
		echo $total_data;
		exit;
		
	case 'index':
		
		helper('format');
		
		$data['title'] = 'Ekspor data Excel';
		$data['selisih'] = 0;
		$data['total_data'] = $total_data;
		
		if (isset($_GET['nama_tabel'])) 
		{
			$path = BASEPATH . 'public/tmp/';
			$form_errors = validate_form();
			$data['status'] = 'ok';
			if ($form_errors) {
				$data['status'] = 'error';
				$data['message'] = $form_errors;
			} else {
				
				if (empty($_GET['data_awal'])) {
					$_GET['data_awal'] = 1;
				}
				
				if (empty($_GET['data_akhir'])) {
					$_GET['data_akhir'] = $data['max_data'];
				}
				
				$_GET['data_awal'] =  preg_replace("/\D/", "", $_GET['data_awal']);
				$_GET['data_akhir'] =  preg_replace("/\D/", "", $_GET['data_akhir']);
				
				$offset = !empty($_GET['data_awal']) ? $_GET['data_awal'] - 1 : 0;
				$num_rows = $_GET['data_akhir'] - ($_GET['data_awal'] - 1);		
												
				// Cek Error Count
				$sql = 'SELECT COUNT(*) AS jml_data FROM (SELECT * FROM ' . $_GET['nama_tabel'] . ' LIMIT ' . $offset . ', ' . $num_rows . ') AS tabel';
				$jml_data = $db->query($sql)->getRowArray();
				
				if (!$jml_data['jml_data']) {
					$data['status'] = 'error';
					$data['message'] = 'Data tidak ditemukan';
				}
				
				if ($data['status'] == 'ok') {

					$result = $db->getFieldData($_GET['nama_tabel']);
					
					$int = ['int', 'tinyint', 'smallint', 'mediumint', 'bigint'];
					$date = ['date', 'year'];
					
					foreach ($result as $val) {
						$format = 'string';
						if (in_array($val['data_type'], $int)) {
							$format = 'integer';
						} else if (in_array($val['data_type'], $date)) {
							$format = 'date';
						} else if ($val['data_type'] == 'datetime') {
							$format = 'datetime';
							
						} else if ($val['data_type'] == 'time') {
							$format = 'time';
						} 
						
						$field[$val['column_name']] = $format;
					}
					
					$sql = 'SELECT * FROM ' . $_GET['nama_tabel'] . ' LIMIT ' . $offset . ', ' . $num_rows;
					$query = $db->query($sql);
					
					// Excel
					
					$sheet_name = strtoupper($_GET['nama_tabel']);
					$writer = new XLSXWriter();
					$writer->setAuthor('Some Author');
					$writer->writeSheetHeader($sheet_name,$field);
					while ($row = $db->fetch($query)) {
						$writer->writeSheetRow($sheet_name, $row);
					}
					
					$filename = 'TABEL ' . (strtoupper($_GET['nama_tabel'])) . '.xlsx';
					header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
					header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
					header('Content-Transfer-Encoding: binary');
					header('Cache-Control: must-revalidate');
					header('Pragma: public');  
					
					$writer->writeToStdOut();
					exit;

					// SPOUT 
					// require_once 'app/libraries/vendors/spout/src/Spout/Autoloader/autoload.php';
					
					/* $writer = WriterEntityFactory::createXLSXWriter();
					$writer->openToBrowser('TABEL ' . (strtoupper($_GET['nama_tabel'])) . '.xlsx'); // stream data directly to the browser

					$rowFromValues = WriterEntityFactory::createRowFromArray($field);
					$writer->addRow($rowFromValues);
					
					while ($row = $db->fetch($query)) {
						$rowFromValues = WriterEntityFactory::createRowFromArray($row);
						$writer->addRow($rowFromValues);
					}

					$writer->close();
					exit; */
				}
			}
		}

		load_view('views/form.php', $data);
}

function validate_form() {
	
	global $data;

	$form_errors = [];
	if (!$_GET['nama_tabel']) {
		$form_errors[] = 'Tabel belum didefinisikan';
	}
	if (!empty($_GET['nama_tabel'])) {
		
		if (@$_GET['data_akhir'] < @$_GET['data_awal']) {
			$form_errors[] = 'Data akhir lebih kecil dari data awal';
		}
		
		if (!key_exists($_GET['nama_tabel'], $data['list_tabel'])) {
			$form_errors[] = 'Tabel ' . $_GET['nama_tabel'] . ' tidak diperkenankan';
		}
		
		if (!empty($_GET['data_akhir'])) {
					
			if (@$_GET['data_akhir'] < $data['max_data']) {
				$num_rows = $_GET['data_akhir'];
			}
			// echo 'xxx<pre>'; print_r($_GET);
			if ($_GET['data_akhir'] > $data['total_data'] || $_GET['data_awal'] > $data['total_data']) 
			{
				$form_errors[] = $data['error_field'] = 'Data awal ('. format_ribuan($_GET['data_awal']) . ') atau data akhir ('. format_ribuan($_GET['data_akhir']) . ') melebih jumlah maksimal data, yaitu <strong> '. format_ribuan($data['total_data']) . '</strong>';
			}
			
			
			$data['selisih'] = @$_GET['data_akhir'] - @$_GET['data_awal'];
			
			if ($data['selisih'] > $data['max_data']) {
				$form_errors[] = $data['error_field'] = 'Data akhir dikurangi data awal (' . format_ribuan($_GET['data_akhir']) . ' - ' . format_ribuan($_GET['data_awal']) . ') melebih batas yang diperkenankan, yaitu <strong>' . format_ribuan($data['max_data']) . '</strong>';
			}
		}
	}
	
	return $form_errors;
}