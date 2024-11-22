<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?></h5>
	</div>
	
	<div class="card-body">
		<a href="?action=add" class="btn btn-success btn-xs"><i class="fa fa-plus pe-1"></i> Tambah Data</a>
		<hr/>
		<?php 
		helper ('html');
		if (!$result) {
			show_message('Data tidak ditemukan', 'error', false);
		} else {
			if (!empty($msg)) {
				show_alert($msg);
			}
			?>
			<div class="table-responsive">
			<table class="table table-striped table-bordered table-hover">
			<thead>
			<tr>
				<th>No</th>
				<th>Nama Penanggung Jawab</th>
				<th>Aksi</th>
			</tr>
			</thead>
			<tbody>
			<?php
			helper ('html');
			
			$i = 1;
			foreach ($result as $key => $val) {
				echo '<tr>
						<td>' . $i . '</td>
						<td>' . $val['nama_penanggung_jawab'] . '</td>
						<td>'. btn_action([
									'edit' => ['class' => 'mb-2', 'url' => '?action=edit&id='. $val['id_penanggung_jawab']]
									, 'delete' => ['class' => 'mb-2', 'url' => ''
												, 'id' =>  $val['id_penanggung_jawab']
												, 'delete-title' => 'Hapus data ?'
											]
							]) .
						'</td>
					</tr>';
					$i++;
			}
			?>
			</tbody>
			</table>
			</div>
			<?php 
		} ?>
	</div>
</div>