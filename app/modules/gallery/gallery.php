<?php
/**
* PHP Admin Template
* Author	: Agus Prawoto Hadi
* Website	: https://jagowebdev.com
* Year		: 2021-2022
*/

$js[] = $config['base_url'] . 'public/vendors/dragula/dragula.min.js';
$js[] = $config['base_url'] . 'public/themes/modern/js/gallery.js';
$js[] = BASE_URL . 'public/vendors/tinymce/tinymce.js?r='.time();
$js[] = BASE_URL . 'public/vendors/jwdfilepicker/jwdfilepicker.js';
$js[] = $config['base_url'] . 'public/themes/modern/js/jwdfilepicker-defaults.js';
$js[] = BASE_URL . 'public/vendors/dropzone/dropzone.min.js';

$styles[] = $config['base_url'] . 'public/vendors/dragula/dragula.min.css';
$styles[] = $config['base_url'] . 'public/vendors/dropzone/dropzone.min.css';
$styles[] = $config['base_url'] . 'public/themes/modern/css/gallery.css';
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
				
		// Kategori
		$list = ['gallery_active' => 'Y', 'gallery_inactive' => 'N'];
		$gallery = [];
		foreach ($list as $type => $val) {
			$sql = 'SELECT *, 
						(SELECT COUNT(id_gallery_kategori) FROM gallery 
							WHERE id_gallery_kategori = gk.id_gallery_kategori
						) 
						AS jml_gambar 
				FROM gallery_kategori AS gk 
				WHERE aktif = ? ORDER BY urut';
				
			$data[$type] = $db->query($sql, $val)->getResultArray();
		}
		
		$sql = 'SELECT * FROM gallery_kategori 
					LEFT JOIN gallery USING (id_gallery_kategori) 
					LEFT JOIN file_picker ON (gallery.id_file_picker = file_picker.id_file_picker)
					
					ORDER BY id_gallery_kategori, gallery.urut';
		$query = $db->query($sql)->getResultArray();
		foreach ($query as $val) {
			$gallery[$val['id_gallery_kategori']][] = $val;
		}

        $data['title'] = 'Gallery';
        $data['message'] = $message;
        $data['gallery'] = $gallery;

        load_view('views/result.php', $data);
	
	case 'ajax-update-kategori-sort' :

		$error = false;
		
		$db->beginTrans();
		
		// Active / inactive
		// print_r($_POST);
		$update = $db->update('gallery_kategori', $_POST['param'], ['id_gallery_kategori' => $_POST['id']]);
		
		if (!$update) {
			$error = true;
		}
		
		// Sort
		$list_id = json_decode($_POST['urut'], true);
		// echo '<pre>'; print_r($list_id);
		foreach ( $list_id as $index => $id) {
			$update = $db->update('gallery_kategori', ['urut' => ($index + 1)], ['id_gallery_kategori' => $id]);
			if (!$update) {
				$error = true;
			}
		}
		
		if ($error) {
			$db->rollbackTrans();
			$result['status'] = 'error';
			$result['message'] = 'Data status gallery gagal disimpan';
		} else {
			$db->commitTrans();
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		}
		
		echo json_encode($result);
		exit();
	
	case 'ajax-update-kategori' :

		$error = false;
		
		$db->startTrans();
		$update = $db->update('gallery_kategori', $_POST['param'], ['id_gallery_kategori' => $_POST['id']]);
		
		if (!$update) {
			$error = true;
		}
		
		$list_id = json_decode($_POST['urut'], true);
		foreach ( $list_id as $index => $id) {
			$update = $db->update('gallery_kategori', ['urut' => ($index + 1)], ['id_gallery_kategori' => $id]);
			if (!$update) {
				$error = true;
			}
		}
		
		if ($error) {
			$db->rollbackTrans();
			$result['status'] = 'error';
			$result['message'] = 'Data status gallery gagal disimpan';
		} else {
			$db->commitTrans();
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		}
		
		echo json_encode($result);
		exit();
		
	case 'ajax-add-image' :
		
		$error = false;
		
		$db->beginTrans();
		/* $urut = 1;
		$sql = 'SELECT MAX(urut) AS max_urut FROM gallery WHERE id_gallery_kategori = ' . $_POST['id_gallery_kategori'];
		$query = $db->query($sql)->getRowArray();
		if ($query) {
			$urut = $query['max_urut'] + 1;
		} */
		
		$insert= false;
		if ($_POST['id_file_picker']) {
			
			$sql = 'UPDATE gallery SET urut = urut + 1 WHERE id_gallery_kategori = ?';
			$update_urut = $db->query($sql, $_POST['id_gallery_kategori']);
			
			$data_db['id_gallery_kategori'] = $_POST['id_gallery_kategori'];
			$data_db['id_file_picker'] = $_POST['id_file_picker'];
			$data_db['id_user_input'] = $_SESSION['user']['id_user'];
			$data_db['tgl_input'] = date('Y-m-d H:i:s');
			$data_db['urut'] = 1;
			
			$insert = $db->insert('gallery', $data_db);
			$result['id_gallery'] = $db->lastInsertId();
		}
		
		if (!$insert) {
			$error = true;
		}
		
		if ($error) {
			$db->rollbackTrans();
			$result['status'] = 'error';
			$result['message'] = 'Data gallery gagal disimpan';
		} else {
			$db->commitTrans();
			$result['status'] = 'ok';
			$result['message'] = 'Data gallery berhasil disimpan';
		}
		
		echo json_encode($result);
		exit();
	
	case 'ajax-kategori-delete':
		
		$db->beginTrans();
		$delete_gallery = $db->delete('gallery', ['id_gallery_kategori' => $_POST['id']]);
		$delete_kategori = $db->delete('gallery_kategori', ['id_gallery_kategori' => $_POST['id']]);

		if ($delete_gallery && $delete_kategori) {
			$db->commitTrans();
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		} else {
			$db->rollbackTrans();
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		}
		
		echo json_encode($result);
		exit();
	
	case 'ajax-gallery-change-image-order':
		
		$error = false;
		
		$db->beginTrans();
		
		$list_id = json_decode($_POST['urut'], true);
		foreach ( $list_id as $index => $id) {
			$update = $db->update('gallery', ['urut' => ($index + 1)], ['id_gallery' => $id]);
			if (!$update) {
				$error = true;
			}
		}
		
		if ($error) {
			$db->rollbackTrans();
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		} else {
			$db->commitTrans();
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		}
		
		echo json_encode($result);
		exit();
		
	case 'ajax-gallery-delete-image':
		
		$delete = $db->delete('gallery', ['id_gallery' => $_POST['id'] ]);

		if ($delete) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil dihapus';
		} else {
			$result['status'] = 'error';
			$result['message'] = 'Data status gallery gagal dihapus';
		}
		
		echo json_encode($result);
		exit();
				
	case 'ajax-gallery-change-image-category':
		
		$update = $db->update('gallery', ['id_gallery_kategori' => $_POST['id_gallery_kategori'] ], ['id_gallery' => $_POST['id']]);

		if ($update) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		} else {
			$result['status'] = 'error';
			$result['message'] = 'Data status gallery gagal disimpan';
		}
		
		echo json_encode($result);
		exit();
	
	case 'add-kategori':
		
		$message = [];
		$kategori = [];
		$id = '';
		
		if (!empty($_POST['submit'])) 
		{
			$save = save_data_kategori();
			$message = $save['message'];
			if ($save['message']['status'] == 'ok') {
				$id = $save['id_gallery_kategori'];
			}
		}
		
		if ($id) {
			$sql = 'SELECT * FROM gallery_kategori WHERE id_gallery_kategori = ?';
			$kategori = $db->query($sql, $id)->getRowArray();
		}
		
        $data['title'] = 'Add Kategori';
        $data['kategori'] = $kategori;
        $data['id_gallery_kategori'] = $id;
        $data['message'] = $message;
		
        load_view('views/form-kategori.php', $data);
		
	case 'edit-kategori':
		
		$message = [];
		if (!empty($_POST['submit'])) {
			$save = save_data_kategori();
			$message = $save['message'];
		}

        $sql = 'SELECT * FROM gallery_kategori WHERE id_gallery_kategori = ?';
		$kategori = $db->query($sql, $_GET['id'])->getRowArray();
		
		if (!$kategori)
            data_notfound();
		
        $data['title'] = 'Edit Kategori';
        $data['gallery_kategori'] = $kategori;
        $data['id_gallery_kategori'] = $_GET['id'];
        $data['message'] = $message;
		
        load_view('views/form-kategori.php', $data);
		break;
	
	case 'edit-gallery':
		
		$message = [];
		if (!empty($_POST['submit'])) {
			$save = save_data();
			$message = $save['message'];
		}
		
		// Kategori
		$sql = 'SELECT * FROM gallery_kategori';
		$result = $db->query($sql)->getResultArray();
		$kategori = [];
		$kategori[''] = 'Semua kategori';
		foreach ($result as $val) {
			$kategori[$val['id_gallery_kategori']] = $val['judul_kategori'];
		}
		
		$id_kategori = '';
		if (!empty($_GET['id_kategori'])) {
			$id_kategori = $_GET['id_kategori'];
		}
		
        // Gallery
		$sql = 'SELECT * FROM gallery 
				LEFT JOIN file_picker USING(id_file_picker)';
		
		if ($id_kategori) {
			$sql .= 'WHERE id_gallery_kategori = ' . $id_kategori;
		}
		
		$sql .= '  ORDER BY urut'; 
		$gallery = $db->query($sql)->getResultArray();
		
		foreach ($gallery as &$val) 
		{
			$meta_file = json_decode($val['meta_file'], true);
			// echo '<pre>'; print_r($gallery); die;
			if (key_exists('thumbnail', $meta_file)) {
				$thumbnail_file = $meta_file['thumbnail']['small']['filename'];
			} else {
				$thumbnail_file = $val['nama_file'];
			}
			
			$thumbnail_url = $config['filepicker_upload_url'] . $thumbnail_file;
			$val['thumbnail']['url'] = $thumbnail_url;
		}
	
		if (!$gallery) {
			$message['status'] = 'error';
			$message['message'] = 'Gallery tidak ditemukan';
		}
		
        $data['title'] = 'Edit Gallery';
        $data['gallery_kategori'] = $kategori;
        $data['id_kategori'] = $id_kategori;
        $data['gallery'] = $gallery;
        $data['message'] = $message;
		
        load_view('views/form-gallery.php', $data);
}

