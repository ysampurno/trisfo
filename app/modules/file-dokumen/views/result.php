<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?></h5>
	</div>
	
	<div class="card-body">
		<?php 
		helper ('html');
		if (!$result) {
			show_message('Data tidak ditemukan, file dokumen dihalaman ini terkait akta yang diinput', 'error', false);
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
				<th>Judul File</th>
				<th>Deskripsi File</th>
				<th>Nama File</th>
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
						<td>' . $val['judul_file'] . '</td>
						<td>' . $val['deskripsi_file'] . '</td>
						<td>' . '<a href="' . BASE_URL . $config['dokumen_path'] . $val['nama_file'] . '" target="_blank">' . $val['nama_file'] . '</a>' . '</td>
						<td>'. btn_action([
									'edit' => ['class' => 'mb-2', 'url' => '?action=edit&id='. $val['id_akta_file']]
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