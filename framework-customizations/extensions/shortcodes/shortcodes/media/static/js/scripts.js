document.addEventListener('DOMContentLoaded', function(e){
	jQuery(function($){

		$('#edit-medias').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,media = $button.data('media')
				,medias_title = $button.data('medias-title')
				;

			$('#edit-medias-label').text(medias_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_media_form',
					media:media
				},
				beforeSend: function(xhr) {
					$body.text('Đang tải..');
				},
				success: function(response) {
					$body.html(response);
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

			$('#edit-medias-label').text('');
			$body.text('');

		});

		function get_media_info(media) {
			$.ajax({
				url: theme.ajax_url+'?action=get_media_info',
				type: 'GET',
				dataType: 'json',
				cache: false,
				data: {media:media},
				success: function(response) {
					$('.media-'+media+' .media-web').html(response['web']);
					$('.media-'+media+' .media-fb').html(response['fb']);
					$('.media-'+media+' .media-last_date').html(response['last_date']);
					$('.media-'+media+' .media-end_date').html(response['end_date']);
					$('#edit-medias .btn-close').trigger('click');
				}
			});
		}

		$(document).on('submit', '#frm-edit-media', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-media-response')
				;
			$button.prop('disabled', true);
			
			$.ajax({
				url: theme.ajax_url+'?action=update_media',
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
						get_media_info(formData.get('media'));
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