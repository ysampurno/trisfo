<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?></h5>
	</div>
	<div class="card-body">
	<?php
		if (!empty($message)) {
				show_alert($message);
	} ?>
	<a href="<?=module_url()?>/add" class="btn btn-success btn-xs"><i class="fas fa-plus pe-1"></i> Tambah Data</a>
	<hr/>
	<div class="table-responsive">
	<table class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>No</th>
				<th>Judul File</th>
				<th>Deskripsi File</th>
				<th>Download File</th>
				<th>Aksi</th>
			</tr>
		</thead>
		<tbody>
		
		<?php
		helper('html');
		$no = 1;
		foreach ($result as $val) {
			echo '<tr>
					<td>' . $no . '</td>
					<td>' . $val['judul_file'] . '</td>
					<td>' . $val['deskripsi_file'] . '</td>
					<td>' . btn_label([
										'attr' => ['class' => 'btn btn-outline-secondary btn-inline']
										, 'url' => $config['base_url'] . 'filedownload/download?id=' . $val['id_file_download']
										, 'icon' => 'fas fa-file-download'
										, 'label' => 'Download'
									]) . '
					<td>' . btn_action([
							'edit' => ['url' => BASE_URL . 'filedownload/edit?id='. $val['id_file_download']]
							, 'delete' => ['url' => ''
											, 'id' =>  $val['id_file_download']
											,'delete-title' => 'Hapus file download : <strong>'.$val['judul_file'].'</strong> ?'
										]
							]) .
					'</td>
			</tr>';
			$no++;
		}
		?>
		</tbody>
	</table>
	</div>
	</div>
</div>