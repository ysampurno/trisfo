<?php
/**
* PHP Admin Template
* Author	: Agus Prawoto Hadi
* Website	: https://jagowebdev.com
* Year		: 2021-2022
*/

/* $js[] = BASE_URL . 'public/themes/modern/js/filepicker.js';
$js[] = BASE_URL . 'public/vendors/imagebrowser/imagebrowser.js';
$js[] = BASE_URL . 'public/vendors/dropzone/dropzone.min.js';

$styles[] = BASE_URL . 'public/themes/modern/css/artikel.css';	
$styles[] = BASE_URL . 'public/vendors/dropzone/dropzone.min.css';
$styles[] = BASE_URL . 'public/vendors/imagebrowser/imagebrowser.css';
$styles[] = BASE_URL . 'public/vendors/imagebrowser/imagebrowser-loader.css';
$styles[] = BASE_URL . 'public/vendors/imagebrowser/imagebrowser-modal.css'; */

$js[] = $config['base_url'] . 'public/themes/modern/js/filepicker.js';
$styles[] = BASE_URL . 'public/themes/modern/css/filepicker.css';

$js[] = BASE_URL . 'public/vendors/jwdfilepicker/jwdfilepicker.js';
$js[] = $config['base_url'] . 'public/themes/modern/js/jwdfilepicker-defaults.js';
$js[] = BASE_URL . 'public/vendors/dropzone/dropzone.min.js';
$styles[] = BASE_URL . 'public/vendors/jwdfilepicker/jwdfilepicker.css';
$styles[] = BASE_URL . 'public/vendors/jwdfilepicker/jwdfilepicker-loader.css';
$styles[] = BASE_URL . 'public/vendors/jwdfilepicker/jwdfilepicker-modal.css';

require_once('app/libraries/vendors/imageworkshop/autoload.php');
use PHPImageWorkshop\ImageWorkshop;
$item_per_page = !empty($_GET['item_per_page']) ? $_GET['item_per_page'] : $config['item_per_page'];

$nama_bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$list_file_type = file_type();

