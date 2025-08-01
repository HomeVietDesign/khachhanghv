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
					//$body.find('#document_value').inputNumber({'negative':false});
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
								$('.document-'+formData.get('document_id')+' .zalo-link').html(response['zalo']);
								$('.document-'+formData.get('document_id')+' .attachment-download').html(response['attachment']);
								$('.document-'+formData.get('document_id')+' .document-info').html(response['info']);
								$('.document-'+formData.get('document_id')+' .document-required').html(response['required']);
								$('.document-'+formData.get('document_id')+' .document-created').html(response['created']);
								$('.document-'+formData.get('document_id')+' .document-completed').html(response['completed']);
								$('.document-'+formData.get('document_id')+' .document-sent').html(response['sent']);
								$('.document-'+formData.get('document_id')+' .document-selected').html(response['selected']);
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

			if(confirm(doc_title)) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'document_hide', client: client, doc: doc},
					beforeSend: function() {

					},
					success: function(response) {
						if(response) {
							$doc.addClass('hide');
						}
					}
				});
			}
		});

		if($('#document-filter-form').length) {
			let none = 0, required = 0, created = 0, completed = 0, sent = 0, selected = 0;
			$('#document-filter-form').find('.document-item:not(.hide)').each(function(i, el){
				let $el = $(el), isNone = true;
					
				if($el.find('.document-required').hasClass('on')) {
					required += 1;
					isNone = false;
				}
				if($el.find('.document-created').hasClass('on')) {
					created += 1;
					isNone = false;
				}
				if($el.find('.document-completed').hasClass('on')) {
					completed += 1;
					isNone = false;
				}
				if($el.find('.document-sent').hasClass('on')) {
					sent += 1;
					isNone = false;
				}
				if($el.find('.document-selected').hasClass('on')) {
					selected += 1;
					isNone = false;
				}
				if(isNone) {
					none += 1;
				}
			});
			$('label[for="progress-none"] span').text(none);
			$('label[for="progress-required"] span').text(required);
			$('label[for="progress-created"] span').text(created);
			$('label[for="progress-completed"] span').text(completed);
			$('label[for="progress-sent"] span').text(sent);
			$('label[for="progress-selected"] span').text(selected);
		}

	});
});