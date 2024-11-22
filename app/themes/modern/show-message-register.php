<?php 
$type = $message['status'] == 'error' ? 'danger' : 'success';
$title = $message['status'] == 'error' ? 'Error...' : 'Success...';
?>
<div class="card-body">
	<div class="alert alert-last alert-<?=$type?>">
		<h4><?=$title?></h4>
		<?=$message['message']?>
	</div>
</div>