<?php
/**
* PHP Admin Template
* Author	: Agus Prawoto Hadi
* Website	: https://jagowebdev.com
* Year		: 2021-2022
*/

$js[] = BASE_URL . 'public/vendors/jwdfilepicker/jwdfilepicker.js';
$js[] = $config['base_url'] . 'public/themes/modern/js/jwdfilepicker-defaults.js';
$js[] = BASE_URL . 'public/vendors/dropzone/dropzone.min.js';

$styles[] = $config['base_url'] . 'public/vendors/dropzone/dropzone.min.css';
$styles[] = BASE_URL . 'public/vendors/jwdfilepicker/jwdfilepicker.css';
$styles[] = BASE_URL . 'public/vendors/jwdfilepicker/jwdfilepicker-loader.css';
$styles[] = BASE_URL . 'public/vendors/jwdfilepicker/jwdfilepicker-modal.css';
$js[] = $config['base_url'] . 'public/themes/modern/js/filedownload.js';

switch ($_GET['action']) 
{
    default: 
        action_notfound();
    
    	// INDEX 
    case 'index':
        $message = [];
        if (!empty($_POST['delete'])) {
			
			cek_hakakses('delete_data');
			
            $delete = $db->delete('file_download', ['id_file_download' => $_POST['id']]);
			// $delete =  true;
			
            if ($delete) {
                $message['status'] = 'ok';
                $message['message'] = 'Data berhasil dihapus';
            } else {
                $message['status'] = 'error';
                $message['message'] = 'Data gagal dihapus';
            }
        }
		
        $sql 	= 'SELECT * FROM file_download LEFT JOIN file_picker USING(id_file_picker) ' . where_own();
        $result = $db->query($sql)->getResultArray();

        $data['result'] = $result;
        $data['message'] = $message;

        if (!$data['result'])
            data_notfound();

        load_view('views/result.php', $data);
    
    case 'add':
		
		$data['title'] = 'Add File Download';
        $result = ['message' => '', 'id' => '', 'id_file_picker' => ''];
        if (!empty($_POST['submit'])) {
            $result = save_data();
			if ($result['message']['status'] == 'ok') {
				$data['title'] = 'Edit File Download';
			}
        }

        $data['message'] = $result['message'];
        $data['id'] = $result['id'];
        $data['id_file_picker'] = $result['id_file_picker'];

        load_view('views/form.php', $data);

    case 'edit':
		
		cek_hakakses('update_data');
		
        if (empty($_GET['id']))
            data_notfound();

        $result['message'] = [];
        if (!empty($_POST['submit'])) {
            $result = save_data();
        }

        $sql 	= 'SELECT * FROM file_download LEFT JOIN file_picker USING(id_file_picker) WHERE id_file_download = ?';
        $file_download = $db->query($sql, $_GET['id'])->getRowArray();

        if (!$file_download)
            data_notfound();

        $data['title'] = 'Edit Data File Download';
        $data['file_download'] = $file_download;
        $data['message'] = $result['message'];
        $data['id'] = $_GET['id'];
        $data['id_file_picker'] = $file_download['id_file_picker'];
        load_view('views/form.php', $data);
	
	case 'download':
		
		$id_file = $_GET['id'];
		
		$sql = 'SELECT * FROM file_download 
						LEFT JOIN file_picker USING(id_file_picker) 
				WHERE id_file_download = ?';
		$file = $db->query($sql, $id_file)->getRowArray();
		
		if (!$file) {
			data_notfound();
		}
		
		$file_path = $config['filepicker_upload_path'] . $file['nama_file'];
		// echo $file_path;
		if (!file_exists($file_path)) {
			exit_error( 'File ' . $file['nama_file'] . ' tidak ditemukan, mohon menghubungi admin, terima kasih' );
		}
		
		$data_db['id_user'] = $_SESSION['user']['id_user'];
		$data_db['id_file_download'] = $file['id_file_download'];
		$data_db['judul_file'] = $file['judul_file'];
		$data_db['id_file_picker'] = $file['id_file_picker'];
		$data_db['filename'] = $file['nama_file'];
		$data_db['tgl_download'] = date('Y-m-d H:i:s');

		
		$insert = $db->insert('file_download_log', $data_db);
	
		header('Content-Description: File Transfer');
		header("Content-Type: application/octet-stream");
		header("Content-Transfer-Encoding: Binary"); 
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header("Content-Disposition: attachment; filename=\"".$file['nama_file']."");
		header("Content-Length: " . filesize($file_path));
		ob_end_clean();
		ob_end_flush();
		readfile($file_path);
		exit;
}

function validate_form() {
    $error = false;
    if (empty(trim($_POST['judul_file']))) {
        $error[] = 'Judul file harus diisi';
    }

    if (empty(trim($_POST['deskripsi_file']))) {
        $error[] = 'Deskripsi file harus diisi';
    }

    return $error;
}

function save_data() 
{
    global $db;
    $message = [];
    $id_file_download= '';
    if (!empty($_POST['submit'])) {
        $error = validate_form();
        if ($error) {
            $message['status'] = 'error';
            $message['message'] = $error;
        } else {
            $data_db['judul_file'] = $_POST['judul_file'];
            $data_db['deskripsi_file'] = $_POST['deskripsi_file'];
            $data_db['id_file_picker'] = $_POST['id_file_picker'];
            if (!empty($_POST['id'])) {
				$data_db['id_user_update'] = $_SESSION['user']['id_user'];
                $query = $db->update('file_download', $data_db, ['id_file_download' => $_POST['id']]);
                $id_file_download = $_POST['id'];
            } else {
				$data_db['id_user_input'] = $_SESSION['user']['id_user'];
                $query = $db->insert('file_download', $data_db);
                $id_file_download = $db->lastInsertId();
            }
            
            if ($query) {
                $message['status'] = 'ok';
                $message['message'] = 'Data berhasil disimpan';
            } else {
                $message['status'] = 'error';
                $message['message'] = 'Data gagal disimpan';
            }
        }
    }
    return ['message' => $message, 'id' => $id_file_download, 'id_file_picker' => $_POST['id_file_picker']];
}