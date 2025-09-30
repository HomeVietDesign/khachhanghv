document.addEventListener('DOMContentLoaded', function(e){
	jQuery(function($){

		$('#edit-nha88').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,nha88 = $button.data('nha88')
				,nha88_title = $button.data('nha88-title')
				;
			$('#edit-nha88-label').text(nha88_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_nha88_form',
					nha88:nha88
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
			
			$('#edit-nha88-label').text('');
			$body.text('');
		});

		$(document).on('submit', '#frm-edit-nha88', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-nha88-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_nha88',
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
							url: theme.ajax_url+'?action=get_nha88_info',
							type: 'GET',
							cache: false,
							dataType: 'json',
							data: {nha88:formData.get('nha88')},
							success: function(response) {
								$('.nha88-'+formData.get('nha88')+' .required-content').html(response['required_content']);
								$('.nha88-'+formData.get('nha88')+' .nha88-zalo').html(response['nha88_zalo']);
								$('.nha88-'+formData.get('nha88')+' .nha88-url').html(response['nha88_url']);
								$('.nha88-'+formData.get('nha88')+' .nha88-info').html(response['nha88_info']);
								$('.nha88-'+formData.get('nha88')+' .dates').html(response['nha88_dates']);

								$('#edit-nha88 .btn-close').trigger('click');
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

		/*

		$('.nha88-hide').on('click', function(e){
			let $this = $(this),
				nha88_type = $this.data('nha88_type'),
				nha88 = $this.data('nha88'),
				nha88_title = $this.data('nha88Title'),
				$nha88 = $this.closest('.nha88-item');

			if(confirm('Ẩn/Hiện "'+nha88_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'nha88_hide', nha88_type: nha88_type, nha88: nha88},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$nha88.addClass('active');
						} else if(response===-1) {
							$nha88.removeClass('active');
						}
					}
				});
			}
		});

		*/
	});
});