function format_ribuan(bilangan) {
	var number_string = bilangan.toString().replace(/\D/g, ''),
		split	= number_string.split(','),
		sisa 	= split[0].length % 3,
		rupiah 	= split[0].substr(0, sisa),
		ribuan 	= split[0].substr(sisa).match(/\d{1,3}/gi);
		
	if (ribuan) {
		separator = sisa ? '.' : '';
		rupiah += separator + ribuan.join('.');
	}
	
	rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
	return rupiah;
}


jQuery(document).ready(function () 
{
	max_data = parseInt(max_data);
	total_data = parseInt(total_data);
	var $error_container = $('#error-container');
	$('#data-awal, #data-akhir').keyup(function()
	{
		$error_container.hide();
		this.value = format_ribuan(this.value);
		data_awal = parseInt($('#data-awal').val().replace(/\D/g, ''));
		data_akhir = parseInt($('#data-akhir').val().replace(/\D/g, ''));
		selisih = data_akhir - data_awal;
	
		error = false;
		if (data_awal > max_data) {
			error = 'Data awal melebihi ' + format_ribuan(max_data)
		} else if (data_awal > total_data) {
			error = 'Data awal melebihi total data';
		} else if (data_akhir > total_data) {
			error = 'Data akhir melebihi total data'; 
		} else if (data_akhir < data_awal) {
			error = 'Data akhir lebih kecil dari data awal'; 
		} else if (selisih > max_data) {
			error = 'Data awal dikurangi data akhir melebihi ' + format_ribuan(max_data); 
		}
		
		if (error) {
			$error_container.find('small').html(error);
			$error_container.show();
		}
	});

	$('#nama-tabel').change(function(){
		$this = $(this);
		$button = $this.parents('form').eq(0).find('button').attr('disabled', 'disabled');
		$loader = $('<i>').addClass('fas fa-circle-notch fa-spin ms-2').insertAfter($('#jms-data').find('strong'));
		$.get(module_url + '/max-data', function(max_data){
			$.ajax({
			  type: "POST",
			  url: module_url + '/countdata',
			  dataType: 'text',
			  data: 'nama_tabel=' + $this.val(),
			  success: function(data){
				  total_data = parseInt(data);
				  $('#jms-data').html('<strong>' + format_ribuan(data) + '</strong>');
				  if (parseInt(data) > parseInt(max_data)) {
					  $('#download-range-container').show().find('input').removeAttr('disabled');
				  } else {
					  $('#download-range-container').hide().find('input').attr('disabled', 'disabled');
				  }
				  
				  $button.removeAttr('disabled');
			  }
			});
		});
		
	});
	
	$('form').submit(function(e){
		e.preventDefault;
		$('#data-awal').val($('#data-awal').val().replace(/\D/g, ''));		
		$('#data-akhir').val($('#data-akhir').val().replace(/\D/g, ''));
		
		$(this).submit();
	});
	
	$('#data-awal, #data-akhir').trigger('keyup');
});