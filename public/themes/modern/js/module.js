$(document).ready(function() {
	
	let dataTables = '';
	$('#table-result').delegate('.switch', 'click', function()
	{
		var id_module = $(this).data('module-id');
		var id_result = $(this).is(':checked') ? 1 : 3;
		$.ajax({
			type: "POST",
			url: base_url + 'module/ajax-switch-module-status',
			data: 'id_module=' + id_module + '&id_result=' + id_result + '&switch_type=aktif&change_module_attr=1&ajax=true',
			dataType: 'text',
			success: function(data) {
				if (data == 'ok') {
					var text = id_result == 1 ? 'Aktif' : 'Non Aktif';
					$('[data-status-text="'+id_module+'"]').html(text);
					
				}
			},
			error: function(xhr) {
				console.log(xhr);
			}
		});
	})
	
	if ($('#table-result').length) {
		column = $.parseJSON($('#dataTables-column').html());
		url = $('#dataTables-url').text();
		
		 var settings = {
			"processing": true,
			"serverSide": true,
			"scrollX": true,
			"ajax": {
				"url": url,
				"type": "POST",
				/* "dataSrc": function (json) {
					console.log(json)
				} */
			},
			"columns": column
		}
		
		$add_setting = $('#dataTables-setting');
		if ($add_setting.length > 0) {
			add_setting = $.parseJSON($('#dataTables-setting').html());
			for (k in add_setting) {
				settings[k] = add_setting[k];
			}
		}
		
		dataTables =  $('#table-result').DataTable( settings );
	}
	
	$('#table-result').delegate('.btn-delete', 'click', function(e) {
		e.preventDefault();
		id = $(this).attr('data-id');
		$bootbox = bootbox.confirm({
			message: $(this).attr('data-delete-title'),
			callback: function(confirmed) {
				if (confirmed) {
					$.ajax({
						type: 'POST',
						url: current_url + '/ajax-delete',
						data: 'id=' + id,
						dataType: 'json',
						success: function (data) {
							$bootbox.modal('hide');
							if (data.status == 'ok') {
								const Toast = Swal.mixin({
									toast: true,
									position: 'top-end',
									showConfirmButton: false,
									timer: 2500,
									timerProgressBar: true,
									iconColor: 'white',
									customClass: {
										popup: 'bg-success text-light toast p-2'
									},
									didOpen: (toast) => {
										toast.addEventListener('mouseenter', Swal.stopTimer)
										toast.addEventListener('mouseleave', Swal.resumeTimer)
									}
								})
								Toast.fire({
									html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data berhasil dihapus</div>'
								})
								dataTables.draw();
							} else {
								show_alert('Error !!!', data.message, 'error');
							}
						},
						error: function (xhr) {
							show_alert('Error !!!', xhr.responseText, 'error');
							console.log(xhr.responseText);
						}
					})
				}
			},
			centerVertical: true
		});
	})
});