function validate_form() 
{
    $error = false;
    if (empty(trim($_POST['judul_kategori']))) {
        $error[] = 'Judul kategori harus diisi';
    }

    if (empty(trim($_POST['deskripsi']))) {
        $error[] = 'Deskripsi artikel harus diisi';
    }
	
	if (empty(trim($_POST['layout']))) {
        $error[] = 'Opsi Layout harus dipilih';
    }
	
	if (empty($_POST['aktif'])) {
        $error[] = 'Opsi Aktif harus dipilih';
    }

    return $error;
}

function save_data_kategori() 
{
    global $db;
    $message = [];
    $id_gallery_kategori = '';

    if (!empty($_POST['submit'])) {
        $error = validate_form();
        if ($error) {
            $message['status'] = 'error';
            $message['message'] = $error;
        } else {
			
            $data_db['judul_kategori'] = trim($_POST['judul_kategori']);
            $data_db['deskripsi'] = trim($_POST['deskripsi']);
            $data_db['aktif'] = trim($_POST['aktif']);
            $data_db['layout'] = trim($_POST['layout']);
			
			$sql = 'SELECT MAX(urut) AS max_urut FROM gallery_kategori';
			$result = $db->query($sql)->getRowArray();
            $data_db['urut'] = $result['max_urut'] + 1;
           
            if (!empty($_POST['id'])) {
				$data_db['id_user_update'] = $_SESSION['user']['id_user'];
				$data_db['tgl_update'] = date('Y-m-d H:i:s');
                $query = $db->update('gallery_kategori', $data_db, ['id_gallery_kategori' => $_POST['id']]);
                $id_gallery_kategori = $_POST['id'];
            } else {
				$data_db['tgl_create'] = date('Y-m-d H:i:s');
				$data_db['id_user_create'] = $_SESSION['user']['id_user'];
                $query = $db->insert('gallery_kategori', $data_db);
                $id_gallery_kategori = $db->lastInsertId();
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
    return ['message' => $message, 'id_gallery_kategori' => $id_gallery_kategori];
}