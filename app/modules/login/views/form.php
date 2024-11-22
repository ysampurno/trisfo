<div class="login-body">
	<?php
	if (!empty($message)) {?>
		<div class="alert alert-danger">
			<?=$message?>
		</div>
	<?php }
	?>
	<form method="post" action="" class="form-horizontal form-login">
	<div class="mb-3 input-group">
	  <div class="input-group-prepend login-input">
		<span class="input-group-text" id="basic-addon1">
			<i class="fa fa-user"></i>
		</span>
	  </div>
	  <input type="text" name="username" value="<?=@$_POST['username']?>" class="form-control login-input" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" required>
	</div>
	<div class="mb-3 input-group">
	  <div class="input-group-prepend login-input">
		<span class="input-group-text" id="basic-addon1">
			<i class="fa fa-lock" style="font-size:22px"></i>
		</span>
	  </div>
	  <input type="password"  name="password" class="form-control login-input" placeholder="Password" aria-label="Password" aria-describedby="basic-addon1" required>
	</div>
	<div style="margin:10px">
	    Administrator: admin:admin, user biasa: user:user. Versi demo tidak dapat mengubah data
	</div>
	<div class="mb-3 input-group">
		<div class="checkbox">
			<label style="font-weight:normal"><input name="remember" value="1" type="checkbox">&nbsp;&nbsp;Remember me</label>
		</div>
	</div>
	<div class="mb-3" style="margin-bottom:7px">
		<button type="submit" class="form-control btn <?=$setting_app['btn_login']?>" name="submit">Submit</button>
		<?php csrf_field(); ?>
	</div><?php
	helper('registrasi');
	$setting_registrasi = get_setting_registrasi();?>
	<div class="login-footer">
		<p>Lupa Password? <a href="<?=$config['base_url']?>recovery">Request reset password</a></p>
		<?php if ($setting_registrasi['enable'] == 'Y') { ?>
			<p>Belum punya akun? <a href="<?=$config['base_url']?>register">Daftar akun</a></p>
		<?php }?>
		<p>Tidak menerima link aktivasi? <a href="<?=$config['base_url']?>resendlink">Kirim ulang</a></p>
	</div>
</div>