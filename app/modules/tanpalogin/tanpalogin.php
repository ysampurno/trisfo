<?php 
/**
* PHP Admin Template
* Author	: Agus Prawoto Hadi
* Website	: https://jagowebdev.com
* Year		: 2021-2022
*/

$js[] = BASE_URL . 'public/vendors/echarts/echarts.min.js';
$js[] = BASE_URL . 'public/themes/modern/js/echarts.js';

switch ($_GET['action']) 
{
    default: 
        action_notfound();
    
    	// INDEX 
    case 'index':	
		$sql = 'SELECT * FROM artikel WHERE slug = "tanpalogin"';
		$artikel = $db->query($sql)->getRowArray();
		$artikel['konten'] = str_replace('{{BASE_URL}}', BASE_URL, $artikel['konten']);
		$data['artikel'] = $artikel;
		echo load_view('views/result.php', $data, true);
}