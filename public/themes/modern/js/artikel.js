var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})
$(document).ready(function(){
	
	$('.select2').select2();
	$('.flatpicker').flatpickr({
		enableTime: true,
		dateFormat: "Y-m-d H:i",
		time_24hr: true
	});
	// $(".selector").flatpickr(optional_config);
	
	$('.feature-image').click(function(){
		var $this = $(this);
		jwdfilepicker.init({
			title : 'Feature Image',
			filter_file : 'image',
			onSelect: function ($elm) {
				var meta = JSON.parse($elm.find('.meta-file').html());
				$this.find('.text').hide();
				$this.find('img').remove();
				$this.find('input').val(meta.id_file_picker);
				$this.append($elm.find('img'));
				$this.find('a.btn-remove').show();
			}
		});
	});
	
	var window_width = $(window).width();
	var window_height = $(window).height();

	if (window_width < 768) {
		var filepicker_width = window_width;
		var filepicker_height = window_height;
	} else {
		var filepicker_width = window_width - 25;
		var filepicker_height = window_height - 25;
	}
	
	$('a.btn-remove').click(function(e) {
		e.stopPropagation();
		$this = $(this);
		$parent = $this.parents('.feature-image').eq(0);
		$parent.find('img').remove();
		$parent.find('.text').show();
		$parent.find('input').val('');
		$this.hide();
	});
	
	tinymce.init({
		selector: '.tinymce',
		plugins: 'imagepick advlist lists link wordcount codesample',
		toolbar: 'styleselect | bold italic underline strikethrough | forecolor | numlist bullist | image codesample',
		branding: false,
		image_title: true,
		image_description: true,
		statusbar: false,
		image_caption: true,
		
		file_picker_types: 'image',
		file_picker_callback: function (callback, value, meta) 
		{
			tinymce.activeEditor.windowManager.openUrl({
				title: 'File Picker',
				url: filepicker_server_url + 'tinymce',
				height: filepicker_height,
				width: filepicker_width,
				resizable: true,
				maximizable: true,
				inline: 1,
				onMessage: function (instance, data) {
					if (data.mceAction == 'setFileUrl')
					{
						callback(data.meta.url, {alt: data.meta.alt_text, title: data.meta.title});
					}
				}
			});
		},
		
		codesample_content_css: base_url + "public/vendors/prism/themes/prism-dark.css",
	});
});