switch ($_GET['action']) 
{
    default: 
        action_notfound();
    
     case 'index':
	 
        $message = [];
		$where = ' WHERE 1 = 1';
		
		if (!empty($_GET['id_file_picker'])) {
			$where .= ' AND id_file_picker = ' . $_GET['id_file_picker'];
			
		} else {
		
			// Filter Filee Type
			if (!empty($_GET['filter_file'])) {
								
				$split = explode(' ', $_GET['filter_file']);
				$list_filter = [];
				foreach ($split as $filter) 
				{
					
					$filter = trim($filter);
					if (!$filter)
						continue;
					
					$list_mime = [];
					foreach ($list_file_type as $mime => $val) {
						if ($val['file_type'] == $filter) {
							$list_mime[] = $mime;
						}
					}
					
					if ($list_mime) {
						$list_filter[] = 'mime_type IN ("' . join ('","', $list_mime) . '")';
					}
				}
				
				if ($list_filter) {
					$where .= ' AND (' . join(' OR ', $list_filter) . ')';
				}
			}
			
			// Date Options
			$sql = 'SELECT DATE_FORMAT(tgl_upload,"%Y-%m") AS bulan FROM file_picker GROUP BY bulan ORDER BY bulan DESC ';
			$tanggal = $db->query($sql)->getResultArray();
			foreach ($tanggal as $val) {
				$exp = explode('-', $val['bulan']);
				$result['filter_tgl'][$val['bulan']] = $nama_bulan[$exp[1] * 1] . ' ' . $exp[0];
			}
			
			// FIlter Tgl
			if ( !empty($_GET['filter_tgl']) ) {
				$where .= ' AND tgl_upload LIKE "' . $_GET['filter_tgl'] . '%"';
			}
			
			// Filter Search 
			if ( !empty($_GET['q']) && trim($_GET['q']) != '' ) {
				$where .= ' AND (title LIKE "%' . $_GET['q'] . '%" OR nama_file LIKE "%' . $_GET['q'] . '%")';
			}
		}
			
		
		if (empty($_GET['page'])) {
			$_GET['page'] = 1;
		}

		$limit = $item_per_page * ( $_GET['page'] - 1 ) . ', ' . $item_per_page;
		
        $sql = 'SELECT * FROM file_picker ' . $where . ' ORDER BY tgl_upload DESC LIMIT ' . $limit;
		$result['data'] = $db->query($sql)->getResultArray();
		
		$sql = 'SELECT COUNT(*) AS total_item FROM file_picker ' . $where;
		$query = $db->query($sql)->getRowArray();
		$total_item = $query['total_item'];
		$result['total_item'] = $total_item;
		
		$jml_data = count($result['data']);
		$loaded_item = $jml_data < $item_per_page ? $jml_data : $item_per_page;
		$result['loaded_item'] = ( $item_per_page * ($_GET['page'] - 1) ) + count($result['data']);
					
		foreach ($result['data'] as $key => $val) 
		{
			$meta_file = json_decode($val['meta_file'], true);
			$properties = get_file_properties($val['mime_type'], $val['nama_file'], $meta_file);
			$result['data'][$key] = array_merge($result['data'][$key], $properties);
		}
		
		if ( !empty($_GET['ajax']) ) {
			echo json_encode($result);
			exit();
		}
		
		$data['title'] = 'File Picker Manager';
		$data['filter_file'] = ['' => 'All Files', 'image' => 'Image', 'video' => 'Video', 'document' => 'Dokumen', 'archive' => 'Archive'];
		$data['filter_tgl'] = @$result['filter_tgl'];
		$data['total_item'] = $total_item;
		$data['loaded_item'] = $loaded_item;
		$data['item_per_page'] = $item_per_page;
        $data['result'] = $result;
        $data['message'] = $message;
		

        if (!$data['result'])
            data_notfound();

        load_view('views/result.php', $data);
		
    case 'tinymce':
		
		include 'views/form.php';
		exit;

	case 'ajax-file-icon' :
		
		$result['status'] = 'error';
		$result['icon']	= '';
		
		$file_icon = 'file';
		
		// if (!empty($_GET['mime']) && !empty($_GET['ext']) ) {

		if (key_exists($_GET['mime'], $list_file_type)) {
			$file_icon =$list_file_type[$_GET['mime']]['extension'];
		} else {
			
			foreach ($list_file_type as $val) {
				if ($val['extension'] == $_GET['ext']) {
					$file_icon = strtolower($_GET['ext']);
				}
			}
		}
		
		// }
		
		$icon_path = $config['filepicker_icon_path'] . $file_icon . '.png';			
			
		if (file_exists($icon_path)) 
		{
			$result['status'] = 'ok';
			$result['icon']	= 'data:image/png;base64,' . base64_encode(file_get_contents($icon_path));
		}
		
		echo file_get_contents($icon_path);
		echo json_encode($result);
		exit;
		
	case 'ajax-update-file' :
	
		$update = $db->update('file_picker', [$_POST['name'] => $_POST['value']], ['id_file_picker' => $_POST['id']]);
		if ($update)
			echo json_encode( ['status' => 'ok'] );
		else
			echo json_encode( ['status' => 'error'] );
		
		exit;
			
	case 'ajax-upload-file' :
	
		$path = $config['filepicker_upload_path'];

		if ( !empty($_FILES) ) {
						
			if ( file_exists($path) && is_dir($path) ) {
				
				if ( !is_writable($path) ) {
					$result = array (
						'status' => 'error',
						'message'   => 'Tidak dapat menulis file ke folder'
					);
					
				} else {

					$new_name = upload_file($path, $_FILES['file']);
					if ($new_name) {
						
						$meta_file = [];
						
						$mime_image = ['image/png', 'image/jpeg', 'image/bmp', 'image/gif'];
						$current_mime_type = mime_content_type ($path . $new_name);
						
						if (in_array($current_mime_type, $mime_image)) 
						{
							$img_size = @getimagesize($path . $new_name);
						
							$meta_file['default'] = ['width' => $img_size[0]
														, 'height' => $img_size[1]
														, 'size' => $_FILES['file']['size']
													];

							foreach ($config['thumbnail'] as $size => $dim) 
							{
							
								if ($img_size[0] > $dim['w'] || $img_size[1] > $dim['h']) 
								{
									$img_dim = image_dimension($path. $new_name, $dim['w'], $dim['h']);
									$img_width = ceil($img_dim[0]);
									$img_height = ceil($img_dim[1]);
									
									$width = $height = null;
									if ($img_width >= $dim['w']) {
										
										$width = $dim['w'];
										
									} else if ($img_height >= $dim['h']) {
										
										$height = $dim['h'];
									}

									$layer = ImageWorkshop::initFromPath($path . $new_name);
									$layer->resizeInPixel($width, $height, true);
									$name_path = pathinfo($new_name);
									$thumb_name = $name_path['filename'] . '_' . $size . '.' . $name_path['extension'];
									$layer->save($path, $thumb_name, false, false, 97);
									
									$thumb_dim =  @getimagesize($path . $thumb_name);
									$meta_file['thumbnail'][$size] = [
															'filename' => $thumb_name
															, 'width' => $thumb_dim[0]
															, 'height' => $thumb_dim[1]
															, 'size' => @filesize($path . $thumb_name)
														];
								}
							}
						}
						
						$data_db['nama_file'] = $new_name;
						$data_db['mime_type'] = $current_mime_type;
						$data_db['size'] = $_FILES['file']['size'];
						$data_db['tgl_upload'] = date('Y-m-d H:i:s');
						$data_db['id_user_upload'] = $_SESSION['user']['id_user'];
						$data_db['meta_file'] = json_encode($meta_file);
						
						$sql = $db->insert('file_picker', $data_db);
						
						$file_info = $data_db;
						$file_info['bulan_upload'][date('Y-m')] = $nama_bulan[date('n')] . ' ' . date('Y');
						$file_info['id_file_picker'] = $db->lastInsertId();
						$result = get_file_properties($current_mime_type, $new_name, $meta_file);
						$file_info = array_merge($file_info, $result);
						
						$result = [
								'status'    => 'success',
								'message'      => 'File berhasil diupload.',
								'file_info' => $file_info
						];
					} else {
						$result = [
							'status' => 'error',
							'message'   => 'System error'
						];
					}
				}

			} else {
				$result = [
					'status' => 'error',
					'message'   => 'Folder ' . $path . ' tidak ditemukan'
				];
			}
			
			

			// Return the response
			echo json_encode($result);
			exit;
		}
	
	case 'ajax-delete-all' :
		
		$result['status'] = 'error';
		$result['message'] = 'Bad request';
		if (!empty($_POST['submit'])) 
		{
			if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
				&& !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
				&& strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
			) {

				$path = '../public/files/uploads/';
				$list_file = @scandir($path);
				if ($list_file) {
					foreach ($list_file as $val) 
					{
						if ($val == '.' || $val == '..') {
							continue;
						}
						
						// unlink($path . $val);
					}
				
					$sql = 'TRUNCATE TABLE file_picker';
					$db->query($sql);
					$result['status'] = 'ok';
					$result['message'] = 'Data berhasil dihapus';
				} else {
					$result['status'] = 'error';
					$result['message'] = 'Folder ' . $path . ' kososng';
				}
			}
		}
		echo json_encode($result);
		exit;
		
	case 'ajax-delete-file' :
	
		$result['status'] = 'error';
		$result['message'] = 'Bad request';
		
		$error = [];
		
		if (empty($_POST['submit']) 
			|| empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
			|| @strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest'
		) {
			$error[] = 'Bad request';
		}

		if (empty($_POST['id'])) {
			$error[] = 'ID file tidak valid';
		}
		
		if (!$error) {
			
			$db->beginTrans();
			$id_files = json_decode($_POST['id'], true);
			
			if (!is_array($id_files)) {
				if ($id_files) {
					$id_files = [ $id_files ];
				} else {
					$id_files = [ $_POST['id'] ];
				}
			}
	
			foreach ($id_files as $id_file) 
			{
				$sql = 'SELECT * FROM file_picker WHERE id_file_picker = ?';
				$file = $db->query($sql, $id_file)->getRowArray();
				
				if (!$file) {
					$error[] = 'File tidak ditemukan';
				} else {
		
					$delete = $db->delete('file_picker', ['id_file_picker' => $id_file]);
					if ($delete) {
						$meta = json_decode($file['meta_file'], true);
						
						$config['upload_dir'] = 'public/files/uploads/';
						$dir = trim($config['upload_dir'], '/');
						$dir = trim($dir, '\\');
						$dir = $dir . '/';
						
						// Main File
						$unlink = delete_file(BASE_PATH_PARENT . $dir . $file['nama_file']);
						if (!$unlink) {
							$error[] = 'Gagal menghapus file: ' . $val['filename'];
						}
						
						// Thumbnail
						if(key_exists('thumbnail', $meta)) 
						{
							foreach ($meta['thumbnail'] as $val) {
								$unlink = delete_file(BASE_PATH_PARENT . $dir . $val['filename']);
								if (!$unlink) {
									$error[] = 'Gagal menghapus file: ' . $val['filename'];
								}
							}
						}
						
					} else {
						$error[] = 'Gagal menghapus data database file ID: ' . $id_file;
					}
				}
			}
			
			if ($error) {
				$db->rollbackTrans();
				$result['status'] = 'error';
				$result['message'] = '<ul><li>' . join('</li></li>', $error) . '</li></ul>';
			} else {
				$db->commitTrans();
				$result['status'] = 'ok';
				$result['message'] = 'Data berhasil dihapus';
			}
		}
		
		
		echo json_encode($result);
		exit();
}

