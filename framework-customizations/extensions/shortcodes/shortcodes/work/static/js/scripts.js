document.addEventListener('DOMContentLoaded', function(e){
	const chunkSize = 2* 1024 * 1024; // 2MB
	
	jQuery(function($){

		// work
		let workUploadId = '';
		let work_ajax_upload = null;

		$('#edit-work').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,employee = $button.data('employee')
				,work = $button.data('work')
				,work_title = $button.data('work-title')
				;

			$('#edit-work-label').text(work_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_work_form',
					employee:employee,
					work:work
				},
				beforeSend: function(xhr) {
					$body.text('Đang tải..');
				},
				success: function(response) {
					$body.html(response);
					// Khởi tạo lại TinyMCE
					wp.editor.initialize("work_content", JSON.parse($('#work_content_settings').val()));
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

			wp.editor.remove('work_content');

			$('#edit-work-label').text('');
			$body.text('');

			if(work_ajax_upload!=null) work_ajax_upload.abort();

		});

		function get_work_info(employee, work, save=true) {
			$.ajax({
				url: theme.ajax_url+'?action=get_work_info',
				type: 'GET',
				dataType: 'json',
				cache: false,
				data: {employee:employee, work:work},
				success: function(response) {
					$('.work-'+work+' .work-content').html(response['content']);
					$('.work-'+work+' .work-info').html(response['info']);
					$('.work-'+work+' .zalo-link').html(response['zalo']);
					$('.work-'+work+' .file-download').html(response['file']);
					$('.work-'+work+' .work-proc1').html(response['proc1']);
					$('.work-'+work+' .work-proc2').html(response['proc2']);
					$('.work-'+work+' .work-proc3').html(response['proc3']);
					$('.work-'+work+' .work-proc4').html(response['proc4']);
					if(save) {
						$('#edit-work .btn-close').trigger('click');
					}
				}
			});
		}

		$(document).on('submit', '#frm-edit-work', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-work-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_work',
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
						get_work_info(formData.get('employee'), formData.get('work'));
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

		$(document).on('click', '#work_remove_file', function(e){
			e.preventDefault();
			let $this = $(this), $wrap = $this.closest('.input-group');
			$('#work_file_id').val('');
			$this.prop('disabled', true);
			$wrap.find('.form-control').html('');

		});

		$(document).on('input', '#work_file', function() {
			let $input = $(this), $form = $input.closest('form'), files = $input.prop('files');
			$input.closest('[for="work_file"]').find('.form-control').text($input.val().split('\\').pop());
			
			let employee = $form.find('#employee').val(),
				work = $form.find('#work').val(),
				nonce = $form.find('#nonce').val(),
				$uploaded = $form.find('#attachment-uploaded'),
				$uploaded_error = $form.find('#attachment-uploaded-error'),
				$upload_bar = $form.find('#attachment-upload-bar');

			$upload_bar.removeClass('d-none').addClass('d-flex');
			$uploaded.addClass('d-none');
			$uploaded_error.addClass('d-none');

			$upload_bar.find('.abort').on('click', function(e){
				if(work_ajax_upload!=null) work_ajax_upload.abort();

				setTimeout(function(){
					if(work_ajax_upload==null || work_ajax_upload.status==0) {
						$uploaded.removeClass('d-none');
						$upload_bar.addClass('d-none').removeClass('d-flex');
					}
				},800);
				
				$input.prop('files', new DataTransfer().files);
				$input.closest('[for="work_file"]').find('.form-control').text('');

			});

			if(files.length>0) {

				const file = files[0];
				
				if (!file) return alert("Chọn file!");

				const totalChunks = Math.ceil(file.size / chunkSize);
				workUploadId = btoaUtf8(file.name + '_' + file.size);

				let chunkIndex = 0;

				// Gọi để kiểm tra chunk đã upload (resume)
				$.get(theme.ajax_url, {
					action: 'work_check_chunks',
					uploadId: workUploadId
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
					formData.append('action', 'work_chunk_upload');
					formData.append('file', blob);
					formData.append('uploadId', workUploadId);
					formData.append('fileName', file.name);
					formData.append('chunkIndex', chunkIndex);
					formData.append('totalChunks', totalChunks);
					formData.append('employee', employee);
					formData.append('work', work);
					formData.append('nonce', nonce);

					work_ajax_upload = $.ajax({
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
								$uploaded.find('#work_file_id').val(res.attachment_id);
								$uploaded.find('#work_remove_file').prop('disabled', false);
								$uploaded.find('.form-control').text(res.filename);

								get_work_info(employee, work, false);

								setTimeout(function(){
									$uploaded.removeClass('d-none');
									$upload_bar.addClass('d-none').removeClass('d-flex');
									$input.prop('files', new DataTransfer().files);
									$input.closest('[for="work_file"]').find('.form-control').text('');
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

		$('.work-show').on('click', function(e){
			let $this = $(this),
				employee = $this.data('employee'),
				work = $this.data('work'),
				work_title = $this.data('workTitle'),
				$work = $('.work-item-'+work);

			if(confirm('Ẩn/Hiện "'+work_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'work_show', em: employee, work: work},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$work.addClass('active');
						} else if(response===-1) {
							$work.removeClass('active');
						}
					}
				});
			}
		});

		$('.work-toggle').on('click', function(e){
			let $this = $(this),
				employee = $this.data('employee'),
				work = $this.data('work'),
				work_title = $this.data('workTitle'),
				$work = $('.work-item-'+work);

			if(confirm((($work.hasClass('removed'))?'Sử dụng "':'Loại bỏ "')+work_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'work_toggle', em: employee, work: work},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$work.addClass('removed');
						} else if(response===-1) {
							$work.removeClass('removed');
						}
					}
				});
			}
		});

	});
});