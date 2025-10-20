document.addEventListener('DOMContentLoaded', function(e){
	jQuery(function($){

		// estimate furniture
		$('#edit-estimate-furniture').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,client = $button.data('client')
				,contractor = $button.data('contractor')
				,contractor_title = $button.data('contractor-title')
				;

			$('#edit-estimate-furniture-label').text(contractor_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_estimate_furniture_form',
					client:client,
					contractor:contractor
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

			$('#edit-estimate-furniture-label').text('');
			$body.text('');
		});

		$(document).on('submit', '#frm-edit-estimate-furniture', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-estimate-furniture-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_estimate_furniture',
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
							url: theme.ajax_url+'?action=get_estimate_furniture_info',
							type: 'GET',
							cache: false,
							dataType: 'json',
							data: {client:formData.get('estimate_furniture_client'), contractor:formData.get('estimate_furniture_contractor')},
							success: function(response) {
								$('.estimate-'+formData.get('estimate_furniture_contractor')+' .required-content').html(response['required_content']);
								$('.estimate-'+formData.get('estimate_furniture_contractor')+' .zalo-link').html(response['zalo']);
								$('.estimate-'+formData.get('estimate_furniture_contractor')+' .attachment-download').html(response['attachment']);
								$('.estimate-'+formData.get('estimate_furniture_contractor')+' .contractor-info').html(response['info']);
								$('.estimate-'+formData.get('estimate_furniture_contractor')+' .estimate-required').html(response['required']);
								$('.estimate-'+formData.get('estimate_furniture_contractor')+' .estimate-received').html(response['received']);
								$('.estimate-'+formData.get('estimate_furniture_contractor')+' .estimate-completed').html(response['completed']);
								$('.estimate-'+formData.get('estimate_furniture_contractor')+' .estimate-sent').html(response['sent']);
								$('#edit-estimate-furniture .btn-close').trigger('click');
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

		$(document).on('input', '#estimate_furniture_attachment', function() {
			let $input = $(this);
			$input.closest('[for="estimate_furniture_attachment"]').find('.form-control').text($input.val().split('\\').pop());
		});
		$(document).on('click', '#estimate_furniture_remove_attachment', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#estimate_furniture_attachment_id').val('');
			$this.closest('.input-group').remove();
		});

		$('.estimate-contractor-furniture-hide').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				contractor = $this.data('contractor'),
				contractor_title = $this.data('contractorTitle'),
				$estimate = $this.closest('.estimate-item');

			if(confirm('Ẩn/Hiện "'+contractor_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'estimate_contractor_furniture_hide', client: client, contractor: contractor},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$estimate.addClass('active');
						} else if(response===-1) {
							$estimate.removeClass('active');
						}
					}
				});
			}
		});

		$('.estimate-contractor-furniture-toggle').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				contractor = $this.data('contractor'),
				contractor_title = $this.data('contractorTitle'),
				$estimate = $this.closest('.estimate-item');

			if(confirm((($estimate.hasClass('removed'))?'Sử dụng "':'Loại bỏ "')+contractor_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'estimate_contractor_furniture_toggle', client: client, contractor: contractor},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$estimate.addClass('removed');
						} else if(response===-1) {
							$estimate.removeClass('removed');
						}
					}
				});
			}
		});
	});
});