function file_type() {
	
	return [
	
		'text/plain' => ['file_type' => 'document', 'extension' => 'txt'],
		
		// Image
		'image/jpg'		=> ['file_type' => 'image', 'extension' => 'jpg'],
		'image/jpeg'		=> ['file_type' => 'image', 'extension' => 'jpg'],
		'image/png'		=> ['file_type' => 'image', 'extension' => 'png'],
		'image/bmp'		=> ['file_type' => 'image', 'extension' => 'bmp'],
		'image/gif'		=> ['file_type' => 'image', 'extension' => 'gif'],

		// Media
		'audio/x-wav'		=> ['file_type' => 'audio', 'extension' => 'wav'],
		'audio/flac'		=> ['file_type' => 'audio', 'extension' => 'flac'],
		'audio/mpeg'		=> ['file_type' => 'audio', 'extension' => 'mp3'],
		
		'video/mp4'			=> ['file_type' => 'video', 'extension' => 'mp4'],
		'video/x-msvideo' 	=> ['file_type' => 'video', 'extension' => 'avi'],
		'video/quicktime' 	=> ['file_type' => 'video', 'extension' => 'mov'],
		'video/x-matroska' 	=> ['file_type' => 'video', 'extension' => 'mkv'],
		'video/x-ms-asf' 	=> ['file_type' => 'video', 'extension' => 'wmv'],

		// Document
		'application/pdf' => ['file_type' => 'document', 'extension' => 'pdf'],

		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['file_type' => 'document', 'extension' => 'xlsx'], //xlsx
		'application/vnd.ms-excel' => ['file_type' => 'document', 'extension' => 'xls'], // xls
		'application/vnd.oasis.opendocument.spreadsheet' => ['file_type' => 'document', 'extension' => 'ods'], // ods

		'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['file_type' => 'document', 'extension' => 'docx'], //docx
		'application/msword' => ['file_type' => 'document', 'extension' => 'doc'], // doc
		'application/vnd.oasis.opendocument.text' => ['file_type' => 'document', 'extension' => 'odt'],
		'text/rtf' => ['file_type' => 'document', 'extension' => 'rtf'],

		'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['file_type' => 'document', 'extension' => 'ppt'], // pptx
		'application/vnd.oasis.opendocument.presentation' => ['file_type' => 'document', 'extension' => 'odp'],
		'application/vnd.ms-powerpoint' => ['file_type' => 'document', 'extension' => 'ppt'], //ppt

		// Compression
		'application/x-rar'	=> ['file_type' => 'archive', 'extension' => 'rar'],
		'application/zip'	=> ['file_type' => 'archive', 'extension' => 'zip'],
		'application/gzip'	=> ['file_type' => 'archive', 'extension' => 'gz'],
		'application/x-7z-compressed' => ['file_type' => 'archive', 'extension' => '7z'],

		// Application
		'application/x-msi' => ['file_type' => 'application', 'extension' => 'msi'],
		'application/x-dosexec' => ['file_type' => 'application', 'extension' => 'exe']
    
	];
}

