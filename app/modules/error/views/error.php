<?php
	require_once('app/includes/functions.php');
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=(@$data['title'] ?: 'Error')?></h5>
	</div>
	<div class="card-body">
		<?=show_message($data);?>
	</div>
</div>