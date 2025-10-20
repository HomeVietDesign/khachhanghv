document.addEventListener('DOMContentLoaded', function(e){
	const chunkSize = 2* 1024 * 1024; // 2MB
	
	jQuery(function($){

		// elighting
		let elightingUploadId = '';
		let elighting_ajax_upload = null;

		$('#edit-elighting').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,client = $button.data('client')
				,elighting = $button.data('elighting')
				,elighting_title = $button.data('elighting-title')
				;

			$('#edit-elighting-label').text(elighting_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_elighting_form',
					client:client,
					elighting:elighting
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

			$('#edit-elighting-label').text('');
			$body.text('');

			if(elighting_ajax_upload!=null) elighting_ajax_upload.abort();

		});

		function get_elighting_info(client, elighting, save=true) {
			$.ajax({
				url: theme.ajax_url+'?action=get_elighting_info',
				type: 'GET',
				dataType: 'json',
				cache: false,
				data: {client:client, elighting:elighting},
				success: function(response) {
					$('.elighting-'+elighting+' .required-content').html(response['required_content']);
					$('.elighting-'+elighting+' .elighting-info').html(response['info']);
					$('.elighting-'+elighting+' .zalo-link').html(response['zalo']);
					$('.elighting-'+elighting+' .file-download').html(response['file']);
					$('.elighting-'+elighting+' .elighting-required').html(response['required']);
					$('.elighting-'+elighting+' .elighting-received').html(response['received']);
					$('.elighting-'+elighting+' .elighting-completed').html(response['completed']);
					$('.elighting-'+elighting+' .elighting-sent').html(response['sent']);
					if(save) {
						$('#edit-elighting .btn-close').trigger('click');
					}
				}
			});
		}

		$(document).on('submit', '#frm-edit-elighting', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-elighting-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_elighting',
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
						get_elighting_info(formData.get('client'), formData.get('elighting'));
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

		$(document).on('click', '#elighting_remove_file', function(e){
			e.preventDefault();
			let $this = $(this), $wrap = $this.closest('.input-group');
			$('#elighting_file_id').val('');
			$this.prop('disabled', true);
			$wrap.find('.form-control').html('');

		});

		$(document).on('input', '#elighting_file', function() {
			let $input = $(this), $form = $input.closest('form'), files = $input.prop('files');
			$input.closest('[for="elighting_file"]').find('.form-control').text($input.val().split('\\').pop());
			
			let client = $form.find('#client').val(),
				elighting = $form.find('#elighting').val(),
				nonce = $form.find('#nonce').val(),
				$uploaded = $form.find('#attachment-uploaded'),
				$uploaded_error = $form.find('#attachment-uploaded-error'),
				$upload_bar = $form.find('#attachment-upload-bar');

			$upload_bar.removeClass('d-none').addClass('d-flex');
			$uploaded.addClass('d-none');
			$uploaded_error.addClass('d-none');

			$upload_bar.find('.abort').on('click', function(e){
				if(elighting_ajax_upload!=null) elighting_ajax_upload.abort();

				setTimeout(function(){
					if(elighting_ajax_upload==null || elighting_ajax_upload.status==0) {
						$uploaded.removeClass('d-none');
						$upload_bar.addClass('d-none').removeClass('d-flex');
					}
				},800);
				
				$input.prop('files', new DataTransfer().files);
				$input.closest('[for="elighting_file"]').find('.form-control').text('');

			});

			if(files.length>0) {

				const file = files[0];
				
				if (!file) return alert("Chọn file!");

				const totalChunks = Math.ceil(file.size / chunkSize);
				elightingUploadId = btoaUtf8(file.name + '_' + file.size);

				let chunkIndex = 0;

				// Gọi để kiểm tra chunk đã upload (resume)
				$.get(theme.ajax_url, {
					action: 'elighting_check_chunks',
					uploadId: elightingUploadId
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
					formData.append('action', 'elighting_chunk_upload');
					formData.append('file', blob);
					formData.append('uploadId', elightingUploadId);
					formData.append('fileName', file.name);
					formData.append('chunkIndex', chunkIndex);
					formData.append('totalChunks', totalChunks);
					formData.append('client', client);
					formData.append('elighting', elighting);
					formData.append('nonce', nonce);

					elighting_ajax_upload = $.ajax({
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
								$uploaded.find('#elighting_file_id').val(res.attachment_id);
								$uploaded.find('#elighting_remove_file').prop('disabled', false);
								$uploaded.find('.form-control').text(res.filename);

								get_elighting_info(client, elighting, false);

								setTimeout(function(){
									$uploaded.removeClass('d-none');
									$upload_bar.addClass('d-none').removeClass('d-flex');
									$input.prop('files', new DataTransfer().files);
									$input.closest('[for="elighting_file"]').find('.form-control').text('');
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

		$('.elighting-hide').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				elighting = $this.data('elighting'),
				elighting_title = $this.data('elightingTitle'),
				$elighting = $this.closest('.elighting-item');

			if(confirm('Ẩn/Hiện "'+elighting_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'elighting_hide', client: client, elighting: elighting},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$elighting.addClass('active');
						} else if(response===-1) {
							$elighting.removeClass('active');
						}
					}
				});
			}
		});

		$('.elighting-toggle').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				elighting = $this.data('elighting'),
				elighting_title = $this.data('elightingTitle'),
				$elighting = $this.closest('.elighting-item');

			if(confirm((($elighting.hasClass('removed'))?'Sử dụng "':'Loại bỏ "')+elighting_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'elighting_toggle', client: client, elighting: elighting},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$elighting.addClass('removed');
						} else if(response===-1) {
							$elighting.removeClass('removed');
						}
					}
				});
			}
		});

	});
});