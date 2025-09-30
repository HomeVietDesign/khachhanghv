document.addEventListener('DOMContentLoaded', function(e){
	jQuery(function($){
		
		// estimate contractor
		$('#edit-estimate').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,client = $button.data('client')
				,contractor = $button.data('contractor')
				,contractor_title = $button.data('contractor-title')
				;

			$('#edit-estimate-label').text(contractor_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				//dataType: 'json',
				data: {
					action: 'get_edit_estimate_form',
					client:client,
					contractor:contractor
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

			$('#edit-estimate-label').text('');
			$body.text('');
		});

		$(document).on('submit', '#frm-edit-estimate', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-estimate-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_estimate',
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
							url: theme.ajax_url+'?action=get_estimate_info',
							type: 'GET',
							cache: false,
							dataType: 'json',
							data: {client:formData.get('estimate_client'), contractor:formData.get('estimate_contractor')},
							success: function(response) {
								$('.estimate-'+formData.get('estimate_contractor')+' .required-content').html(response['required_content']);
								$('.estimate-'+formData.get('estimate_contractor')+' .zalo-link').html(response['zalo']);
								$('.estimate-'+formData.get('estimate_contractor')+' .attachment-download').html(response['attachment']);
								$('.estimate-'+formData.get('estimate_contractor')+' .contractor-info').html(response['info']);
								$('.estimate-'+formData.get('estimate_contractor')+' .estimate-required').html(response['required']);
								$('.estimate-'+formData.get('estimate_contractor')+' .estimate-received').html(response['received']);
								$('.estimate-'+formData.get('estimate_contractor')+' .estimate-completed').html(response['completed']);
								$('.estimate-'+formData.get('estimate_contractor')+' .estimate-sent').html(response['sent']);
								$('.estimate-'+formData.get('estimate_contractor')+' .estimate-quote').html(response['quote']);

								$('#edit-estimate .btn-close').trigger('click');
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

		$(document).on('click', '#estimate_remove_attachment', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#estimate_attachment_id').val('');
			$this.closest('.input-group').remove();
		});

		$(document).on('input', '#estimate_attachment', function() {
			let $input = $(this);
			$input.closest('[for="estimate_attachment"]').find('.form-control').text($input.val().split('\\').pop());
		});

		$('.estimate-contractor-hide').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				contractor = $this.data('contractor'),
				contractor_title = $this.data('contractorTitle'),
				$estimate = $this.closest('.estimate-item');

			if(confirm('Ẩn/Hiện "'+contractor_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'estimate_contractor_hide', client: client, contractor: contractor},
					beforeSend: function() {

					},
					success: function(response) {
						if(response===1) {
							$estimate.addClass('active');
						} else if(response===-1) {
							$estimate.removeClass('active');
						}
					}
				});
			}
		});

		function getPageNumbers(currentPage, totalPages) {
			const pages = [];

			// Luôn có trang đầu tiên
			pages.push(1);

			// Tính phạm vi trang giữa
			let start = Math.max(2, currentPage - 2);
			let end = Math.min(totalPages - 1, currentPage + 2);

			if (start > 2) {
				pages.push("...");
			}

			for (let i = start; i <= end; i++) {
				pages.push(i);
			}

			if (end < totalPages - 1) {
				pages.push("...");
			}

			// Luôn có trang cuối
			if (totalPages > 1) {
				pages.push(totalPages);
			}

			return pages;
		}

		function renderPagination($paginationLink, currentPage, totalPages) {
			$paginationLink.html('');
			const pages = getPageNumbers(currentPage, totalPages);
			pages.forEach(p => {
				const $btn = $('<button type="button" class="btn btn-sm btn-secondary m-1"></button>');
				$btn.text(p);
				if (p == currentPage) {
					$btn.css('font-weight', 'bold');
					$btn.prop('disabled', true);
				}
				if (p == "...") {
					$btn.prop('disabled', true);
				}
				$paginationLink.append($btn);
			});
		}

		$('.fw-shortcode-estimates section.accordion-item').each(function(index, container){
			let $container = $(container),
				$paginationLink = $container.find('.pagination-link'),
				ids = $paginationLink.data('ids'),
				per = $paginationLink.data('per'),
				totalPages = $paginationLink.data('total');
			if(per>0 && totalPages>0 && per<ids.length) {
				renderPagination($paginationLink, 1, totalPages);
			}
		});

		function paginate(array, pageSize, pageNumber) {
			// pageNumber bắt đầu từ 1
			return array.slice((pageNumber - 1) * pageSize, pageNumber * pageSize);
		}

		$(document).on('click', '.fw-shortcode-estimates section.accordion-item .pagination-link button', function(e){
			let $this = $(this),
				$paginationLink = $this.closest('.pagination-link'),
				$container = $paginationLink.closest('section.accordion-item'),
				$items = $container.find('.items'),
				ids = $paginationLink.data('ids'),
				per = $paginationLink.data('per'),
				client = $paginationLink.data('client'),
				totalPages = $paginationLink.data('total'),
				p = parseInt($this.text());

			const items = paginate(ids, per, p);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {ids: items, action: 'estimate_paginate', client: client},
				beforeSend: function() {

				},
				success: function(response) {
					$items.html(response);
				}
			});

			renderPagination($paginationLink, p, totalPages);
		});
		
	});
});