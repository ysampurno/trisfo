<?php
/**
* PHP Admin Template
* Author	: Agus Prawoto Hadi
* Website	: https://jagowebdev.com
* Year		: 2021-2022
*/
/* Create breadcrumb
$data: title as key, and url as value */ 

function breadcrumb($data) 
{
	$separator = '&raquo;';
	echo '<nav aria-label="breadcrumb">
  <ol class="breadcrumb">';
	foreach ($data as $title => $url) {
		if ($url) {
			echo '<li class="breadcrumb-item"><a href="'.$url.'">'.$title.'</a></li>';
		} else {
			echo '<li class="breadcrumb-item active" aria-current="page">'.$title.'</li>';
		}
	}
	echo '
  </ol>
</nav>';
}

function nama_bulan() 
{
	return [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']; 
}

// $date harus yyyy-mm-dd
function format_tanggal($date, $format = 'dd mmmm yyyy') 
{
	// echo $format;
	
	if ($date == '0000-00-00' || $date == '')
		return $date;
	
	$format = strtolower($format);
	$new_format = $date;
	
	list($year, $month, $date) = explode('-', $date);
	if (strpos($format, 'dd') !== false) {
		$new_format = str_replace('dd', $date, $format);
	}
	
	if (strpos($format, 'mmmm') !== false) {
		$bulan = nama_bulan();
		$new_format = str_replace('mmmm', $bulan[ ($month * 1) ], $new_format);
	} else if (strpos($format, 'mm') !== false) {
		$new_format = str_replace('mm', $month, $new_format);
	}
	
	if (strpos($format, 'yyyy') !== false) {
		$new_format = str_replace('yyyy', $year, $new_format);
	}
	return $new_format;
}

function format_tanggal_db($date) 
{
	if ($date == '0000-00-00' || $date == '')
		return $date;
	
	$bulan = nama_bulan();
	$exp = explode('-', $date);
	return $exp[2] . ' ' . $bulan[ ($exp[1] * 1) ] . ' ' . $exp[0]; // * untuk mengubah 02 menjadi 2
}

function prepare_datadb($data) {
	foreach ($data as $field) {
		$result[$field] = $_POST[$field];
	}
	return $result;
}

function get_menu() 
{
	global $db;
	global $current_module;
	// print_r($current_module);
	$nama_module = $current_module['nama_module'];
	// echo '<pre>'; print_r($_SESSION); die;
	$sql = 'SELECT * FROM menu 
					LEFT JOIN menu_role USING (id_menu) 
					LEFT JOIN module USING (id_module)
					LEFT JOIN menu_kategori USING(id_menu_kategori)
				WHERE menu_kategori.aktif = "Y" AND ( id_role IN ( ' . join(',', array_keys($_SESSION['user']['role'])) . ') )
				ORDER BY menu_kategori.urut, menu.urut';
						
	$db->query($sql);
	
	$current_id = '';
	$menu = [];
	while ($row = $db->fetch()) 
	{
		$menu[$row['id_menu']] = $row;
		$menu[$row['id_menu']]['highlight'] = 0;
		$menu[$row['id_menu']]['depth'] = 0;

		if ($nama_module == $row['nama_module']) {
			
			$current_id = $row['id_menu'];
			$menu[$row['id_menu']]['highlight'] = 1;
		}	
	}
	
	if ($current_id) {
		menu_current($menu, $current_id);
	}
	
	$menu_kategori = [];
	foreach ($menu as $id_menu => $val) {
		if (!$id_menu)
			continue;
		
		$menu_kategori[$val['id_menu_kategori']][$val['id_menu']] = $val;
	}
	
	// Kategori
	$sql = 'SELECT * FROM menu_kategori WHERE aktif = "Y" ORDER BY urut';
	$query_result = $db->query($sql)->getResultArray();
	$result = [];
	foreach ($query_result as $val) {
		if (key_exists($val['id_menu_kategori'], $menu_kategori)) {
			$result[$val['id_menu_kategori']] = [ 'kategori' => $val, 'menu' => $menu_kategori[$val['id_menu_kategori']] ];
		}
	}		
	
	return $result;
}

function menu_current( &$result, $current_id) 
{
	$parent = $result[$current_id]['id_parent'];

	$result[$parent]['highlight'] = 1; // Highlight menu parent
	if (@$result[$parent]['id_parent']) {
		menu_current($result, $parent);
	}
}

function menu_list($result)
{
	$refs = array();
	$list = array();

	foreach ($result as $key => $data)
	{
		if (empty($data['id_menu'])) // Highlight OR No parent
			continue;
		
		$thisref = &$refs[ $data['id_menu'] ];
		foreach ($data as $field => $value) {
			$thisref[$field] = $value;
		}

		// no parent
		if ($data['id_parent'] == 0) {
			
			$list[ $data['id_menu'] ] = &$thisref;
		} else {
			
			$thisref['depth'] = @$refs[ $data['id_menu']]['depth']++;			
			$refs[ $data['id_parent'] ]['children'][ $data['id_menu'] ] = &$thisref;
		}
	}
	
	set_depth($list);	
	return $list;
}

function set_depth(&$result, $depth = 0) {
	foreach ($result as $key => &$val) 
	{
		$val['depth'] = $depth;
		if (key_exists('children', $val)) {
			set_depth($val['children'], $val['depth'] + 1);
		}
	}
}

function build_menu( $arr, $submenu = false)
{
	global $current_module;
	$menu = "\n" . '<ul'.$submenu.'>'."\r\n";

	foreach ($arr as $key => $val) 
	{
		if (!$key)
			continue;
	
		// Check new
		$new = '';
		if (key_exists('new', $val)) {
			$new = $val['new'] == 1 ? '<span class="menu-baru">NEW</span>' : '';
		}
		$arrow = key_exists('children', $val) ? '<span class="pull-right-container">
								<i class="fa fa-angle-left arrow"></i>
							</span>' : '';
		$has_child = key_exists('children', $val) ? 'has-children' : '';
		
		if ($has_child) {
			$url = '#';
			$onClick = ' onclick="javascript:void(0)"';
		} else {
			$onClick = '';
			$url = $val['url'];
		}
		
		// class attribute for <li>
		$class_li = [];		
		if ($current_module['nama_module'] == $val['nama_module']) {
			$class_li[] = 'tree-open';
		}
		
		if ($val['highlight']) {
			$class_li[] = 'highlight tree-open';
		}
		
		if ($class_li) {
			$class_li = ' class="' . join(' ', $class_li) . '"';
		} else {
			$class_li = '';
		}
		
		// Class attribute for <a>, children of <li>
		$class_a = ['depth-' . $val['depth']];
		if ($has_child) {
			$class_a[] = 'has-children';
		}
		
		$class_a = ' class="' . join(' ', $class_a) . '"';
		
		// Menu icon
		$menu_icon = '';
		if ($val['class']) {
			$menu_icon = '<i class="sidebar-menu-icon ' . $val['class'] . '"></i>';
		}

		// Menu
		if (substr($url, 0, 4) != 'http') {
			$url = BASE_URL . $url;
		}
							
		$menu .= '<li'. $class_li . '>
					<a '.$class_a.' href="'. $url.'"'.$onClick.'>'.
						'<span class="menu-item">' . 
							$menu_icon.
							$val['nama_menu'] .
						'</span>' .
						$arrow.
					'</a>'.$new;
		
		if (key_exists('children', $val))
		{ 	
			$menu .= build_menu($val['children'], ' class="submenu"');
		} 
		$menu .= "</li>\n";
	}
	$menu .= "</ul>\n";
	return $menu;
}

/* function create_image_mime ($tipe_file, $newfile)
{
	switch ($tipe_file)
	{
		case "image/gif":
			return imagecreatefromgif($newfile);
			
		case "image/png":
			return imagecreatefrompng($newfile);
			
		case "image/bmp":
			return imagecreatefrombmp($newfile);
			
		default:
			return imagecreatefromjpeg($newfile);		
	}
} */
	
/* function create_image ($tipe_file, $resized_img, $newfile)
{
	switch ($tipe_file)
	{
		case "image/gif":
			return imagegif ($resized_img,$newfile, 85);
			
		case "image/png":
			imagesavealpha($resized_img, true);
			$color = imagecolorallocatealpha($resized_img, 0,0,0,127);
			imagefill($resized_img, 0,0, $color);
			return imagepng ($resized_img,$newfile, 9);
			
		case "image/bmp":
			return imagecreatefrombmp($newfile);
			
		default:
			return imagejpeg ($resized_img,$newfile, 85);
			
	}
} */

/* function save_image ($image, $file_type, $w, $h) 
{
	$img_size = @getimagesize($image);
	
	$resized_img = imagecreatetruecolor($w,$h);
	$new_img = \create_image_mime($file_type, $image);
	imagecopyresized($resized_img, $new_img, 0, 0, 0, 0, $w, $h, $img_size[0], $img_size[1]);
	$do = create_image($file_type, $resized_img, $image);
	ImageDestroy ($resized_img);
	ImageDestroy ($new_img);
	return $do;
}*/

function get_filename($file_name, $path) {
	
	$file_name_path = $path . $file_name;
	if ($file_name != "" && file_exists($file_name_path))
	{
		$file_ext = strrchr($file_name, '.');
		$file_basename = substr($file_name, 0, strripos($file_name, '.'));
		$num = 1;
		while (file_exists($file_name_path)){
			$file_name = $file_basename."($num)".$file_ext;
			$num++;
			$file_name_path = $path . $file_name;
		}
		
		return $file_name;
	}
	return $file_name;
}

function upload_image($path, $file, $max_w = 500, $max_h = 500) 
{
	
	$file_type = $file['type'];
	$new_name =  get_filename(stripslashes($file['name']), $path); ;
	$move = move_uploaded_file($file['tmp_name'], $path . $new_name);
	
	$save_image = false;
	if ($move) {
		$dim = image_dimension($path . $new_name, $max_w, $max_h);
		$save_image = save_image($path . $new_name, $file_type, $dim[0], $dim[1]);
	}
	
	if ($save_image)
		return $new_name;
	else
		return false;
}

function image_dimension($images, $maxw=null, $maxh=null)
{
	if($images)
	{
		$img_size = @getimagesize($images);
		$w = $img_size[0];
		$h = $img_size[1];
		$dim = array('w','h');
		foreach($dim AS $val){
			$max = "max{$val}";
			if(${$val} > ${$max} && ${$max}){
				$alt = ($val == 'w') ? 'h' : 'w';
				$ratio = ${$alt} / ${$val};
				${$val} = ${$max};
				${$alt} = ${$val} * $ratio;
			}
		}
		return array($w,$h);
	}
}

function save_image($image, $file_type, $w, $h) 
{
	require_once('app/libraries/vendors/imageworkshop/autoload.php');	
	$layer = PHPImageWorkshop\ImageWorkshop::initFromPath($image);
	$layer->resizeInPixel($w, $h, true);
	$name_path = pathinfo($image);
	$layer->save($name_path['dirname'], $name_path['basename'], false, false, 100);
	return true;
}

function upload_file($path, $file) 
{
	$new_name =  get_filename(stripslashes($file['name']), $path); ;
	$move = move_uploaded_file($file['tmp_name'], $path . $new_name);
	if ($move) 
		return $new_name;
	else
		return false;
}

function get_dimensi_kartu($ori_panjang, $ori_lebar, $dpi) {
	// print_r($ori_panjang); die;
	$px = 0.393700787; 
	$panjang = $ori_panjang * $dpi * $px;
	$lebar = $ori_lebar * $dpi * $px;
	return ['w' => $panjang, 'h' => $lebar];
}

function generateQRCode($version, $ecc, $text, $module_width) {
	
	require BASE_PATH . 'libraries' . DS . 'vendors' . DS . 'qrcode' . DS . 'qrcode_extended.php';
	$qr = new QRCodeExtended();
	$ecc_code = ['L' => QR_ERROR_CORRECT_LEVEL_L
		, 'M' => QR_ERROR_CORRECT_LEVEL_M
		, 'Q' => QR_ERROR_CORRECT_LEVEL_Q
		, 'H' => QR_ERROR_CORRECT_LEVEL_H
	];
	$qr->setErrorCorrectLevel($ecc_code[$ecc]);
	$qr->setTypeNumber($version);
	$qr->addData($text);
	$qr->make();
	return $qr->saveHtml($module_width);
}

// For backup only
function file_extension_background_color() {
	
	return [
		// Document
		'doc'	=> '#0070C0',
		'docx'	=> '#0070C0',
		'odt'	=> '#0070C0',
		'xlsx'	=> '#00B050',
		'xls'	=> '#00B050',
		'ods'	=> '#00B050',
		'csv'	=> '#00B050',
		'pdf'	=> '#FD4739',
		'ppt'	=> '#FD4739',
		'pptx'	=> '#FD4739',
		'odp'	=> '#FD4739',

		// Video / Audio
		'wav'	=> '#00B0F0',
		'flac'	=> '#00B0F0',
		'mp3'	=> '#00B0F0',

		'mp4' => '#fd4739',
		'avi' => '#fd4739',
		'mov' => '#fd4739',
		'mkv' => '#fd4739',
		'wmv' => '#fd4739',
		'mpeg' => '#fd4739',

		// Compression
		'rar' 	=> '#ef904f',
		'zip' 	=> '#ffcb25',
		'gzip' 	=> '#958f8f',
		'7zip' 	=> '#767171',

		// Application
		'msi' => '#8497b0',
		'exe' => '#8497b0'
	];
}