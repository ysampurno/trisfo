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
	<table class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>No</th>
				<th>Judul Artikel</th>
				<th>Kategori</th>
				<th>Author</th>
				<th>Tanggal</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody>
		<?php
		helper('html');
		$no = 1;
		
		if (!$artikel_kategori)
			$artikel_kategori = [];
		
		if (!$artikel_author)
			$artikel_author = [];

		foreach ($artikel as $val) 
		{
			echo '<tr>
					<td>' . $no . '</td>
					<td>' . $val['judul_artikel'] . '</td>
					<td>' . join(', ', $artikel_kategori[$val['id_artikel']]). '</td>
					<td>' . join(', ', $artikel_author[$val['id_artikel']]). '</td>
					<td>' . $val['tgl_terbit'] . '</td>
					<td>' . btn_action([
							'edit' => ['url' => BASE_URL . 'artikel/edit?id='. $val['id_artikel']]
							, 'delete' => ['url' => ''
											, 'id' =>  $val['id_artikel']
											,'delete-title' => 'Hapus data artikel: <strong>'.$val['judul_artikel'].'</strong> ?'
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