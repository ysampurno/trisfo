<?php
/**
* PHP Admin Template
* Author	: Agus Prawoto Hadi
* Website	: https://jagowebdev.com
* Year		: 2021-2022
*/

$js[] = BASE_URL . 'public/vendors/jquery.select2/js/select2.full.min.js';
// $js[] = BASE_URL . 'public/vendors/jquery.select2/js/select2.bootstrap.js';
// $js[] = BASE_URL . 'public/vendors/tinymce/tinymce.min.js';
$js[] = BASE_URL . 'public/vendors/tinymce/tinymce.js?r='.time();
$js[] = BASE_URL . 'public/vendors/flatpickr/dist/flatpickr.js';
$js[] = BASE_URL . 'public/themes/modern/js/artikel.js';

$styles[] = BASE_URL . 'public/themes/modern/css/artikel.css';	
$styles[] = BASE_URL . 'public/vendors/flatpickr/dist/flatpickr.min.css';
$styles[] = BASE_URL . 'public/vendors/jquery.select2/css/select2.min.css';
$styles[] = BASE_URL . 'public/vendors/dropzone/dropzone.min.css';


$js[] = BASE_URL . 'public/vendors/jwdfilepicker/jwdfilepicker.js';
$js[] = BASE_URL . 'public/themes/modern/js/jwdfilepicker-defaults.js';
$js[] = BASE_URL . 'public/vendors/dropzone/dropzone.min.js';
$styles[] = BASE_URL . 'public/vendors/jwdfilepicker/jwdfilepicker.css';
$styles[] = BASE_URL . 'public/vendors/jwdfilepicker/jwdfilepicker-loader.css';
$styles[] = BASE_URL . 'public/vendors/jwdfilepicker/jwdfilepicker-modal.css';

switch ($_GET['action']) 
{
    default: 
        action_notfound();
    
    	// INDEX 
    case 'index':
		
		$message = [];
		
		if (!empty($_POST['delete'])) {
			$delete = $db->delete('artikel', ['id_artikel' => $_POST['id'] ]);
			if ($delete) {
				$message['status'] = 'ok';
				$message['message'] = 'Data berhasil dihapus';
			} else {
				$message['status'] = 'error';
				$message['message'] = 'Data gagal dihapus';
			}
		}
		
        $sql = 'SELECT * FROM artikel' . where_own();
		$artikel = $db->query($sql)->getResultArray();
		
		if (!$artikel)
            data_notfound();
		
		foreach ($artikel as $val) {
			$in[] = $val['id_artikel'];
			$in_mask[] = '?';
		}
		
		// Artikel Author
		$sql = 'SELECT * FROM artikel_author 
				LEFT JOIN author USING(id_author)
				WHERE id_artikel IN (' . join(',', $in_mask) . ')';
		$query = $db->query($sql, $in)->getResultArray();
		$artikel_author = [];
		foreach($query as $val) {
			$artikel_author[$val['id_artikel']][] = $val['nama_author'];
		}
		
		// Kategori
		$sql = 'SELECT * FROM artikel_kategori 
				LEFT JOIN kategori USING(id_kategori)
				WHERE id_artikel IN (' . join(',', $in_mask) . ')';
		$query = $db->query($sql, $in)->getResultArray();
		$artikel_kategori = [];
		foreach($query as $val) {
			$artikel_kategori[$val['id_artikel']][] = $val['judul_kategori'];
		}
		
        $data['title'] = 'Edit Artikel';
        $data['artikel'] = $artikel;
        $data['artikel_kategori'] = $artikel_kategori;
        $data['artikel_author'] = $artikel_author;
        $data['message'] = $message;

        load_view('views/result.php', $data);
	
	case 'add':
		
		$data['title'] = 'Add Artikel';
		
		$message = [];
		$id_artikel = '';
		$artikel = [];
		if (!empty($_POST['submit'])) {
			$save = save_data();
			$message = $save['message'];
			if ($message['status'] == 'ok') {
				$id_artikel = $save['id_artikel'];
				$data['title'] = 'Edit Artikel';
			}
		}
		
		$set_data = set_data($id_artikel);
		
		if ($set_data['artikel']) {		
			foreach ($artikel as $key => $val) {
				$data[$key] = $val;
			}
		}
		
		$data = array_merge($data, $set_data);
        $data['message'] = $message;
		
        load_view('views/form.php', $data);
		
	
	case 'edit':
		
		$data['title'] = 'Edit Artikel';
		if (empty($_GET['id'])) {
			data_notfound();
		}
		 
		$message = [];
		if (!empty($_POST['submit'])) {
			$save = save_data();
			$message = $save['message'];
		}
		
		$set_data = set_data($_GET['id']);
		if (!$set_data['artikel']) {
			data_notfound();
        }
		
		foreach ($set_data['artikel'] as $key => $val) {
			$data[$key] = $val;
		}
				
		$data = array_merge($data, $set_data);
        $data['message'] = $message;
		
        load_view('views/form.php', $data);
}

