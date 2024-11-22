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
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Penanggung Jawab</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="nama_penanggung_jawab" value="<?=set_value('nama_penanggung_jawab', @$nama_penanggung_jawab)?>" placeholder="Nama Penanggung Jawab" required="required"/>
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