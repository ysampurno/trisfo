<?php
/**
*	App Name	: PHP Admin Template Dashboard	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2022
*/

$js[] = BASE_URL . 'public/themes/modern/js/data-tables-ajax.js';
$js[] = BASE_URL . 'public/themes/modern/js/pdfkirimemail.js';

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
				$del = delete_file($config['foto_path'] . $img['foto']);
				if (!$del) {
					return false;
				}
			}
			
			// $result = $db->delete('mahasiswa', ['id_mahasiswa' => $_POST['id']]);
			$result = true;
			if ($result) {
				$data['msg'] = ['status' => 'ok', 'message' => 'Data Mahasiswa berhasil dihapus'];
			} else {
				$data['msg'] = ['status' => 'error', 'message' => 'Data Mahasiswa gagal dihapus'];
			}
		}
		
		$sql = 'SELECT * FROM mahasiswa';
		$data['result'] = $db->query($sql)->getResultArray();
		
		if (!$data['result']) {
			$data['msg']['status'] = 'error';
			$data['msg']['content'] = 'Data tidak ditemukan';
		}
		
		load_view('views/result.php', $data);
	
	
	case 'pdf': 

		$sql = 'SELECT * FROM mahasiswa WHERE id_mahasiswa = ?';
		$result = $db->query($sql, $_GET['id'])->getRowArray();
		$data['nama'] = $result;
		extract($data);

		require_once BASEPATH . 'app/libraries/vendors/mpdf/autoload.php';

		$mpdf = new \Mpdf\Mpdf();

		$html = '
		<style>
		body {
			font-size: 10px;
			font-family:arial;
		}

		td {
			padding:0;
			padding-right: 5px;
			padding-bottom: 0;
		}

		label {width: 200px; display: block}
		</style>

		<div class="container" style="margin-top:-155px;margin-left:85px">
			<table cellspacing="0" cellpadding="0">
				<tr style="height: 5px;">
					<td>Nama</td>
					<td>:</td>
					<td>' . $nama['nama'] . '</td>
				</tr>
				<tr>
					<td>TTL</td>
					<td>:</td>
					<td>' . $nama['tempat_lahir']. ', ' . format_tanggal($nama['tgl_lahir']) . '</td>
				</tr>
				<tr>
					<td>NPM</td>
					<td>:</td>
					<td>' . $nama['npm'] . '</td>
				</tr>
				<tr>
					<td>Prodi</td>
					<td>:</td>
					<td>' . $nama['prodi'] . '</td>
				</tr>
				<tr>
					<td>Fakultas</td>
					<td>:</td>
					<td>' . $nama['fakultas'] . '</td>
				</tr>
				<tr>
					<td>Alamat</td>
					<td>:</td>
					<td>' . $nama['alamat'] . '</td>
				</tr>
				
			</table>
		</div>';

		$html_tandatangan = '
		<style>
		body {
			font-size: 10px;
			font-family:arial;
		}

		.tanggal {
			text-align:center;
			margin-left: -190px;
			margin-top: 5px;
			line-height: 11px;
		}

		</style>

		<div class="tanggal">
		Solo, ' . format_tanggal(date('Y-m-d')) . '<br/>
		Rektor,
		<br/>
		<br/>
		<br/>
		Agus Prawoto Hadi
		<br/>
		NIP. 19900829 201301 1003</div>';
		
		$html_masaberlaku = '
		<style>
		.masa-berlaku {
			margin-left: 30px;
			margin-top: -20px;
		}
		</style>
		<div class="masa-berlaku">Berlaku s.d ' . format_tanggal( date('Y-m-d', strtotime('+ 5 year', time())) ) . '</div>';

		$mpdf->addPage();
		$x = 10;
		$y = 15;
		
		$photo_path = BASEPATH . 'public/images/foto/' . $nama['foto'];
		if (!file_exists($photo_path)) {
			$nama['foto'] = 'noimage.png';
		}
		
		$mpdf->Image('public/images/kartu/kartu_depan.png' , $x, $y, 90, 0, 'png');
		$mpdf->Image('public/images/kartu/kartu_belakang.png', $x + 90 + 10, $y, 90, 0, 'png');
		$mpdf->WriteHTML($html);
		$mpdf->WriteHTML($html_tandatangan);
		$mpdf->WriteHTML($html_masaberlaku);
		$mpdf->Image('public/images/foto/' . $nama['foto'], $x + 5.3, $y + 16.4 , 20, 0, 'jpg');
		$mpdf->Image('public/images/kartu/tanda_tangan_kartu.png', $x + 57, $y + 37, 18.5, 0, 'png');
		$mpdf->Image('public/images/kartu/stempel.png', $x + 45, $y + 35, 18.5, 0, 'png');
		$mpdf->Image('public/images/kartu/qrcode.png', $x + 90 + 10 + 70, $y + 33, 15, 0, 'png');
		$mpdf->debug = true;
		$mpdf->showImageErrors = true;

		if (!empty($_POST['email'])) 
		{
			$filename = 'public/tmp/kartu_'. time() . '.pdf';
			$mpdf->Output($filename,'F');
			
			require_once 'app/config/email.php';
			$email_config = new EmailConfig;
			$email_data = array('from_email' => $email_config->from
							, 'from_title' => 'Aplikasi Kartu Elektronik'
							, 'to_email' => $_POST['email']
							, 'to_name' => $nama['nama']
							, 'email_subject' => 'Permintaan Kartu Elektronik'
							, 'email_content' => '<h1>KARTU ELEKTRONIK</h1><h2>Hi, ' . $nama['nama'] . '</h2><p>Berikut kami sertakan kartu elektronik atas nama Anda. Anda dapat mengunduhnya pada bagian Attachment.<br/><br/><p>Salam</p>'
							, 'attachment' => ['path' => $filename, 'name' => 'Kartu Elektronik.pdf']
			);
			
			require_once('app/libraries/PhpmailerLib.php');

			$phpmailer = new \App\Libraries\PhpmailerLib;
			$phpmailer->init();
			$phpmailer->setProvider($email_config->provider);
			$send_email =  $phpmailer->send($email_data);

			unlink($filename);
			if ($send_email['status'] == 'ok') {
				$message['status'] = 'ok';
				$message['message'] = 'Kartu elektronik berhasil dikirim ke alamat email: ' . $_POST['email'];
			} else {
				$message['status'] = 'error';
				$message['message'] = 'Kartu elektronik gagal dikirim ke alamat email: ' . $_POST['email'] . '<br/> Error: ' . $send_email['message'];
			}
			echo json_encode($message);
			exit();
		}
		// $mpdf->Output();
		$mpdf->Output('Kartu Elektronik.pdf', 'D');

		exit();
	
	case 'getDataDT':
		
		$sql = 'SELECT COUNT(*) AS jml FROM mahasiswa';
		$result = $db->query($sql)->getRowArray();
		$num_data = $result['jml'];
		
		$result['draw'] = $start = $_POST['draw'] ?: 1;
		$result['recordsTotal'] = $num_data;
		$result['recordsFiltered'] = $num_data;
		$query = getListData();
				
		helper('html');
		$id_user = $_SESSION['user']['id_user'];
		
		foreach ($query as $key => &$val) 
		{
			$foto = 'Anonymous.png';
			if ($val['foto']) {
				if (file_exists('public/images/foto/' . $val['foto'])) {
					$foto = $val['foto'];
				} else {
					$foto = 'noimage.png';
				}
			}
			
			$val['foto'] = '<div class="list-foto"><img src="'. BASE_URL.'public/images/foto/' . $foto . '"/></div>';
			
			$val['tgl_lahir'] = $val['tempat_lahir'] . ', '. format_tanggal($val['tgl_lahir']);
			
			

			$val['ignore_search_action'] = btn_action([
												'pdf' => ['url' => BASE_URL . $current_module['nama_module'] . '/pdf?id='. $val['id_mahasiswa']
													, 'btn_class' => 'btn-danger me-1'
													, 'icon' => 'fas fa-file-pdf'
													, 'text' => 'PDF'
												],
												'Email' => ['url' => '#'
																, 'btn_class' => 'btn-primary me-1 kirim-email'
																, 'icon' => 'fas fa-paper-plane'
																, 'text' => 'Email'
																, 'attr' => ['data-id' => $val['id_mahasiswa'], 'data-email' => $val['email'], 'target' => '_blank']
												]
										
											]);
		}
					
		$result['data'] = $query;
		echo json_encode($result); exit();
}

function getListData() {
	
	global $db;
	$columns = $_POST['columns'];
	$order_by = '';
	
	// Search
	$search_all = @$_POST['search']['value'];
	$where = '1 = 1';
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
	$order = $_POST['order'];
	
	if (@$order[0]['column'] != '' ) {
		$order_by = ' ORDER BY ' . $columns[$order[0]['column']]['data'] . ' ' . strtoupper($order[0]['dir']);
	}

	$start = $_POST['start'] ?: 0;
	$length = $_POST['length'] ?: 10;
	
	// Query Data
	$sql = 'SELECT * FROM mahasiswa WHERE 
			' . $where . $order_by . ' LIMIT ' . $start . ', ' . $length;

	return $db->query($sql)->getResultArray();
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