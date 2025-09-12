document.addEventListener('DOMContentLoaded', function(e){
	jQuery(function($){

		$('#edit-nha88').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,nha88_type = $button.data('nha88_type')
				,nha88 = $button.data('nha88')
				,nha88_title = $button.data('nha88-title')
				;
			console.log($modal);
			$('#edit-nha88-label').text(nha88_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_nha88_form',
					nha88_type:nha88_type,
					nha88:nha88
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
							data: {nha88_type:formData.get('nha88_type'), nha88:formData.get('nha88_id')},
							success: function(response) {
								$('.nha88-'+formData.get('nha88_id')+' .zalo-link').html(response['zalo']);
								$('.nha88-'+formData.get('nha88_id')+' .nha88-info').html(response['info']);
								$('.nha88-'+formData.get('nha88_id')+' .nha88-required').html(response['required']);
								$('.nha88-'+formData.get('nha88_id')+' .nha88-created').html(response['created']);
								$('.nha88-'+formData.get('nha88_id')+' .nha88-completed').html(response['completed']);
								$('.nha88-'+formData.get('nha88_id')+' .nha88-sent').html(response['sent']);
								$('.nha88-'+formData.get('nha88_id')+' .nha88-sold').html(response['sold']);
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

		$(document).on('click', '#nha88_remove_attachment', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#nha88_attachment_id').val('');
			$this.closest('.input-group').remove();
		});

		$(document).on('input', '#nha88_attachment', function() {
			let $input = $(this);
			$input.closest('[for="nha88_attachment"]').find('.form-control').text($input.val().split('\\').pop());
		});

		$('.nha88-hide').on('click', function(e){
			let $this = $(this),
				nha88_type = $this.data('nha88_type'),
				nha88 = $this.data('nha88'),
				nha88_title = $this.data('nha88Title'),
				$nha88 = $this.closest('.nha88-item');

			if(confirm(nha88_title)) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'nha88_hide', nha88_type: nha88_type, nha88: nha88},
					beforeSend: function() {

					},
					success: function(response) {
						if(response) {
							$nha88.addClass('hide');
						}
					}
				});
			}
		});

		if($('#nha88-filter-form').length) {
			let none = 0, required = 0, created = 0, completed = 0, sent = 0, sold = 0;
			$('#nha88-filter-form').find('.nha88-item:not(.hide)').each(function(i, el){
				let $el = $(el), isNone = true;

				if($el.find('.nha88-required').hasClass('on')) {
					required += 1;
					isNone = false;
				}
				if($el.find('.nha88-created').hasClass('on')) {
					created += 1;
					isNone = false;
				}
				if($el.find('.nha88-completed').hasClass('on')) {
					completed += 1;
					isNone = false;
				}
				if($el.find('.nha88-sent').hasClass('on')) {
					sent += 1;
					isNone = false;
				}
				if($el.find('.nha88-sold').hasClass('on')) {
					sold += 1;
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
			$('label[for="progress-sold"] span').text(sold);
		}
		
	});
});