function get_file_properties($mime, $file_name, $meta_file) 
{	
	global $config, $list_file_type;
	
	$extension_color = $extension = '';
	$mime_image = ['image/png', 'image/jpeg', 'image/bmp', 'image/gif'];
	
	$file_exists = true;
	// echo $config['filepicker_upload_path'] . $file_name; die;
	if (file_exists($config['filepicker_upload_path'] . $file_name)) 
	{
		$result['file_exists']['original'] = 'found';
	} else {
		$file_exists = false;
		$result['file_exists']['original'] = 'not_found';
	}
	
	if (in_array($mime, $mime_image)) {
		
		$thumbnail_file = $file_name;
		if (key_exists('thumbnail', $meta_file)) 
		{
			$thumbnail = $meta_file['thumbnail'];
			foreach ($thumbnail as $size => $val) 
			{
				if (file_exists($config['filepicker_upload_path'] . $val['filename'])) {
					$result['file_exists']['thumbnail'][$size] = 'found';
				} else {
					$file_exists = false;
					$result['file_exists']['thumbnail'][$size] = 'not_found';
				}
			}
			
			if (key_exists('small', $thumbnail)) {
				$thumbnail_file = $thumbnail['small']['filename'];
			}
		}
		
		$thumbnail_url = $config['filepicker_upload_url'] . $thumbnail_file; 
		$file_type = 'image';

	} else {
		
		$pathinfo = pathinfo($file_name);
		$extension = $pathinfo['extension'];
		
		$file_icon = 'file';
		$file_type = 'non_image';
		
		if (key_exists($mime, $list_file_type)) {
			$file_icon = $list_file_type[$mime]['extension'];
			$file_type = $list_file_type[$mime]['file_type'];
		} else {
			
			foreach ($list_file_type as $val) {
				if ($val['extension'] == $extension) {
					$file_icon = strtolower($extension);
					$file_type = $val['file_type'];
				}
			}
		}
		
		$thumbnail_url = $config['filepicker_icon_url'] . $file_icon . '.png';
		
	}
	
	if (!$file_exists) {
		$thumbnail_url = $config['filepicker_icon_url'] . 'file_not_found.png';
	}
	
	
	if (!key_exists('thumbnail', $result['file_exists']) ) {
		 $result['file_exists']['thumbnail'] = [];
	}
	
	if ($file_exists) {
		$result['file_not_found'] = 'false';
	} else {
		$result['file_not_found'] = 'true';
	}
	
	$result['file_type'] = $file_type;
	$result['url'] = $config['filepicker_upload_url'] . $file_name; 
	$result['thumbnail']['url'] = $thumbnail_url;
	$result['thumbnail']['extension_name'] = $extension;
	
	return $result;
}