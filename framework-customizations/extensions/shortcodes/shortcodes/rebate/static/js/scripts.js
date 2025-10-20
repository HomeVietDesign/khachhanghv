document.addEventListener('DOMContentLoaded', function(e){
	const chunkSize = 2* 1024 * 1024; // 2MB
	
	jQuery(function($){

		// rebate
		let rebateUploadId = '';
		let rebate_ajax_upload = null;

		$('#edit-rebate').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,product = $button.data('product')
				,rebate = $button.data('rebate')
				,rebate_title = $button.data('rebate-title')
				;

			$('#edit-rebate-label').text(rebate_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_rebate_form',
					product:product,
					rebate:rebate
				},
				beforeSend: function(xhr) {
					$body.text('Đang tải..');
				},
				success: function(response) {
					$body.html(response);
					// Khởi tạo lại TinyMCE
					wp.editor.initialize("rebate_content", JSON.parse($('#rebate_content_settings').val()));
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

			wp.editor.remove('rebate_content');

			$('#edit-rebate-label').text('');
			$body.text('');

			if(rebate_ajax_upload!=null) rebate_ajax_upload.abort();

		});

		function get_rebate_info(product, rebate, save=true) {
			$.ajax({
				url: theme.ajax_url+'?action=get_rebate_info',
				type: 'GET',
				dataType: 'json',
				cache: false,
				data: {product:product, rebate:rebate},
				success: function(response) {
					$('.rebate-'+rebate+' .rebate-content').html(response['content']);
					$('.rebate-'+rebate+' .rebate-info').html(response['info']);
					$('.rebate-'+rebate+' .zalo-link').html(response['zalo']);
					$('.rebate-'+rebate+' .file-download').html(response['file']);
					$('.rebate-'+rebate+' .rebate-proc1').html(response['proc1']);
					$('.rebate-'+rebate+' .rebate-proc2').html(response['proc2']);
					$('.rebate-'+rebate+' .rebate-proc3').html(response['proc3']);
					$('.rebate-'+rebate+' .rebate-proc4').html(response['proc4']);
					if(save) {
						$('#edit-rebate .btn-close').trigger('click');
					}
				}
			});
		}

		$(document).on('submit', '#frm-edit-rebate', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-rebate-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_rebate',
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
						get_rebate_info(formData.get('product'), formData.get('rebate'));
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

		$(document).on('click', '#rebate_remove_file', function(e){
			e.preventDefault();
			let $this = $(this), $wrap = $this.closest('.input-group');
			$('#rebate_file_id').val('');
			$this.prop('disabled', true);
			$wrap.find('.form-control').html('');

		});

		$(document).on('input', '#rebate_file', function() {
			let $input = $(this), $form = $input.closest('form'), files = $input.prop('files');
			$input.closest('[for="rebate_file"]').find('.form-control').text($input.val().split('\\').pop());
			
			let product = $form.find('#product').val(),
				rebate = $form.find('#rebate').val(),
				nonce = $form.find('#nonce').val(),
				$uploaded = $form.find('#attachment-uploaded'),
				$uploaded_error = $form.find('#attachment-uploaded-error'),
				$upload_bar = $form.find('#attachment-upload-bar');

			$upload_bar.removeClass('d-none').addClass('d-flex');
			$uploaded.addClass('d-none');
			$uploaded_error.addClass('d-none');

			$upload_bar.find('.abort').on('click', function(e){
				if(rebate_ajax_upload!=null) rebate_ajax_upload.abort();

				setTimeout(function(){
					if(rebate_ajax_upload==null || rebate_ajax_upload.status==0) {
						$uploaded.removeClass('d-none');
						$upload_bar.addClass('d-none').removeClass('d-flex');
					}
				},800);
				
				$input.prop('files', new DataTransfer().files);
				$input.closest('[for="rebate_file"]').find('.form-control').text('');

			});

			if(files.length>0) {

				const file = files[0];
				
				if (!file) return alert("Chọn file!");

				const totalChunks = Math.ceil(file.size / chunkSize);
				rebateUploadId = btoaUtf8(file.name + '_' + file.size);

				let chunkIndex = 0;

				// Gọi để kiểm tra chunk đã upload (resume)
				$.get(theme.ajax_url, {
					action: 'rebate_check_chunks',
					uploadId: rebateUploadId
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
					formData.append('action', 'rebate_chunk_upload');
					formData.append('file', blob);
					formData.append('uploadId', rebateUploadId);
					formData.append('fileName', file.name);
					formData.append('chunkIndex', chunkIndex);
					formData.append('totalChunks', totalChunks);
					formData.append('product', product);
					formData.append('rebate', rebate);
					formData.append('nonce', nonce);

					rebate_ajax_upload = $.ajax({
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
								$uploaded.find('#rebate_file_id').val(res.attachment_id);
								$uploaded.find('#rebate_remove_file').prop('disabled', false);
								$uploaded.find('.form-control').text(res.filename);

								get_rebate_info(product, rebate, false);

								setTimeout(function(){
									$uploaded.removeClass('d-none');
									$upload_bar.addClass('d-none').removeClass('d-flex');
									$input.prop('files', new DataTransfer().files);
									$input.closest('[for="rebate_file"]').find('.form-control').text('');
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

		$('.rebate-show').on('click', function(e){
			let $this = $(this),
				product = $this.data('product'),
				rebate = $this.data('rebate'),
				rebate_title = $this.data('rebateTitle'),
				$rebate = $('.rebate-item-'+rebate);

			if(confirm('Ẩn/Hiện "'+rebate_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'rebate_show', pro: product, rebate: rebate},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$rebate.addClass('active');
						} else if(response===-1) {
							$rebate.removeClass('active');
						}
					}
				});
			}
		});

		$('.rebate-toggle').on('click', function(e){
			let $this = $(this),
				product = $this.data('product'),
				rebate = $this.data('rebate'),
				rebate_title = $this.data('rebateTitle'),
				$rebate = $('.rebate-item-'+rebate);

			if(confirm((($rebate.hasClass('removed'))?'Sử dụng "':'Loại bỏ "')+rebate_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'rebate_toggle', pro: product, rebate: rebate},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$rebate.addClass('removed');
						} else if(response===-1) {
							$rebate.removeClass('removed');
						}
					}
				});
			}
		});

	});
});