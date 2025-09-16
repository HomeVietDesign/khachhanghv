document.addEventListener('DOMContentLoaded', function(e){
	const chunkSize = 2* 1024 * 1024; // 2MB
	
	jQuery(function($){

		// efurniture
		let efurnitureUploadId = '';
		let efurniture_ajax_upload = null;

		$('#edit-efurniture').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,client = $button.data('client')
				,efurniture = $button.data('efurniture')
				,efurniture_title = $button.data('efurniture-title')
				;

			$('#edit-efurniture-label').text(efurniture_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_efurniture_form',
					client:client,
					efurniture:efurniture
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

			$('#edit-efurniture-label').text('');
			$body.text('');

			if(efurniture_ajax_upload!=null) efurniture_ajax_upload.abort();

		});

		function get_efurniture_info(client, efurniture, save=true) {
			$.ajax({
				url: theme.ajax_url+'?action=get_efurniture_info',
				type: 'GET',
				dataType: 'json',
				cache: false,
				data: {client:client, efurniture:efurniture},
				success: function(response) {
					$('.efurniture-'+efurniture+' .efurniture-info').html(response['info']);
					$('.efurniture-'+efurniture+' .zalo-link').html(response['zalo']);
					$('.efurniture-'+efurniture+' .file-download').html(response['file']);
					$('.efurniture-'+efurniture+' .efurniture-required').html(response['required']);
					$('.efurniture-'+efurniture+' .efurniture-received').html(response['received']);
					$('.efurniture-'+efurniture+' .efurniture-completed').html(response['completed']);
					$('.efurniture-'+efurniture+' .efurniture-sent').html(response['sent']);
					$('.efurniture-'+efurniture+' .efurniture-quote').html(response['quote']);
					if(save) {
						$('#edit-efurniture .btn-close').trigger('click');
					}
				}
			});
		}

		$(document).on('submit', '#frm-edit-efurniture', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-efurniture-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_efurniture',
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
						get_efurniture_info(formData.get('client'), formData.get('efurniture'));
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

		$(document).on('click', '#efurniture_remove_file', function(e){
			e.preventDefault();
			let $this = $(this), $wrap = $this.closest('.input-group');
			$('#efurniture_file_id').val('');
			$this.prop('disabled', true);
			$wrap.find('.form-control').html('');

		});

		$(document).on('input', '#efurniture_file', function() {
			let $input = $(this), $form = $input.closest('form'), files = $input.prop('files');
			$input.closest('[for="efurniture_file"]').find('.form-control').text($input.val().split('\\').pop());
			
			let client = $form.find('#client').val(),
				efurniture = $form.find('#efurniture').val(),
				nonce = $form.find('#nonce').val(),
				$uploaded = $form.find('#attachment-uploaded'),
				$uploaded_error = $form.find('#attachment-uploaded-error'),
				$upload_bar = $form.find('#attachment-upload-bar');

			$upload_bar.removeClass('d-none').addClass('d-flex');
			$uploaded.addClass('d-none');
			$uploaded_error.addClass('d-none');

			$upload_bar.find('.abort').on('click', function(e){
				if(efurniture_ajax_upload!=null) efurniture_ajax_upload.abort();

				setTimeout(function(){
					if(efurniture_ajax_upload==null || efurniture_ajax_upload.status==0) {
						$uploaded.removeClass('d-none');
						$upload_bar.addClass('d-none').removeClass('d-flex');
					}
				},800);
				
				$input.prop('files', new DataTransfer().files);
				$input.closest('[for="efurniture_file"]').find('.form-control').text('');

			});

			if(files.length>0) {

				const file = files[0];
				
				if (!file) return alert("Chọn file!");

				const totalChunks = Math.ceil(file.size / chunkSize);
				efurnitureUploadId = btoaUtf8(file.name + '_' + file.size);

				let chunkIndex = 0;

				// Gọi để kiểm tra chunk đã upload (resume)
				$.get(theme.ajax_url, {
					action: 'efurniture_check_chunks',
					uploadId: efurnitureUploadId
				}, function(uploadedChunks) {
					if (Array.isArray(uploadedChunks)) {
						uploadNext(uploadedChunks);
					} else {
						uploadNext([]);
					}
				}, 'json');

				function uploadNext(uploadedChunks) {
					if (chunkIndex >= totalChunks) {
						return;
					}

					if (uploadedChunks.includes(chunkIndex)) {
						chunkIndex++;
						uploadNext(uploadedChunks);
						return;
					}

					const start = chunkIndex * chunkSize;
					const end = Math.min(start + chunkSize, file.size);
					const blob = file.slice(start, end);

					const formData = new FormData();
					formData.append('action', 'efurniture_chunk_upload');
					formData.append('file', blob);
					formData.append('uploadId', efurnitureUploadId);
					formData.append('fileName', file.name);
					formData.append('chunkIndex', chunkIndex);
					formData.append('totalChunks', totalChunks);
					formData.append('client', client);
					formData.append('efurniture', efurniture);
					formData.append('nonce', nonce);

					efurniture_ajax_upload = $.ajax({
						url: theme.ajax_url,
						method: 'POST',
						data: formData,
						contentType: false,
						processData: false,
						success: function (res) {

							chunkIndex++;
							let percent = Math.floor((chunkIndex / totalChunks) * 100);
							
							$upload_bar.find('.progress').attr('aria-valuenow', percent);
							$upload_bar.find('.progress-bar').css('width', percent+'%');
							$upload_bar.find('.percent').text(percent+'%');
							//$('#uploadProgress').val(percent);
							
							uploadNext(uploadedChunks);

							if(res.success) {
								$uploaded.find('#efurniture_file_id').val(res.attachment_id);
								$uploaded.find('#efurniture_remove_file').prop('disabled', false);
								$uploaded.find('.form-control').text(res.filename);

								get_efurniture_info(client, efurniture, false);

								setTimeout(function(){
									$uploaded.removeClass('d-none');
									$upload_bar.addClass('d-none').removeClass('d-flex');
									$input.prop('files', new DataTransfer().files);
									$input.closest('[for="efurniture_file"]').find('.form-control').text('');
								},1000);
							} else if (chunkIndex == totalChunks) {
								$uploaded_error.html(res.msg);
								$uploaded_error.removeClass('d-none');
							}
						},
						error: function (xhr) {
							$upload_bar.find('.percent').text(xhr.responseText);
						}
					});
				}
			}
		});

		if($('#efurniture-filter-form').length) {
			let none = 0, required = 0, received = 0, completed = 0, sent = 0, quote = 0;
			$('#efurniture-filter-form').find('.efurniture-item:not(.hide)').each(function(i, el){
				let $el = $(el), isNone = true;
					
				if($el.find('.efurniture-required').hasClass('on')) {
					required += 1;
					isNone = false;
				}
				if($el.find('.efurniture-received').hasClass('on')) {
					received += 1;
					isNone = false;
				}
				if($el.find('.efurniture-completed').hasClass('on')) {
					completed += 1;
					isNone = false;
				}
				if($el.find('.efurniture-sent').hasClass('on')) {
					sent += 1;
					isNone = false;
				}
				if($el.find('.efurniture-quote').hasClass('on')) {
					quote += 1;
					isNone = false;
				}
				if(isNone) {
					none += 1;
				}
			});
			$('label[for="progress-none"] span').text(none);
			$('label[for="progress-required"] span').text(required);
			$('label[for="progress-received"] span').text(received);
			$('label[for="progress-completed"] span').text(completed);
			$('label[for="progress-sent"] span').text(sent);
			$('label[for="progress-quote"] span').text(quote);
		}

		$('.efurniture-hide').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				efurniture = $this.data('efurniture'),
				efurniture_title = $this.data('efurnitureTitle'),
				$efurniture = $this.closest('.efurniture-item');

			if(confirm('Ẩn/Hiện "'+efurniture_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'efurniture_hide', client: client, efurniture: efurniture},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$efurniture.addClass('active');
						} else if(response===-1) {
							$efurniture.removeClass('active');
						}
					}
				});
			}
		});

	});
});