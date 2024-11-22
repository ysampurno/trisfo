<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?></h5>
	</div>
	
	<div class="card-body">
		<a href="<?=current_url()?>?action=add" class="btn btn-success btn-xs"><i class="fa fa-plus pe-1"></i> Tambah Data</a>
		<hr/>
		<?php 
		if (!$result) {
			show_message('Data tidak ditemukan', 'error', false);
		} else {
			if (!empty($msg)) {
				show_alert($msg);
			}
			?>
			<div class="table-responsive">
			<table class="table table-striped table-bordered table-hover" id="data-tables">
			<thead>
			<tr>
				<th>No</th>
				<th>Foto</th>
				<th>Nama</th>
				<th>TTL</th>
				<th>ALAMAT</th>
				<th>NPM</th>
				<th>PRODI</th>
				<th>FAKULTAS</th>
				<th>Aksi</th>
			</tr>
			</thead>
			<tbody>
			<?php
			helper('html');
			
			$i = 1;
			foreach ($result as $key => $val) {
				
				$foto = 'Anonymous.png';
				if ($val['foto']) {
					if (file_exists('public/images/foto/' . $val['foto'])) {
						$foto = $val['foto'];
					} else {
						$foto = 'noimage.png';
					}
				}
			
				echo '<tr>
						<td>' . $i . '</td>
						<td><div class="list-foto"><img src="'. BASE_URL.'public/images/foto/' . $foto . '"/></div></td>
						<td>' . $val['nama'] . '</td>
						<td>' . $val['tempat_lahir'] . ', ' . format_tanggal($val['tgl_lahir']) . '</td>
						<td>' . $val['alamat'] . '</td>
						<td>' . $val['npm'] . '</td>
						<td>' . $val['prodi'] . '</td>
						<td>' . $val['fakultas'] . '</td>
						<td>'. btn_action([
									'edit' => ['url' => current_url() . '?action=edit&id='. $val['id_mahasiswa']]
								, 'delete' => ['url' => ''
												, 'id' =>  $val['id_mahasiswa']
												, 'delete-title' => 'Hapus data mahasiswa: <strong>'.$val['nama'].'</strong> ?'
											]
							]) .
						'</td>
					</tr>';
					$i++;
			}
			
			$settings['order'] = [2,'asc'];
			$settings['columnDefs'][] = ['targets' => [0, 1, 8], 'orderable' => false];
			?>
			</tbody>
			</table>
			</div>
			<span id="dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
			<?php 
		} ?>
	</div>
</div>