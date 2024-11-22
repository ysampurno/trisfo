<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?></h5>
	</div>
	
	<div class="card-body">
		<div class="alert alert-danger">Alamat email data dibawah ini menggunakan yopmail.com, kamu dapat mengecek email yang dikirim dengan langsung membuka yopmail.com dan memasukkan alamat email yang ingin dicek</div> 
		<?php 
		if (!$result) {
			show_message('Data tidak ditemukan', 'error', false);
		} else {
			if (!empty($msg)) {
				show_alert($msg);
			}
			
			
			$column =[
						'id_mahasiswa' => 'ID'
						, 'foto' => 'Foto'
						, 'nama' => 'Nama'
						, 'tgl_lahir' => 'TTL'
						, 'alamat' => 'Alamat'
						, 'email' => 'Email'
						, 'ignore_search_action' => 'Action'
					];
			$setting = ['order' => [3,'desc']];
			$th = '';
			foreach ($column as $val) {
				$th .= '<th>' . $val . '</th>'; 
			}
			?>
			
			<table id="table-result" class="table display table-striped table-bordered table-hover" style="width:100%">
			<thead>
				<tr>
					<?=$th?>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<?=$th?>
				</tr>
			</tfoot>
			</table>
			<?php
				foreach ($column as $key => $val) {
					$column_dt[] = ['data' => $key];
				}
			?>
			<span id="dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
			<span id="dataTables-setting" style="display:none"><?=json_encode($setting)?></span>
			<span id="dataTables-url" style="display:none"><?=current_url() . '?action=getDataDT'?></span>
			<?php 
		} ?>
	</div>
</div>