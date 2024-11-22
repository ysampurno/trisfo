<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php 
			helper ('html');
			echo btn_label(['attr' => ['class' => 'btn btn-success btn-xs'],
				'url' => module_url() . '?action=add',
				'icon' => 'fa fa-plus',
				'label' => 'Tambah Data'
			]);
			
			echo btn_label(['attr' => ['class' => 'btn btn-light btn-xs'],
				'url' => module_url(),
				'icon' => 'fa fa-arrow-circle-left',
				'label' => $current_module['judul_module']
			]);
		?>
		<hr/>
		<?php
		if (@$tgl_lahir) {
			$exp = explode('-', $tgl_lahir);
			$tgl_lahir = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		}
		if (!empty($msg)) {
			show_message($msg);
		}
		?>
		<form method="post" action="" class="form-horizontal" enctype="multipart/form-data">
			<div class="tab-content" id="myTabContent">
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Siswa</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="nama" value="<?=set_value('nama', @$nama)?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tempat Lahir</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="tempat_lahir" value="<?=set_value('tempat_lahir', @$tempat_lahir)?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tgl. Lahir</label>
					<div class="col-sm-5">
						<input class="form-control date-picker" type="text" name="tgl_lahir" value="<?=set_value('tgl_lahir', @$tgl_lahir)?>"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">NPM</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="npm" value="<?=set_value('npm', @$npm)?>"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Prodi</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="prodi" value="<?=set_value('prodi', @$prodi)?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Fakultas</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="fakultas" value="<?=set_value('fakultas', @$fakultas)?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Alamat</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="alamat" value="<?=set_value('alamat', @$alamat)?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Foto (Image Upload)</label>
					<div class="col-sm-5">
						<?php 
						if (!empty($foto) ) 
						{
							$note = '';
							if (file_exists(BASE_PATH . 'public/images/foto/' . $foto)) {
								$image = $config['base_url'] . 'public/images/foto/' . $foto;
							} else {
								$image = $config['base_url'] . 'public/images/foto/noimage.png';
								$note = '<small><b>Note</strong>: File <strong>public/images/foto/' . $foto . '</strong> tidak ditemukan</small>';
							}
							echo '<div class="img-choose" style="margin:inherit;margin-bottom:10px">
									<div class="img-choose-container">
										<img src="'. $image . '?r=' . time() . '"/>
										<a href="javascript:void(0)" class="remove-img"><i class="fas fa-times"></i></a>
									</div>
								</div>
								' . $note .'
								';
						}
						?>
						<input type="hidden" class="foto-delete-img" name="foto_delete_img" value="0">
						<input type="file" class="file form-control" name="foto">
						<input type="hidden" class="foto-max-size" name="foto_max_size" value="300000"/>
						<?php if (!empty($form_errors['foto'])) echo '<small class="alert alert-danger">' . $form_errors['foto'] . '</small>'?>
						<small class="small" style="display:block">Maksimal 300Kb, Minimal 100px x 100px, Tipe file: .JPG, .JPEG, .PNG</small>
						<div class="upload-img-thumb mb-2"><div class="img-prop mt-2"></div></div>
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