function set_data($id_artikel) 
{
	global $db;
	
	$artikel = [];
	$feature_image = [];
	if ($id_artikel) 
	{
		$sql = 'SELECT * FROM artikel ' . where_own() . ' AND id_artikel = ?';
		$query = $db->query($sql, $id_artikel)->getRowArray();
		if ($query) {
			foreach ($query as $key => $val) {
				$artikel[$key] = $val;
			}
			
			if ($artikel['id_file_picker']) {
				$sql = 'SELECT * FROM file_picker WHERE id_file_picker = ?';
				$feature_image = $db->query($sql, $artikel['id_file_picker'])->getRowArray();
			}
		}
	}
	
	// Artikel Author
	$artikel_author = [];
	$id_author = [];
	
	if ($id_artikel) 
	{
		$sql = 'SELECT * FROM artikel_author 
				LEFT JOIN author USING(id_author)
				WHERE id_artikel = ?';
		$query = $db->query($sql, $id_artikel)->getResultArray();
		$artikel_author = [];
		$id_author = [];
		foreach($query as $val) {
			$artikel_author[] = $val['nama_author'];
			$id_author[] = $val['id_author'];
		}
	}

	// Kategori
	$artikel_kategori = [];
	$id_kategori = [];
	
	if ($id_artikel) {
		$sql = 'SELECT * FROM artikel_kategori 
				LEFT JOIN kategori USING(id_kategori)
				WHERE id_artikel = ?';
		$query = $db->query($sql, $id_artikel)->getResultArray();
		$artikel_kategori = [];
		$id_kategori = [];
		foreach($query as $val) {
			$artikel_kategori[] = $val['judul_kategori'];
			$id_kategori[] = $val['id_kategori'];
		}
	}
	
	// Ref Author
	$sql = 'SELECT * FROM author';
	$query = $db->query($sql)->getResultArray();
	$ref_author = [];
	foreach($query as $val) {
		$ref_author[$val['id_author']] = $val['nama_author'];
	}
	
	// Ref Kategori
	$sql = 'SELECT * FROM kategori';
	$query = $db->query($sql)->getResultArray();
	$ref_kategori = [];
	foreach($query as $val) {
		$ref_kategori[$val['id_kategori']] = $val['judul_kategori'];
	}
	
	return [
			'artikel' => $artikel
			, 'ref_author' => $ref_author
			, 'ref_kategori' => $ref_kategori
			, 'id_kategori' => $id_kategori
			, 'artikel_kategori' => $artikel_kategori
			, 'id_author' => $id_author
			, 'artikel_author' => $artikel_author
			, 'id_artikel' => $id_artikel
			, 'feature_image' => $feature_image
	];
}

function validate_form() 
{
    $error = false;
    if (empty(trim($_POST['judul_artikel']))) {
        $error[] = 'Judul artikel harus diisi';
    }

    if (empty(trim($_POST['konten']))) {
        $error[] = 'Konten artikel harus diisi';
    }
	
	if (empty(trim($_POST['slug']))) {
        $error[] = 'Slug artikel harus diisi';
    }
	
	if (empty($_POST['id_author'])) {
        $error[] = 'Author harus diisi';
    }
	
	if (empty($_POST['tgl_terbit'])) {
        $error[] = 'Tgl. terbit harus diisi';
    }
	
	if (empty($_POST['status'])) {
        $error[] = 'Status harus diisi';
    }

    return $error;
}

function save_data() 
{
    global $db;
    $message = [];
    $id_artikel = '';
	$db->beginTrans();
	
    if (!empty($_POST['submit'])) {
        $error = validate_form();
        if ($error) {
            $message['status'] = 'error';
            $message['message'] = $error;
        } else {
			
			$db->delete('artikel_kategori', ['id_artikel' => $_POST['id']]);
			$db->delete('artikel_author', ['id_artikel' => $_POST['id']]);
			
            $data_db['judul_artikel'] = trim($_POST['judul_artikel']);
            $data_db['slug'] = trim($_POST['slug']);
            $data_db['konten'] = trim($_POST['konten']);
            $data_db['excerp'] = trim($_POST['excerp']);
            $data_db['status'] = trim($_POST['status']);
            $data_db['id_file_picker'] = trim($_POST['feature_image']);
            $data_db['search_engine_index'] = trim($_POST['search_engine_index']);
            $data_db['meta_description'] = trim($_POST['meta_description']);
			$data_db['tgl_terbit'] = trim($_POST['tgl_terbit'] . ':59');
           			
            if (!empty($_POST['id'])) {
				$data_db['id_user_update'] = $_SESSION['user']['id_user'];
				$data_db['tgl_update'] = date('Y-m-d H:i:s');
                $query = $db->update('artikel', $data_db, ['id_artikel' => $_POST['id']]);
                $id_artikel = $_POST['id'];
            } else {
				$data_db['tgl_create'] = date('Y-m-d H:i:s');
				$data_db['id_user_create'] = $_SESSION['user']['id_user'];
                $query = $db->insert('artikel', $data_db);
                $id_artikel = $db->lastInsertId();
            }
			
			if (!empty($_POST['id_kategori'])) {
				foreach ($_POST['id_kategori'] as $val) {
					$data_db = ['id_artikel' => $id_artikel, 'id_kategori' => $val];
					$db->insert('artikel_kategori', $data_db);
				}
			}
			
			if (!empty($_POST['id_author'])) {
				foreach ($_POST['id_author'] as $val) {
					$data_db = ['id_artikel' => $id_artikel, 'id_author' => $val];
					$db->insert('artikel_author', $data_db);
				}
			}
			
			
            if ($db->completeTrans()) {
                $message['status'] = 'ok';
                $message['message'] = 'Data berhasil disimpan';
            } else {
                $message['status'] = 'error';
                $message['message'] = 'Data gagal disimpan';
            }
        }
    }
    return ['message' => $message, 'id_artikel' => $id_artikel];
}