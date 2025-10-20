document.addEventListener('DOMContentLoaded', function(e){
	jQuery(function($){

		// edit document
		$('#edit-document').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,client = $button.data('client')
				,doc = $button.data('document')
				,document_title = $button.data('document-title')
				;

			$('#edit-document-label').text(document_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_document_form',
					client:client,
					document:doc
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

			$('#edit-document-label').text('');
			$body.text('');
		});

		$(document).on('submit', '#frm-edit-document', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-document-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_document',
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
							url: theme.ajax_url+'?action=get_document_info',
							type: 'GET',
							cache: false,
							dataType: 'json',
							data: {client:formData.get('document_client'), document:formData.get('document_id')},
							success: function(response) {
								$('.document-'+formData.get('document_id')+' .required-content').html(response['required_content']);
								$('.document-'+formData.get('document_id')+' .zalo-link').html(response['zalo']);
								$('.document-'+formData.get('document_id')+' .attachment-download').html(response['attachment']);
								$('.document-'+formData.get('document_id')+' .document-info').html(response['info']);
								$('.document-'+formData.get('document_id')+' .document-required').html(response['required']);
								$('.document-'+formData.get('document_id')+' .document-created').html(response['created']);
								$('.document-'+formData.get('document_id')+' .document-completed').html(response['completed']);
								$('.document-'+formData.get('document_id')+' .document-sent').html(response['sent']);
								$('#edit-document .btn-close').trigger('click');
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

		$(document).on('click', '#document_remove_attachment', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#document_attachment_id').val('');
			$this.closest('.input-group').remove();
		});

		$(document).on('input', '#document_attachment', function() {
			let $input = $(this);
			$input.closest('[for="document_attachment"]').find('.form-control').text($input.val().split('\\').pop());
		});

		$('.document-hide').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				doc = $this.data('document'),
				doc_title = $this.data('documentTitle'),
				$doc = $this.closest('.document-item');

			if(confirm('Ẩn/Hiện "'+doc_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'document_hide', client: client, doc: doc},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$doc.addClass('active');
						} else if(response===-1) {
							$doc.removeClass('active');
						}
					}
				});
			}
		});

		$('.document-toggle').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				doc = $this.data('document'),
				doc_title = $this.data('documentTitle'),
				$doc = $this.closest('.document-item');

			if(confirm((($doc.hasClass('removed'))?'Sử dụng "':'Loại bỏ "')+doc_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'document_toggle', client: client, doc: doc},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$doc.addClass('removed');
						} else if(response===-1) {
							$doc.removeClass('removed');
						}
					}
				});
			}
		});

	});
});