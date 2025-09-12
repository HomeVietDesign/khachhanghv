document.addEventListener('DOMContentLoaded', function(e){
	jQuery(function($){

		// procedure contractor
		$('#edit-procedure').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,client = $button.data('client')
				,contractor = $button.data('contractor')
				,contractor_title = $button.data('contractor-title')
				;

			$('#edit-procedure-label').text(contractor_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_procedure_form',
					client:client,
					contractor:contractor
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

			$('#edit-procedure-label').text('');
			$body.text('');
		});

		$(document).on('submit', '#frm-edit-procedure', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-procedure-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_procedure',
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
							url: theme.ajax_url+'?action=get_procedure_info',
							type: 'GET',
							cache: false,
							dataType: 'json',
							data: {client:formData.get('procedure_client'), contractor:formData.get('procedure_contractor')},
							success: function(response) {
								$('.procedure-'+formData.get('procedure_contractor')+' .zalo-link').html(response['zalo']);
								$('.procedure-'+formData.get('procedure_contractor')+' .attachment-download').html(response['attachment']);
								$('.procedure-'+formData.get('procedure_contractor')+' .contractor-info').html(response['info']);
								$('.procedure-'+formData.get('procedure_contractor')+' .procedure-required').html(response['required']);
								$('.procedure-'+formData.get('procedure_contractor')+' .procedure-received').html(response['received']);
								$('.procedure-'+formData.get('procedure_contractor')+' .procedure-completed').html(response['completed']);
								$('.procedure-'+formData.get('procedure_contractor')+' .procedure-sent').html(response['sent']);
								$('.procedure-'+formData.get('procedure_contractor')+' .procedure-quote').html(response['quote']);
								$('#edit-procedure .btn-close').trigger('click');
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

		$(document).on('click', '#procedure_remove_attachment', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#procedure_attachment_id').val('');
			$this.closest('.input-group').remove();
		});

		$(document).on('input', '#procedure_attachment', function() {
			let $input = $(this);
			$input.closest('[for="procedure_attachment"]').find('.form-control').text($input.val().split('\\').pop());
		});

		$('.procedure-contractor-hide').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				contractor = $this.data('contractor'),
				contractor_title = $this.data('contractorTitle'),
				$procedure = $this.closest('.procedure-item');

			if(confirm('Ẩn nhà thầu "'+contractor_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'procedure_contractor_hide', client: client, contractor: contractor},
					beforeSend: function() {

					},
					success: function(response) {
						if(response) {
							$procedure.addClass('hide');
						}
					}
				});
			}
		});

		if($('#procedure-filter-form').length) {
			let none = 0, required = 0, received = 0, completed = 0, sent = 0, quote = 0;
			$('#procedure-filter-form').find('.procedure-item:not(.hide)').each(function(i, el){
				let $el = $(el), isNone = true;
					
				if($el.find('.procedure-required').hasClass('on')) {
					required += 1;
					isNone = false;
				}
				if($el.find('.procedure-received').hasClass('on')) {
					received += 1;
					isNone = false;
				}
				if($el.find('.procedure-completed').hasClass('on')) {
					completed += 1;
					isNone = false;
				}
				if($el.find('.procedure-sent').hasClass('on')) {
					sent += 1;
					isNone = false;
				}
				if($el.find('.procedure-quote').hasClass('on')) {
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

		$('.fw-shortcode-procedures section.accordion-item').each(function(index, container){
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

		$(document).on('click', '.fw-shortcode-procedures section.accordion-item .pagination-link button', function(e){
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
				data: {ids: items, action: 'procedure_paginate', client: client},
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