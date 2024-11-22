$(document).ready(function() {
	
	
	function generate_options(json) {
		options = '';
		$.each(json, function(i, v) {
			options += '<option value="' + i + '">' + v + '</option>';
		})
		
		return options;
	}
	
	function set_options($elm, url) 
	{
		let $next_option = '';
		let $wilayah = '';
		let url_wilayah = '';
		const value = $elm.val();

		if ($elm.hasClass('propinsi'))
		{
			$wilayah = $('.kabupaten, .kecamatan, .kelurahan').prop('disabled', true);
			$next_option = $('.kabupaten');
			url_wilayah = 'ajaxGetKabupatenByIdPropinsi';
		} 
		else if ($elm.hasClass('kabupaten')) {
			$wilayah = $('.kecamatan, .kelurahan').prop('disabled', true);
			$next_option = $('.kecamatan');
			url_wilayah = 'ajaxGetKecamatanByIdKabupaten';
		}
		else if ($elm.hasClass('kecamatan')) {
			$wilayah = $('.kelurahan').prop('disabled', true);
			$next_option = $('.kelurahan');
			url_wilayah = 'ajaxGetKelurahanByIdKecamatan';
		}
		
		if (!$next_option || ! $wilayah) {
			return false;
		}
		
		$spinner = $('<div class="spinner-border spinner-border-md" role="status" style="width: 1.5rem; height: 1.5rem; position:absolute; right: -27px; top:5px"></div>');
		
		$wrapper = $('<div>').css('position', 'relative');
		$wrapper.insertAfter($next_option);
		$wrapper.append($spinner);

		$.getJSON(base_url + 'wilayah?action=' + url_wilayah + '&id=' + value, function(data) 
		{
			console.log('ff');
			new_options = generate_options(data);
			$wilayah.each (function(i, elm) 
			{
				$elm = $(elm);
				teks = '-- Pilih Kelurahan --';
				if ($elm.hasClass('kabupaten')) {
					teks = '-- Pilih Kabupaten --';
				} else if ($elm.hasClass('kecamatan')) {
					teks = '-- Pilih Kecamatan --';
				}
				
				if (i == 0) {
					$elm.prop('disabled', false)
				}
				$elm
					.empty()
					.append(new_options)
					.prepend('<option value="">' + teks + '</option>')
					.val('');
				$wrapper.remove();
			});
		});
	}

	$('.propinsi, .kabupaten, .kecamatan').change(function() {
		set_options($(this));
	});
})