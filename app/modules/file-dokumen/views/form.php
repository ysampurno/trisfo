<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php 
			helper ('html');
		if (!empty($msg)) {
			show_message($msg['content'], $msg['status']);
		}
	
		?>
		<form method="post" action="" id="form-setting">
			<div class="tab-content" id="myTabContent">
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Judul File</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="judul_file" value="<?=set_value('judul_file', @$judul_file)?>" placeholder="Judul File" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Deskripsi File</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="deskripsi_file" value="<?=set_value('deskripsi_file', @$deskripsi_file)?>" placeholder="Deskripsi File" required="required"/>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-5">
						<button type="submit" name="submit" id="btn-submit" value="submit" class="btn btn-primary">Submit</button>
						<input type="hidden" name="id" value="<?=@$_GET['id']?>"/>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>