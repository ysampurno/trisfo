  <!-- Begin Page Content -->
 <?= $this->extend('layout/template'); ?>
 <?= $this->section('content'); ?>

 <div class="container-fluid">
	<?php
	helper('util');
	
	$produk_tab = $images_tab = '';
	$produk_tab_content = $images_tab_content = '';
	if (empty($_POST['tab'])) {
		$produk_tab = 'active';
		$produk_tab_content = 'show active';
	} else {
		switch($_POST['tab']) {
			case 'produk':
				$produk_tab = 'active';
				$produk_tab_content = 'show active';
				break;
			case 'images':
				$images_tab = 'active';
				$images_tab_content = 'show active';
				break;
		}
	}
	?>
	 <ul class="nav nav-pills mb-3" id="myTab" role="tablist">
		<li class="nav-item">
			<a class="nav-link <?=$produk_tab?>" id="detail-produk-tab" data-toggle="tab" href="#detail-produk" role="tab" aria-controls="detail-produk" aria-selected="true">Detail Produk</a>
		</li>
		<li class="nav-item">
			<a class="nav-link <?=$images_tab?>" id="gallery-produk-tab" data-toggle="tab" href="#gallery-produk" role="tab" aria-controls="gallery-produk" aria-selected="false">Gallery</a>
		</li>
	</ul>
	<hr/>
	<div class="tab-content produk-edit" id="pills-tabContent">
		<div class="tab-pane fade <?=$produk_tab_content?>" id="detail-produk" role="tabpanel" aria-labelledby="nama-produk-tab">
			<?php
			if (@$_POST['tab'] == 'produk' && !empty($msg)) {
				show_message($msg['message'], $msg['status']);
			}
			?>
			 <form action="<?= base_url('listing/tambah'); ?>" method="POST" enctype="multipart/form-data">
				 <input type="hidden" name="no" id="no-listing">
				 <div class="mb-3">
					 <label for="listing">Nama</label>
					 <input type="text" name="listing" id="listing" class="form-control" placeholder="Masukan nama listing">
				 </div>

				 <div class="mb-3">
					 <label for="kategori">kategori</label>
					 <select class="col-md-12 form-select" aria-label="Default select example" name="kategori" id="kategori">
						 <?php foreach ($kategori as $ko) : ?>
							 <option value="<?= $ko['kategori']; ?>"> <?= $ko['kategori']; ?></option>
						 <?php endforeach ?>
					 </select>

				 </div>

				 <div class="mb-3">
					 <label for="lokasi">lokasi</label>
					 <select class="col-md-12 form-select" aria-label="Default select example" name="lokasi" id="lokasi">
						 <?php foreach ($lokasi as $ko) : ?>
							 <option value="<?= $ko['lokasi']; ?>"> <?= $ko['lokasi']; ?></option>
						 <?php endforeach ?>
					 </select>
				 </div>

				 <div class="mb-3">
					 <label for="agent">agent</label>
					 <select class="col-md-12 form-select" aria-label="Default select example" name="agent" id="agent">
						 <?php foreach ($agent as $ko) : ?>
							 <option value=" <?= $ko['nama']; ?>"> <?= $ko['nama']; ?></option>
						 <?php endforeach ?>
					 </select>
				 </div>

				 <div class="mb-3">
					 <label for="luasbangunan">luas Bangunan</label>
					 <input type="text" name="luasbangunan" id="luasbangunan" class="form-control" placeholder="Masukan luas bangunan">
				 </div>

				 <div class="mb-3">
					 <label for="kamartidur">kamar tidur</label>
					 <input type="text" name="kamartidur" id="kamartidur" class="form-control" placeholder="Masukan banyak kamar tidur">
				 </div>

				 <div class="mb-3">
					 <label for="kamarmandi">kamar Mandi</label>
					 <input type="text" name="kamarmandi" id="kamarmandi" class="form-control" placeholder="Masukan banyak kamar mandi">
				 </div>

				 <div class="mb-3">
					 <label for="dayalistrik">Daya Listrik</label>
					 <input type="text" name="dayalistrik" id="dayalistrik" class="form-control" placeholder="Masukan Daya listrik">
				 </div>

				 <div class="mb-3">
					 <label for="harga">Harga</label>
					 <input type="text" name="harga" id="harga" class="form-control" placeholder="Masukan Harga">
				 </div>

				 <div class="mb-3">
					 <label for="nego">Nego</label>
					 <select class="col-md-12 form-select" name="nego" id="nego">
						 <option value="Ya">Ya</option>
						 <option value="Tidak">Tidak</option>
					 </select>
				 </div>

				 <div class="mb-3">
					 <label for="lantai">Lantai</label>
					 <input type="text" name="lantai" id="lantai" class="form-control" placeholder="Masukan Tinggi lantai">
				 </div>

				 <div class="mb-3">
					 <label for="kamarpembantu">kamar Pembantu</label>
					 <input type="text" name="kamarpembantu" id="kamarpembantu" class="form-control" placeholder="Masukan banyak kamar pembantu">
				 </div>
				 <div class="mb-3">
					 <label for="perabot">Perabot</label>
					 <select class="col-md-12 form-select" aria-label="Default select example" name="perabot" id="perabot">
						 <option value="Tanpa Perabot">Tanpa Perabot</option>
						 <option value="Berserta Perabot">Berserta Perabot</option>
						 <option value="Sebagian Perabot">Sebagian Perabot</option>
					 </select>
				 </div>

				 <div class="mb-3">
					 <label for="seterfikat">Seterfikasi</label>
					 <select class="col-md-12 form-select" aria-label="Default select example" name="seterfikat" id="seterfikat">
						 <option value="Hak Milik">Hak Milik</option>
						 <option value="Hak Guna Bangunan">Hak Guna Bangunan</option>
						 <option value="Hak Pakai">Hak Pakai</option>
						 <option value="Hak Guna Usaha">Hak Guna Usaha</option>
						 <option value="Hak Pengelolaan">Hak Pengelolaan</option>
						 <option value="Hak Akta Jual Beli">Akta Jual Beli</option>
						 <option value="PPJB">PPJB</option>
					 </select>
				 </div>



				 <div class="mb-3">
					 <label for="deskripsi">Deskripsi</label>
					 <textarea class="form-control" name="deskripsi" cols="50" rows="10" id="deskripsi"></textarea>
				 </div>

				 <div class="mb-3">
					 <label for="linkvideo">Link Youtube</label>
					 <input type="text" name="linkvideo" id="linkvideo" class="form-control" placeholder="Link Video Youtube">
				 </div>

				 <div class="mb-3">
					 <label for="sell/rent">Sell/Rent</label>
					 <select class="col-md-12 form-select" aria-label="Default select example" name="sell/rent" id="sell/rent">
						 <option value="Sell">Sell</option>
						 <option value="Rent">Rent</option>
					 </select>
				 </div>

				 <div class="mb-3">
					 <label for="featured">Featured</label>
					 <select class="col-md-12 form-select" aria-label="Default select example" name="featured" id="featured">
						 <option value="Tidak">Tidak</option>
						 <option value="Ya">Ya</option>
					 </select>
				 </div>

				 <div class="mb-3">
					 <label for="aktif">aktif</label>
					 <select class="col-md-12 form-select" aria-label="Default select example" name="aktif" id="aktif">
						 <option value="Ya">Ya</option>
						 <option value="Tidak">Tidak</option>
					 </select>
				 </div>
				 
				  <div class="mb-3">
						<button type="subsmit" name="submit" value="submit" class="btn btn-danger">Submit</button>
						<input type="hidden" name="tab" value="produk"/>
				 </div>
			</form>
		</div>

	<!-- GALLERY -->
		<div class="tab-pane fade <?=$images_tab_content?>" id="gallery-produk" role="tabpanel" aria-labelledby="gallery-produk-tab">
			<?php
			if (@$_POST['tab'] == 'images' && !empty($msg)) {
				show_message($msg['message'], $msg['status']);
			}
			
			if (empty($id_produk)) {
				if (empty($msg)) {
					
					show_message('Produk belum didefinisikan', 'error');
				}
			} else {
				// echo '<pre>xxx'; print_r($nama_file); die;
				
				?>
				<form method="post" action="" class="form-container" enctype="multipart/form-data">
					<?php
					
					if (!@is_array($_POST['nama_file'])) {
						$_POST['nama_file'] = [];
						$_POST['deskripsi_image'] = [];
					}
					
					if (empty($_POST['nama_file'])) {
						$_POST['nama_file'][0] = '';
						$_POST['deskripsi_image'][0] = '';
					}

					foreach ($_POST['nama_file'] as $key => $val) {
						$class_container = $key == 0 ? 'add-row-container' : '';
						$btn_class = $key == 0 ? 'add-row' : 'delete-row';
						$btn_color = $key == 0 ? 'btn-success' : 'btn-danger';
						$btn_text = $key == 0 ? 'Tambah File' : 'Delete File';
						?>
						<div class="row mb-3">
							<div class="col-sm-4 mb-0">
								<div style="display:none">
									<div class="upload-file-thumb mb-3"><span class="file-prop"></span></div>
									<input type="file" class="file mb-2" name="nama_file[]">
									<input class="form-control mb-2" type="text" name="judul_image[]" value="<?=set_value('judul_image['.$key.']', '')?>" placeholder="Nama Image"/>
									<textarea class="form-control mb-2 mt-2" name="deskripsi_image[]" placeholder="Deskripsi Image"/><?=set_value('deskripsi_image['.$key.']', '')?></textarea>										
								</div>
								<a class="btn <?=$btn_color?> btn-xs mt-2 <?=$btn_class?>" href="javascript:void(0)"><?=$btn_text?></a>
							</div>
						</div>
						
					<?php
					}
					?>
					<div class="mb-3 row submit" style="display:none">
						<div class="col-sm-12">
							<button type="submit" name="submit" value="images" class="btn btn-primary">Simpan Gambar</button>
							<input type="hidden" name="id" value="<?=@$id_produk?>"/>
							<input type="hidden" name="tab" value="images"/>
						</div>
					</div>
				</form>
				<?php
				
				if (!empty($nama_file)) {
					$class = ['main' => 'success', 'feature' => 'success', 'screenshot' => 'secondary', 'none' => 'danger'];
					// Cek
					$feature = $screenshot = $none = false;
					foreach ($nama_file as $key_data => $val_data) 
					{
						if ($jenis[$key_data] == 'feature') {
							$feature = true;
						}
						
						if ($jenis[$key_data] == 'screenshot') {
							$screenshot = true;
						}
						
						if ($jenis[$key_data] == 'none') {
							$none = true;
						}
					}
					
					// Feature
					if ($feature) {
						echo '<div class="list-image-edit col-sm-12 col-md-12 col-lg-12 col-xl-12">
								<ol class="list-group">';
								
						foreach ($nama_file as $key_data => $val_data) {
							if ($jenis[$key_data] == 'feature') {
								echo '
								<li class="row shadow-sm col-sm-12 col-md-12 col-lg-12 col-xl-6">
									<span class="id-img" style="display:none">' . $id_image[$key_data] . '</span>
									<div class="img-container col-sm-4 col-md-4 col-lg-4">
										<img class="card-img-top" src="'.$config->appURL .'/public/files/produk/' . $id_produk . '/' . $val_data .'"/>
									</div>
									<div class="col-sm-8 col-md-8 col-lg-8">
										<h5 class="card-title">'. $judul_image[$key_data] .'</h5>
										<p class="card-text">' .  $deskripsi_image[$key_data] . '</p>
										<span class="badge badge-' . $class[$jenis[$key_data]] . '">' . $jenis[$key_data] . '</span>
									</div>
									<ul class="btn-toolbar">
										<li>
											<a href="javascript:void(0)" data-permalink-image="'. $config->appURL .'public/files/produk/' . $id_produk . '/' . $nama_file[$key_data] . '" class="bg-primary btn-link text-white small">
												<i class="fas fa-link"></i>
											</a>
										</li>
										<li>
											<a href="javascript:void(0)" data-id-file="' . $id_image[$key_data] . '" class="bg-success btn-edit text-white small">
												<i class="fas fa-pencil-alt"></i>
											</a>
										</li>
										<li>												
											<a href="javascript:void(0)" data-jenis="screenshot" data-id-file="'.$id_image[$key_data].'" data-delete-title="Hapus image: <strong>' . $judul_image[$key_data] . '</strong> ?" class="bg-danger btn-close text-white">
												<i class="fa fa-times"></i>
											</a>
										</li>
									</ul>
								</li>';
							}
						}
						
						echo '</ol>
								</div>';
					}
				}
			}
			?>
		</div>
	</div>
</div>
 <?= $this->endSection('content'); ?>