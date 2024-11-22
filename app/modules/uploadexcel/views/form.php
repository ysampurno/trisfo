<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php
		helper(['html', 'format']);
		if (!empty($msg)) {
			show_message($msg['content'], $msg['status']);
		}
		?>
		<form method="post" action="" class="form-horizontal" enctype="multipart/form-data">
			<div class="tab-content" id="myTabContent">
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Pilih Tabel</label>
					<div class="col-sm-5">
						<?=options(['name' => 'nama_tabel', 'id' => 'nama-tabel'], $tabel_options)?>
						<div class="mt-1">Contoh file: <a title="<?=$tabel['mahasiswa']['file_excel']['title']?>" href="<?=$tabel['mahasiswa']['file_excel']['url']?>"><?=$tabel['mahasiswa']['file_excel']['display']?></a></div>
						<small>Baris pertama file excel harus disertakan, dan tidak boleh dirubah, karena akan diidentifikasi sebagai nama kolom tabel database</small>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Pilih File Excel</label>
					<div class="col-sm-5">
						<input type="file" class="file form-control" name="file_excel">
							<?php if (!empty($form_errors['file_excel'])) echo '<small class="alert alert-danger">' . $form_errors['file_excel'] . '</small>'?>
							<small class="small" style="display:block">File ekstensi harus .xlsx</small>
						<div class="upload-img-thumb"><span class="img-prop"></span></div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-5">
						<button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
						<input type="hidden" name="id" value="<?=@$_GET['id']?>"/>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>