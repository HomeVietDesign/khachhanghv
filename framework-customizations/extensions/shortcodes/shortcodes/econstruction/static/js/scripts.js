document.addEventListener('DOMContentLoaded', function(e){
	jQuery(function($){

		// econstruction
		$('#edit-econstruction').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,client = $button.data('client')
				,econstruction = $button.data('econstruction')
				,econstruction_title = $button.data('econstruction-title')
				;

			$('#edit-econstruction-label').text(econstruction_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_econstruction_form',
					client:client,
					econstruction:econstruction
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

			$('#edit-econstruction-label').text('');
			$body.text('');
		});

		$(document).on('submit', '#frm-edit-econstruction', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-econstruction-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_econstruction',
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
							url: theme.ajax_url+'?action=get_econstruction_info',
							type: 'GET',
							dataType: 'json',
							cache: false,
							data: {client:formData.get('client'), econstruction:formData.get('econstruction')},
							success: function(response) {
								$('.econstruction-'+formData.get('econstruction')+' .required-content').html(response['required_content']);
								$('.econstruction-'+formData.get('econstruction')+' .econstruction-info').html(response['info']);
								$('.econstruction-'+formData.get('econstruction')+' .zalo-link').html(response['zalo']);
								$('.econstruction-'+formData.get('econstruction')+' .file-download').html(response['file']);
								$('.econstruction-'+formData.get('econstruction')+' .econstruction-required').html(response['required']);
								$('.econstruction-'+formData.get('econstruction')+' .econstruction-received').html(response['received']);
								$('.econstruction-'+formData.get('econstruction')+' .econstruction-completed').html(response['completed']);
								$('.econstruction-'+formData.get('econstruction')+' .econstruction-sent').html(response['sent']);
								$('#edit-econstruction .btn-close').trigger('click');
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

		$(document).on('click', '#econstruction_remove_file', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#econstruction_file_id').val('');
			$this.closest('.input-group').remove();
		});
		$(document).on('input', '#econstruction_file', function() {
			let $input = $(this);
			$input.closest('[for="econstruction_file"]').find('.form-control').text($input.val().split('\\').pop());
		});

		$('.econstruction-hide').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				econstruction = $this.data('econstruction'),
				econstruction_title = $this.data('econstructionTitle'),
				$econstruction = $this.closest('.econstruction-item');

			if(confirm('Ẩn/Hiện "'+econstruction_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'econstruction_hide', client: client, econstruction: econstruction},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$econstruction.addClass('active');
						} else if(response===-1) {
							$econstruction.removeClass('active');
						}
					}
				});
			}
		});

		$('.econstruction-toggle').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				econstruction = $this.data('econstruction'),
				econstruction_title = $this.data('econstructionTitle'),
				$econstruction = $this.closest('.econstruction-item');

			if(confirm((($econstruction.hasClass('removed'))?'Sử dụng "':'Loại bỏ "')+econstruction_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'econstruction_toggle', client: client, econstruction: econstruction},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$econstruction.addClass('removed');
						} else if(response===-1) {
							$econstruction.removeClass('removed');
						}
					}
				});
			}
		});

	});
});