document.addEventListener('DOMContentLoaded', function(e){
	jQuery(function($){

		// estimate customer
		$('#edit-estimate-customer').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,client = $button.data('client')
				,contractor = $button.data('contractor')
				,contractor_title = $button.data('contractor-title')
				;

			$('#edit-estimate-customer-label').text(contractor_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_estimate_customer_form',
					client:client,
					contractor:contractor
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

			$('#edit-estimate-customer-label').text('');
			$body.text('');
		});

		$(document).on('submit', '#frm-edit-estimate-customer', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-estimate-customer-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_estimate_customer',
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
							url: theme.ajax_url+'?action=get_estimate_customer_info',
							type: 'GET',
							cache: false,
							dataType: 'json',
							data: {client:formData.get('estimate_client'), contractor:formData.get('estimate_contractor')},
							success: function(response) {
								$('.estimate-'+formData.get('estimate_contractor')+' .zalo-link').html(response['zalo']);
								$('.estimate-'+formData.get('estimate_contractor')+' .contractor-info').html(response['info']);
								$('#edit-estimate-customer .btn-close').trigger('click');
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
		
		$(document).on('click', '#estimate_customer_remove_attachment', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#estimate_attachment_id').val('');
			$this.closest('.input-group').remove();
		});
		$(document).on('input', '#estimate_attachment', function() {
			let $input = $(this);
			$input.closest('[for="estimate_attachment"]').find('.form-control').text($input.val().split('\\').pop());
		});

		$('.estimate-customer-hide').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				contractor = $this.data('contractor'),
				contractor_title = $this.data('contractorTitle'),
				$estimate = $this.closest('.estimate-item');

			if(confirm('Ẩn nhà thầu "'+contractor_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'estimate_customer_hide', client: client, contractor: contractor},
					beforeSend: function() {

					},
					success: function(response) {
						if(response) {
							$estimate.addClass('hide');
						}
					}
				});
			}
		});

	});
});