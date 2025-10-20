document.addEventListener('DOMContentLoaded', function(e){
	jQuery(function($){

		$('#edit-contract').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,client = $button.data('client')
				,contract = $button.data('contract')
				,contract_title = $button.data('contract-title')
				;

			$('#edit-contract-label').text(contract_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_contract_form',
					client:client,
					contract:contract
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

			$('#edit-contract-label').text('');
			$body.text('');
		});

		$(document).on('submit', '#frm-edit-contract', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-contract-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_contract',
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
							url: theme.ajax_url+'?action=get_contract_info',
							type: 'GET',
							cache: false,
							dataType: 'json',
							data: {client:formData.get('contract_client'), contract:formData.get('contract_id')},
							success: function(response) {
								$('.contract-'+formData.get('contract_id')+' .required-content').html(response['required_content']);
								$('.contract-'+formData.get('contract_id')+' .zalo-link').html(response['zalo']);
								$('.contract-'+formData.get('contract_id')+' .contract-info').html(response['info']);
								$('.contract-'+formData.get('contract_id')+' .contract-required').html(response['required']);
								$('.contract-'+formData.get('contract_id')+' .contract-created').html(response['created']);
								$('.contract-'+formData.get('contract_id')+' .contract-completed').html(response['completed']);
								$('.contract-'+formData.get('contract_id')+' .contract-sent').html(response['sent']);
								$('#edit-contract .btn-close').trigger('click');
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

		$(document).on('click', '#contract_remove_attachment', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#contract_attachment_id').val('');
			$this.closest('.input-group').remove();
		});

		$(document).on('input', '#contract_attachment', function() {
			let $input = $(this);
			$input.closest('[for="contract_attachment"]').find('.form-control').text($input.val().split('\\').pop());
		});

		$('.contract-hide').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				contract = $this.data('contract'),
				contract_title = $this.data('contractTitle'),
				$contract = $this.closest('.contract-item');

			if(confirm('Ẩn/Hiện "'+contract_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'contract_hide', client: client, contract: contract},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$contract.addClass('active');
						} else if(response===-1) {
							$contract.removeClass('active');
						}
					}
				});
			}
		});

		$('.contract-toggle').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				contract = $this.data('contract'),
				contract_title = $this.data('contractTitle'),
				$contract = $this.closest('.contract-item');

			if(confirm((($contract.hasClass('removed'))?'Sử dụng "':'Loại bỏ "')+contract_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'contract_toggle', client: client, contract: contract},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$contract.addClass('removed');
						} else if(response===-1) {
							$contract.removeClass('removed');
						}
					}
				});
			}
		});
		
	});
});