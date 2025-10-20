document.addEventListener('DOMContentLoaded', function(e){
	const chunkSize = 2* 1024 * 1024; // 2MB
	
	jQuery(function($){

		// investment
		let investmentUploadId = '';
		let investment_ajax_upload = null;

		$('#edit-investment').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,investor = $button.data('investor')
				,investment = $button.data('investment')
				,investment_title = $button.data('investment-title')
				;

			$('#edit-investment-label').text(investment_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_investment_form',
					investor:investor,
					investment:investment
				},
				beforeSend: function(xhr) {
					$body.text('Đang tải..');
				},
				success: function(response) {
					$body.html(response);
					// Khởi tạo lại TinyMCE
					wp.editor.initialize("investment_content", JSON.parse($('#investment_content_settings').val()));
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

			wp.editor.remove('investment_content');

			$('#edit-investment-label').text('');
			$body.text('');

			if(investment_ajax_upload!=null) investment_ajax_upload.abort();

		});

		function get_investment_info(investor, investment, save=true) {
			$.ajax({
				url: theme.ajax_url+'?action=get_investment_info',
				type: 'GET',
				dataType: 'json',
				cache: false,
				data: {investor:investor, investment:investment},
				success: function(response) {
					$('.investment-'+investment+' .investment-content').html(response['content']);
					$('.investment-'+investment+' .investment-info').html(response['info']);
					$('.investment-'+investment+' .zalo-link').html(response['zalo']);
					$('.investment-'+investment+' .file-download').html(response['file']);
					$('.investment-'+investment+' .investment-proc1').html(response['proc1']);
					$('.investment-'+investment+' .investment-proc2').html(response['proc2']);
					$('.investment-'+investment+' .investment-proc3').html(response['proc3']);
					$('.investment-'+investment+' .investment-proc4').html(response['proc4']);
					if(save) {
						$('#edit-investment .btn-close').trigger('click');
					}
				}
			});
		}

		$(document).on('submit', '#frm-edit-investment', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-investment-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_investment',
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
						get_investment_info(formData.get('investor'), formData.get('investment'));
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

		$(document).on('click', '#investment_remove_file', function(e){
			e.preventDefault();
			let $this = $(this), $wrap = $this.closest('.input-group');
			$('#investment_file_id').val('');
			$this.prop('disabled', true);
			$wrap.find('.form-control').html('');

		});

		$(document).on('input', '#investment_file', function() {
			let $input = $(this), $form = $input.closest('form'), files = $input.prop('files');
			$input.closest('[for="investment_file"]').find('.form-control').text($input.val().split('\\').pop());
			
			let investor = $form.find('#investor').val(),
				investment = $form.find('#investment').val(),
				nonce = $form.find('#nonce').val(),
				$uploaded = $form.find('#attachment-uploaded'),
				$uploaded_error = $form.find('#attachment-uploaded-error'),
				$upload_bar = $form.find('#attachment-upload-bar');

			$upload_bar.removeClass('d-none').addClass('d-flex');
			$uploaded.addClass('d-none');
			$uploaded_error.addClass('d-none');

			$upload_bar.find('.abort').on('click', function(e){
				if(investment_ajax_upload!=null) investment_ajax_upload.abort();

				setTimeout(function(){
					if(investment_ajax_upload==null || investment_ajax_upload.status==0) {
						$uploaded.removeClass('d-none');
						$upload_bar.addClass('d-none').removeClass('d-flex');
					}
				},800);
				
				$input.prop('files', new DataTransfer().files);
				$input.closest('[for="investment_file"]').find('.form-control').text('');

			});

			if(files.length>0) {

				const file = files[0];
				
				if (!file) return alert("Chọn file!");

				const totalChunks = Math.ceil(file.size / chunkSize);
				investmentUploadId = btoaUtf8(file.name + '_' + file.size);

				let chunkIndex = 0;

				// Gọi để kiểm tra chunk đã upload (resume)
				$.get(theme.ajax_url, {
					action: 'investment_check_chunks',
					uploadId: investmentUploadId
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
					formData.append('action', 'investment_chunk_upload');
					formData.append('file', blob);
					formData.append('uploadId', investmentUploadId);
					formData.append('fileName', file.name);
					formData.append('chunkIndex', chunkIndex);
					formData.append('totalChunks', totalChunks);
					formData.append('investor', investor);
					formData.append('investment', investment);
					formData.append('nonce', nonce);

					investment_ajax_upload = $.ajax({
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
								$uploaded.find('#investment_file_id').val(res.attachment_id);
								$uploaded.find('#investment_remove_file').prop('disabled', false);
								$uploaded.find('.form-control').text(res.filename);

								get_investment_info(investor, investment, false);

								setTimeout(function(){
									$uploaded.removeClass('d-none');
									$upload_bar.addClass('d-none').removeClass('d-flex');
									$input.prop('files', new DataTransfer().files);
									$input.closest('[for="investment_file"]').find('.form-control').text('');
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

		$('.investment-show').on('click', function(e){
			let $this = $(this),
				investor = $this.data('investor'),
				investment = $this.data('investment'),
				investment_title = $this.data('investmentTitle'),
				$investment = $('.investment-item-'+investment);

			if(confirm('Ẩn/Hiện "'+investment_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'investment_show', inv: investor, investment: investment},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$investment.addClass('active');
						} else if(response===-1) {
							$investment.removeClass('active');
						}
					}
				});
			}
		});

		$('.investment-toggle').on('click', function(e){
			let $this = $(this),
				investor = $this.data('investor'),
				investment = $this.data('investment'),
				investment_title = $this.data('investmentTitle'),
				$investment = $('.investment-item-'+investment);

			if(confirm((($investment.hasClass('removed'))?'Sử dụng "':'Loại bỏ "')+investment_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'investment_toggle', inv: investor, investment: investment},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$investment.addClass('removed');
						} else if(response===-1) {
							$investment.removeClass('removed');
						}
					}
				});
			}
		});

	});
});