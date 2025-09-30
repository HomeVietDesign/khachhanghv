document.addEventListener('DOMContentLoaded', function(e){
	jQuery(function($){

		// edit gzalo
		$('#edit-gzalo').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,gzalo = $button.data('gzalo')
				,gzalo_title = $button.data('gzalo-title')
				;

			$('#edit-gzalo-label').text(gzalo_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_gzalo_form',
					gzalo:gzalo
				},
				beforeSend: function(xhr) {
					$body.text('Đang tải..');
				},
				success: function(response) {
					$body.html(response);
					// Khởi tạo lại TinyMCE
					wp.editor.initialize("required_content", JSON.parse($('#required_content_settings').val()));
				},
				error: function() {
					$body.text('Lỗi khi tải. Tắt mở lại.');
				},
				complete: function() {
					
				}
			});
			
		}).on('hidden.bs.modal', function (e) {
			let $modal = $(this),
				$body = $modal.find('.modal-body');

			wp.editor.remove('required_content');

			$('#edit-gzalo-label').text('');
			$body.text('');
		});

		$(document).on('submit', '#frm-edit-gzalo', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-gzalo-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_gzalo',
				type: 'POST',
				processData: false,
				contentType: false,
				data: formData,
				dataType: 'json',
				cache: false,
				beforeSend: function() {
					$response.html('<p class="text-primary">Đang xử lý...</p>');
				},
				success: function(response) {
					if(response['code']>0) {
						$.ajax({
							url: theme.ajax_url+'?action=get_gzalo_info',
							type: 'GET',
							cache: false,
							dataType: 'json',
							data: {gzalo:formData.get('gzalo')},
							success: function(response) {
								$('.gzalo-'+formData.get('gzalo')+' .required-content').html(response['required_content']);
								$('.gzalo-'+formData.get('gzalo')+' .zalo-link').html(response['zalo']);
								$('#edit-gzalo .btn-close').trigger('click');
							}
						});
					}
					$response.html(response['msg']);
				},
				error: function(xhr) {
					$response.html('<p class="text-danger">Có lỗi xảy ra. Xin vui lòng thử lại.</p>');
				},
				complete: function() {
					$button.prop('disabled', false);
				}
			});
		});
		
	});
});