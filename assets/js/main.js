window.addEventListener('DOMContentLoaded', function(){
	
	// console.log(document.referrer);
	// console.log(window.atob(getCookie('_ref')).split(','));

	const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
	const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))

	jQuery(function($){

		let debounced_contractor_search = debounce((event) => {
			let kw = $('#contractor-search-input').val().trim(),
				province = parseInt($('#contractor-search-province').val()),
				$result_wrap_el = $('#contractor-search-result-wrap'),
				$result_el = $result_wrap_el.find('.contractor-search-result'),
				$loading = $result_wrap_el.find('.loading');

			$loading.removeClass('invisible');
			$result_el.addClass('invisible');

			if(kw.length>2) {
				$loading.html('Đang tìm kiếm...');
				$.ajax({
					url: theme.ajax_url+'?action=contractor_search',
					method:'POST',
					data:{kw: kw, province: province},
					//dataType:'json',
					beforeSend:function(){
						
					},
					success:function(response){
						//console.log(response);
						$result_el.html(response);
						$result_el.removeClass('invisible');
						$loading.addClass('invisible');

						$result_el.find(".change-province").select2({
							data: theme.provinces,
							width: '100%',
							allowClear: true,
							dropdownAutoWidth: true,
							dropdownCssClass: 'change-province-dropdown',
							placeholder: 'Chọn tỉnh'
						});
					},
					complete: function() {
						
					}
				});
			} else if(kw.length>1) {

				$loading.html('Nhập từ khóa từ 3 ký tự trở lên');
			} else {
				$result_el.html('');
				$loading.addClass('invisible');
			}
		}, 800); // Wait 800ms after the last keypress

		$(document).on('input', '#contractor-search-input', function(event) {
			debounced_contractor_search(event);
		});

		function set_vh_size() {
			let vh = $(window).innerHeight();
			if($('#site-header').length>0) {
				vh -= $('#site-header').height();
			}
			if($('#wpadminbar').length>0) {
				vh -= $('#wpadminbar').height();
			}
			if($('#footer-buttons-fixed').length>0) {
				vh -= $('#footer-buttons-fixed').height();
			}
			$('#main-nav ul.sub-menu').css('max-height', `${vh}px`);
		}

		function align_submenu() {
			let win_width = $(window).width();
			$('#main-nav ul.sub-menu').each(function(index){
				let $sub_menu = $(this),
					$wrap_sub = $sub_menu.parent();
				let delta = $wrap_sub.offset().left + $sub_menu.width() - win_width;
				if( delta>0 ) {
					$sub_menu.css('right', '0');
					$sub_menu.css('left', 'auto');
				} else {
					$sub_menu.css('left', '0');
					$sub_menu.css('right', 'auto');
				}
			});
		}

		$(window).on('resize', function(){
			set_vh_size();
			align_submenu();
		}).resize();

		$('a[href$="#"]').on('click', function(e){
			e.preventDefault();
			return false;
		});
		
		// xử lý sub menu
		$('#main-nav a.toggle-sub-menu').on('click', function(e){
			e.preventDefault();
			e.stopPropagation();
			let $this = $(this);
			console.log($this);
			$this.parent('li').siblings().find('ul.sub-menu').removeClass('open');

			let sub = $this.next('ul.sub-menu');

			sub.toggleClass('open');

		});
		

		$('body').on('click', function(e){
			$('#main-nav ul.sub-menu').removeClass('open');
		});

		$('#modal-video-player').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget),
				video = $button.data('video'),
				url = $button.data('url');
			$('#video-player').html(video.content);
			if(url!='') {
				$('#video-link').html('<a href="'+url+'" class="btn btn-sm btn-danger">Xem chi tiết</a>');
			}
			if(video.type=='youtube') {
				let h,w;
				if($(window).width()>768) {
					h = $(window).innerHeight()-100;
					w = 16*h/9;
				} else {
					w = $(window).width() - 24;
					h = 9*w/16;
				}
				$('#video-player').addClass('ratio ratio-16x9').height(h).width(w);
			} else {
				$('#video-player').removeClass('ratio ratio-16x9').removeAttr('style');
			}
		}).on('shown.bs.modal', function (event) {
			$(this).css('display', 'flex');
			if($(this).find('video').length>0) {
				$(this).find('video').get(0).play();
			}
		}).on('hidden.bs.modal', function (e) {
			$('#video-player').html('<div class="ratio ratio-16x9"></div>');
		});

		function matchKWS(params, data) {
			// If there are no search terms, return all of the data
			if ($.trim(params.term) === '') {
				return null;
			}

			// Do not display the item if there is no 'text' property
			if (typeof data.text === 'undefined') {
				return null;
			}

			// `params.term` should be the term that is used for searching
			// `data.text` is the text that is displayed for the data object
			if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
				var modifiedData = $.extend({}, data, true);

				// You can return modified objects from here
				// This includes matching the `children` how you want in nested data sets
				return modifiedData;
			}

			// Return `null` if the term should not be displayed
			return null;
		}
		
		$('#keyword-search').select2({
			// matcher: matchKWS,
			// data: theme.kws,
			ajax: {
				url: theme.ajax_url,
				dataType: 'json',
				data: function (params) {
					var query = {
						search: params.term,
						action: 'get_seo_post'
					}
					return query;
				},
				delay: 500,
				cache: true
			},
			dropdownParent: $('#modal-keyword-search .modal-body'),
			allowClear: false,
			placeholder: 'Tìm công trình theo tỉnh',
			dropdownCssClass: 'kws-dropdown',
			language: {
				inputTooLong: function(n) {
					return "Vui lòng xóa bớt ký tự";
				},
				inputTooShort: function(n) {
					return "Vui lòng nhập thêm ký tự";
				},
				loadingMore: function() {
					return "Đang lấy thêm kết quả…";
				},
				maximumSelected: function(n) {
					return "Chỉ có thể chọn giới hạn lựa chọn";
				},
				noResults: function() {
					return "Không tìm thấy kết quả";
				},
				searching: function() {
					return "Đang tìm…";
				},
				removeAllItems: function() {
					return "Xóa tất cả các mục";
				}
			}
		});
		
		$('#keyword-search').on('change', function(e) {
			location.href = $(this).val();
		});

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
								$('.estimate-'+formData.get('estimate_contractor')+' .zalo-link').html(response['zalo']);
								$('.estimate-'+formData.get('estimate_contractor')+' .attachment-download').html(response['attachment']);
								$('.estimate-'+formData.get('estimate_contractor')+' .contractor-info').html(response['info']);
								// $('.estimate-'+formData.get('estimate_contractor')+' .estimate-require-content').html(response['require_content']);
								// let popover = $('.estimate-'+formData.get('estimate_contractor')+' .estimate-require-content').find('[data-bs-toggle="popover"]');
								// if(popover.length>0){ 
								// 	new bootstrap.Popover(popover.get(0));
								// }
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

		$('.estimate-contractor-hide').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				contractor = $this.data('contractor'),
				contractor_title = $this.data('contractorTitle'),
				$estimate = $this.closest('.estimate-item');

			if(confirm('Ẩn nhà thầu "'+contractor_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'estimate_contractor_hide', client: client, contractor: contractor},
					beforeSend: function() {

					},
					success: function(response) {
						if(response) {
							$estimate.addClass('hide');
						}
					}
				});
			}
		});

		// estimate construction
		$('#edit-estimate-construction').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,client = $button.data('client')
				,contractor = $button.data('contractor')
				,contractor_title = $button.data('contractor-title')
				;

			$('#edit-estimate-construction-label').text(contractor_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_estimate_construction_form',
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

			$('#edit-estimate-construction-label').text('');
			$body.text('');
		});

		$(document).on('submit', '#frm-edit-estimate-construction', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-estimate-construction-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_estimate_construction',
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
							url: theme.ajax_url+'?action=get_estimate_construction_info',
							type: 'GET',
							cache: false,
							dataType: 'json',
							data: {client:formData.get('estimate_construction_client'), contractor:formData.get('estimate_construction_contractor')},
							success: function(response) {
								$('.estimate-'+formData.get('estimate_construction_contractor')+' .zalo-link').html(response['zalo']);
								$('.estimate-'+formData.get('estimate_construction_contractor')+' .attachment-download').html(response['attachment']);
								$('.estimate-'+formData.get('estimate_construction_contractor')+' .contractor-info').html(response['info']);
								$('.estimate-'+formData.get('estimate_construction_contractor')+' .estimate-required').html(response['required']);
								$('.estimate-'+formData.get('estimate_construction_contractor')+' .estimate-received').html(response['received']);
								$('.estimate-'+formData.get('estimate_construction_contractor')+' .estimate-completed').html(response['completed']);
								$('.estimate-'+formData.get('estimate_construction_contractor')+' .estimate-sent').html(response['sent']);
								$('.estimate-'+formData.get('estimate_construction_contractor')+' .estimate-quote').html(response['quote']);
								$('#edit-estimate-construction .btn-close').trigger('click');
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

		$(document).on('input', '#estimate_construction_attachment', function() {
			let $input = $(this);
			$input.closest('[for="estimate_construction_attachment"]').find('.form-control').text($input.val().split('\\').pop());
		});

		$(document).on('click', '#estimate_construction_remove_attachment', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#estimate_construction_attachment_id').val('');
			$this.closest('.input-group').remove();
		});

		$('.estimate-contractor-construction-hide').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				contractor = $this.data('contractor'),
				contractor_title = $this.data('contractorTitle'),
				$estimate = $this.closest('.estimate-item');

			if(confirm('Ẩn nhà thầu "'+contractor_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'estimate_contractor_construction_hide', client: client, contractor: contractor},
					beforeSend: function() {

					},
					success: function(response) {
						if(response) {
							$estimate.addClass('hide');
						}
					}
				});
			}
		});

		// estimate furniture
		$('#edit-estimate-furniture').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,client = $button.data('client')
				,contractor = $button.data('contractor')
				,contractor_title = $button.data('contractor-title')
				;

			$('#edit-estimate-furniture-label').text(contractor_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_estimate_furniture_form',
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

			$('#edit-estimate-furniture-label').text('');
			$body.text('');
		});

		$(document).on('submit', '#frm-edit-estimate-furniture', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-estimate-furniture-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_estimate_furniture',
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
							url: theme.ajax_url+'?action=get_estimate_furniture_info',
							type: 'GET',
							cache: false,
							dataType: 'json',
							data: {client:formData.get('estimate_furniture_client'), contractor:formData.get('estimate_furniture_contractor')},
							success: function(response) {
								$('.estimate-'+formData.get('estimate_furniture_contractor')+' .zalo-link').html(response['zalo']);
								$('.estimate-'+formData.get('estimate_furniture_contractor')+' .attachment-download').html(response['attachment']);
								$('.estimate-'+formData.get('estimate_furniture_contractor')+' .contractor-info').html(response['info']);
								$('.estimate-'+formData.get('estimate_furniture_contractor')+' .estimate-required').html(response['required']);
								$('.estimate-'+formData.get('estimate_furniture_contractor')+' .estimate-received').html(response['received']);
								$('.estimate-'+formData.get('estimate_furniture_contractor')+' .estimate-completed').html(response['completed']);
								$('.estimate-'+formData.get('estimate_furniture_contractor')+' .estimate-sent').html(response['sent']);
								$('.estimate-'+formData.get('estimate_furniture_contractor')+' .estimate-quote').html(response['quote']);
								$('#edit-estimate-furniture .btn-close').trigger('click');
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

		$(document).on('input', '#estimate_furniture_draw', function() {
			let $input = $(this);
			$input.closest('[for="estimate_furniture_draw"]').find('.form-control').text($input.val().split('\\').pop());
		});
		$(document).on('click', '#estimate_furniture_remove_draw', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#estimate_furniture_draw_id').val('');
			$this.closest('.input-group').remove();
		});

		$(document).on('input', '#estimate_furniture_slideshow', function() {
			let $input = $(this);
			$input.closest('[for="estimate_furniture_slideshow"]').find('.form-control').text($input.val().split('\\').pop());
		});
		$(document).on('click', '#estimate_furniture_remove_slideshow', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#estimate_furniture_slideshow_id').val('');
			$this.closest('.input-group').remove();
		});

		$(document).on('input', '#estimate_furniture_attachment', function() {
			let $input = $(this);
			$input.closest('[for="estimate_furniture_attachment"]').find('.form-control').text($input.val().split('\\').pop());
		});
		$(document).on('click', '#estimate_furniture_remove_attachment', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#estimate_furniture_attachment_id').val('');
			$this.closest('.input-group').remove();
		});

		$('.estimate-contractor-furniture-hide').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				contractor = $this.data('contractor'),
				contractor_title = $this.data('contractorTitle'),
				$estimate = $this.closest('.estimate-item');

			if(confirm('Ẩn nhà thầu "'+contractor_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'estimate_contractor_furniture_hide', client: client, contractor: contractor},
					beforeSend: function() {

					},
					success: function(response) {
						if(response) {
							$estimate.addClass('hide');
						}
					}
				});
			}
		});

		// estimate customer
		$('#edit-estimate-customer').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,client = $button.data('client')
				,contractor = $button.data('contractor')
				,contractor_title = $button.data('contractor-title')
				;

			$('#edit-estimate-customer-label').text(contractor_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_estimate_customer_form',
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

			$('#edit-estimate-customer-label').text('');
			$body.text('');
		});

		$(document).on('submit', '#frm-edit-estimate-customer', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-estimate-customer-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_estimate_customer',
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
							url: theme.ajax_url+'?action=get_estimate_customer_info',
							type: 'GET',
							cache: false,
							dataType: 'json',
							data: {client:formData.get('estimate_client'), contractor:formData.get('estimate_contractor')},
							success: function(response) {
								$('.estimate-'+formData.get('estimate_contractor')+' .zalo-link').html(response['zalo']);
								$('.estimate-'+formData.get('estimate_contractor')+' .contractor-info').html(response['info']);
								$('#edit-estimate-customer .btn-close').trigger('click');
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

		$(document).on('click', '#estimate_customer_remove_attachment', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#estimate_attachment_id').val('');
			$this.closest('.input-group').remove();
		});
		$(document).on('input', '#estimate_attachment', function() {
			let $input = $(this);
			$input.closest('[for="estimate_attachment"]').find('.form-control').text($input.val().split('\\').pop());
		});

		// estimate manage
		$('#edit-estimate-manage').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,client = $button.data('client')
				,estimate = $button.data('estimate')
				,estimate_title = $button.data('estimate-title')
				;

			$('#edit-estimate-manage-label').text(estimate_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_estimate_manage_form',
					client:client,
					estimate:estimate
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

			$('#edit-estimate-manage-label').text('');
			$body.text('');
		});

		$(document).on('submit', '#frm-edit-estimate-manage', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-estimate-manage-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_estimate_manage',
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
							url: theme.ajax_url+'?action=get_estimate_manage_info',
							type: 'GET',
							dataType: 'json',
							cache: false,
							data: {estimate_client:formData.get('estimate_client'), estimate_id:formData.get('estimate_id')},
							success: function(response) {
								$('.estimate-'+formData.get('estimate_id')+' .estimate-info').html(response['info']);
								$('.estimate-'+formData.get('estimate_id')+' .zalo-link').html(response['zalo']);
								$('.estimate-'+formData.get('estimate_id')+' .file-download').html(response['file']);
								$('.estimate-'+formData.get('estimate_id')+' .estimate-required').html(response['required']);
								$('.estimate-'+formData.get('estimate_id')+' .estimate-received').html(response['received']);
								$('.estimate-'+formData.get('estimate_id')+' .estimate-completed').html(response['completed']);
								$('.estimate-'+formData.get('estimate_id')+' .estimate-sent').html(response['sent']);
								$('.estimate-'+formData.get('estimate_id')+' .estimate-quote').html(response['quote']);
								$('#edit-estimate-manage .btn-close').trigger('click');
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

		$(document).on('click', '#estimate_remove_file', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#estimate_file_id').val('');
			$this.closest('.input-group').remove();
		});
		$(document).on('input', '#estimate_file', function() {
			let $input = $(this);
			$input.closest('[for="estimate_file"]').find('.form-control').text($input.val().split('\\').pop());
		});

		// econstruction
		$('#edit-econstruction').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,client = $button.data('client')
				,econstruction = $button.data('econstruction')
				,econstruction_title = $button.data('econstruction-title')
				;

			$('#edit-econstruction-label').text(econstruction_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_econstruction_form',
					client:client,
					econstruction:econstruction
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

			$('#edit-econstruction-label').text('');
			$body.text('');
		});

		$(document).on('submit', '#frm-edit-econstruction', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-econstruction-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_econstruction',
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
							url: theme.ajax_url+'?action=get_econstruction_info',
							type: 'GET',
							dataType: 'json',
							cache: false,
							data: {client:formData.get('client'), econstruction:formData.get('econstruction')},
							success: function(response) {
								$('.econstruction-'+formData.get('econstruction')+' .econstruction-info').html(response['info']);
								$('.econstruction-'+formData.get('econstruction')+' .zalo-link').html(response['zalo']);
								$('.econstruction-'+formData.get('econstruction')+' .file-download').html(response['file']);
								$('.econstruction-'+formData.get('econstruction')+' .econstruction-required').html(response['required']);
								$('.econstruction-'+formData.get('econstruction')+' .econstruction-received').html(response['received']);
								$('.econstruction-'+formData.get('econstruction')+' .econstruction-completed').html(response['completed']);
								$('.econstruction-'+formData.get('econstruction')+' .econstruction-sent').html(response['sent']);
								$('.econstruction-'+formData.get('econstruction')+' .econstruction-quote').html(response['quote']);
								$('#edit-econstruction .btn-close').trigger('click');
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

		$(document).on('click', '#econstruction_remove_file', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#econstruction_file_id').val('');
			$this.closest('.input-group').remove();
		});
		$(document).on('input', '#econstruction_file', function() {
			let $input = $(this);
			$input.closest('[for="econstruction_file"]').find('.form-control').text($input.val().split('\\').pop());
		});

		// efurniture
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
		});

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
						$.ajax({
							url: theme.ajax_url+'?action=get_efurniture_info',
							type: 'GET',
							dataType: 'json',
							cache: false,
							data: {client:formData.get('client'), efurniture:formData.get('efurniture')},
							success: function(response) {
								$('.efurniture-'+formData.get('efurniture')+' .efurniture-info').html(response['info']);
								$('.efurniture-'+formData.get('efurniture')+' .zalo-link').html(response['zalo']);
								$('.efurniture-'+formData.get('efurniture')+' .file-download').html(response['file']);
								$('.efurniture-'+formData.get('efurniture')+' .efurniture-required').html(response['required']);
								$('.efurniture-'+formData.get('efurniture')+' .efurniture-received').html(response['received']);
								$('.efurniture-'+formData.get('efurniture')+' .efurniture-completed').html(response['completed']);
								$('.efurniture-'+formData.get('efurniture')+' .efurniture-sent').html(response['sent']);
								$('.efurniture-'+formData.get('efurniture')+' .efurniture-quote').html(response['quote']);
								$('#edit-efurniture .btn-close').trigger('click');
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

		$(document).on('click', '#efurniture_remove_file', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#efurniture_file_id').val('');
			$this.closest('.input-group').remove();
		});
		$(document).on('input', '#efurniture_file', function() {
			let $input = $(this);
			$input.closest('[for="efurniture_file"]').find('.form-control').text($input.val().split('\\').pop());
		});

		$(document).on('click', '#efurniture_remove_slideshow', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#efurniture_slideshow_id').val('');
			$this.closest('.input-group').remove();
		});
		$(document).on('input', '#efurniture_slideshow', function() {
			let $input = $(this);
			$input.closest('[for="efurniture_slideshow"]').find('.form-control').text($input.val().split('\\').pop());
		});

		$(document).on('click', '#efurniture_remove_draw', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#efurniture_draw_id').val('');
			$this.closest('.input-group').remove();
		});
		$(document).on('input', '#efurniture_draw', function() {
			let $input = $(this);
			$input.closest('[for="efurniture_draw"]').find('.form-control').text($input.val().split('\\').pop());
		});


		// edit partner
		$('#edit-partner').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,client = $button.data('client')
				,partner = $button.data('partner')
				,partner_title = $button.data('partner-title')
				;

			$('#edit-partner-label').text(partner_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_partner_form',
					client:client,
					partner:partner
				},
				beforeSend: function(xhr) {
					$body.text('Đang tải..');
				},
				success: function(response) {
					$body.html(response);
					//$body.find('#partner_value').inputNumber({'negative':false});
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

			$('#edit-partner-label').text('');
			$body.text('');
		});

		$(document).on('submit', '#frm-edit-partner', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-partner-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_partner',
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
							url: theme.ajax_url+'?action=get_partner_info',
							type: 'GET',
							cache: false,
							dataType: 'json',
							data: {client:formData.get('partner_client'), partner:formData.get('partner_id')},
							success: function(response) {
								$('.partner-'+formData.get('partner_id')+' .zalo-link').html(response['zalo']);
								$('.partner-'+formData.get('partner_id')+' .partner-info').html(response['info']);
								$('#edit-partner .btn-close').trigger('click');
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

		$(document).on('click', '#partner_remove_attachment', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#partner_attachment_id').val('');
			$this.closest('.input-group').remove();
		});

		$(document).on('input', '#partner_attachment', function() {
			let $input = $(this);
			$input.closest('[for="partner_attachment"]').find('.form-control').text($input.val().split('\\').pop());
		});

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

		// edit contract
		$('#edit-contract').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,client = $button.data('client')
				,contract = $button.data('contract')
				,contract_title = $button.data('contract-title')
				;

			$('#edit-contract-label').text(contract_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_contract_form',
					client:client,
					contract:contract
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

			$('#edit-contract-label').text('');
			$body.text('');
		});

		$(document).on('submit', '#frm-edit-contract', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-contract-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_contract',
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
							url: theme.ajax_url+'?action=get_contract_info',
							type: 'GET',
							cache: false,
							dataType: 'json',
							data: {client:formData.get('contract_client'), contract:formData.get('contract_id')},
							success: function(response) {
								$('.contract-'+formData.get('contract_id')+' .zalo-link').html(response['zalo']);
								$('.contract-'+formData.get('contract_id')+' .contract-info').html(response['info']);
								$('.contract-'+formData.get('contract_id')+' .contract-required').html(response['required']);
								$('.contract-'+formData.get('contract_id')+' .contract-created').html(response['created']);
								$('.contract-'+formData.get('contract_id')+' .contract-completed').html(response['completed']);
								$('.contract-'+formData.get('contract_id')+' .contract-sent').html(response['sent']);
								$('.contract-'+formData.get('contract_id')+' .contract-signed').html(response['signed']);
								$('#edit-contract .btn-close').trigger('click');
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

		$(document).on('click', '#contract_remove_attachment', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#contract_attachment_id').val('');
			$this.closest('.input-group').remove();
		});

		$(document).on('input', '#contract_attachment', function() {
			let $input = $(this);
			$input.closest('[for="contract_attachment"]').find('.form-control').text($input.val().split('\\').pop());
		});

		// edit gzalo
		$('#edit-gzalo').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,client = $button.data('client')
				,gzalo = $button.data('gzalo')
				,gzalo_title = $button.data('gzalo-title')
				;

			$('#edit-gzalo-label').text(gzalo_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_gzalo_form',
					client:client,
					gzalo:gzalo
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

			$('#edit-gzalo-label').text('');
			$body.text('');
		});

		$(document).on('submit', '#frm-edit-gzalo', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-gzalo-response')
				;
			$button.prop('disabled', true);

			$.ajax({
				url: theme.ajax_url+'?action=update_gzalo',
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
							url: theme.ajax_url+'?action=get_gzalo_info',
							type: 'GET',
							cache: false,
							dataType: 'json',
							data: {client:formData.get('gzalo_client'), gzalo:formData.get('gzalo_id')},
							success: function(response) {
								$('.gzalo-'+formData.get('gzalo_id')+' .zalo-link').html(response['zalo']);
								$('.gzalo-'+formData.get('gzalo_id')+' .gzalo-info').html(response['info']);
								$('.gzalo-'+formData.get('gzalo_id')+' .gzalo-required').html(response['required']);
								$('.gzalo-'+formData.get('gzalo_id')+' .gzalo-created').html(response['created']);
								$('.gzalo-'+formData.get('gzalo_id')+' .gzalo-completed').html(response['completed']);
								$('.gzalo-'+formData.get('gzalo_id')+' .gzalo-sent').html(response['sent']);
								$('.gzalo-'+formData.get('gzalo_id')+' .gzalo-signed').html(response['signed']);
								$('#edit-gzalo .btn-close').trigger('click');
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

		$(document).on('click', '#gzalo_remove_attachment', function(e){
			e.preventDefault();
			let $this = $(this);
			$('#gzalo_attachment_id').val('');
			$this.closest('.input-group').remove();
		});

		$(document).on('input', '#gzalo_attachment', function() {
			let $input = $(this);
			$input.closest('[for="gzalo_attachment"]').find('.form-control').text($input.val().split('\\').pop());
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

		$('.estimate-customer-hide').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				contractor = $this.data('contractor'),
				contractor_title = $this.data('contractorTitle'),
				$estimate = $this.closest('.estimate-item');

			if(confirm('Ẩn nhà thầu "'+contractor_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'estimate_customer_hide', client: client, contractor: contractor},
					beforeSend: function() {

					},
					success: function(response) {
						if(response) {
							$estimate.addClass('hide');
						}
					}
				});
			}
		});

		$('.estimate-manage-hide').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				estimate = $this.data('estimate'),
				estimate_title = $this.data('estimateTitle'),
				$estimate = $this.closest('.estimate-item');

			if(confirm('Ẩn nhà thầu "'+estimate_title+'" ?')) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'estimate_manage_hide', client: client, estimate: estimate},
					beforeSend: function() {

					},
					success: function(response) {
						if(response) {
							$estimate.addClass('hide');
						}
					}
				});
			}
		});

		$('.econstruction-hide').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				econstruction = $this.data('econstruction'),
				econstruction_title = $this.data('econstructionTitle'),
				$econstruction = $this.closest('.econstruction-item');

			if(confirm(econstruction_title)) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'econstruction_hide', client: client, econstruction: econstruction},
					beforeSend: function() {

					},
					success: function(response) {
						if(response) {
							$econstruction.addClass('hide');
						}
					}
				});
			}
		});

		$('.efurniture-hide').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				efurniture = $this.data('efurniture'),
				efurniture_title = $this.data('efurnitureTitle'),
				$efurniture = $this.closest('.efurniture-item');

			if(confirm(efurniture_title)) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'efurniture_hide', client: client, efurniture: efurniture},
					beforeSend: function() {

					},
					success: function(response) {
						if(response) {
							$efurniture.addClass('hide');
						}
					}
				});
			}
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

		$('.contract-hide').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				contract = $this.data('contract'),
				contract_title = $this.data('contractTitle'),
				$contract = $this.closest('.contract-item');

			if(confirm(contract_title)) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'contract_hide', client: client, contract: contract},
					beforeSend: function() {

					},
					success: function(response) {
						if(response) {
							$contract.addClass('hide');
						}
					}
				});
			}
		});

		$('.gzalo-hide').on('click', function(e){
			let $this = $(this),
				client = $this.data('client'),
				gzalo = $this.data('gzalo'),
				gzalo_title = $this.data('gzaloTitle'),
				$gzalo = $this.closest('.gzalo-item');

			if(confirm(gzalo_title)) {
				$.ajax({
					url: theme.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {nonce: theme.nonce, action: 'gzalo_hide', client: client, gzalo: gzalo},
					beforeSend: function() {

					},
					success: function(response) {
						if(response) {
							$gzalo.addClass('hide');
						}
					}
				});
			}
		});

		$('.client-heading.position-sticky').each(function(index, el){
			let $el = $(el);
			let stickyObserver = new IntersectionObserver(([entry]) => {
				//console.log(entry.intersectionRatio);
				if (entry.intersectionRatio < 1) {
					$el.removeClass('is-stuck');
				} else {
					$el.addClass('is-stuck');
				}
			}, {
				threshold: [1],
				root: document.getElementById('site-body'),
				rootMargin: '-2px 0px 0px 0px'
			});

			stickyObserver.observe(el);
		});

		$('input.progress-checker').on('change', function(e){
			let $this = $(this);
			
			$this.closest('.filter-progress-item').siblings().find('.progress-checker').prop('checked', false);
			$this.closest('form').submit();
		});

		if($('#estimate-filter-form').length) {
			let none = 0, required = 0, received = 0, completed = 0, sent = 0, quote = 0;
			$('#estimate-filter-form').find('.estimate-item:not(.hide)').each(function(i, el){
				let $el = $(el), isNone = true;
					
				if($el.find('.estimate-required').hasClass('on')) {
					required += 1;
					isNone = false;
				}
				if($el.find('.estimate-received').hasClass('on')) {
					received += 1;
					isNone = false;
				}
				if($el.find('.estimate-completed').hasClass('on')) {
					completed += 1;
					isNone = false;
				}
				if($el.find('.estimate-sent').hasClass('on')) {
					sent += 1;
					isNone = false;
				}
				if($el.find('.estimate-quote').hasClass('on')) {
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

		if($('#gzalo-filter-form').length) {
			let none = 0, required = 0, created = 0, completed = 0, sent = 0, signed = 0;
			$('#gzalo-filter-form').find('.gzalo-item:not(.hide)').each(function(i, el){
				let $el = $(el), isNone = true;
					
				if($el.find('.gzalo-required').hasClass('on')) {
					required += 1;
					isNone = false;
				}
				if($el.find('.gzalo-created').hasClass('on')) {
					created += 1;
					isNone = false;
				}
				if($el.find('.gzalo-completed').hasClass('on')) {
					completed += 1;
					isNone = false;
				}
				if($el.find('.gzalo-sent').hasClass('on')) {
					sent += 1;
					isNone = false;
				}
				if($el.find('.gzalo-signed').hasClass('on')) {
					signed += 1;
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
			$('label[for="progress-signed"] span').text(signed);
		}

		if($('#contract-filter-form').length) {
			let none = 0, required = 0, created = 0, completed = 0, sent = 0, signed = 0;
			$('#contract-filter-form').find('.contract-item:not(.hide)').each(function(i, el){
				let $el = $(el), isNone = true;
					
				if($el.find('.contract-required').hasClass('on')) {
					required += 1;
					isNone = false;
				}
				if($el.find('.contract-created').hasClass('on')) {
					created += 1;
					isNone = false;
				}
				if($el.find('.contract-completed').hasClass('on')) {
					completed += 1;
					isNone = false;
				}
				if($el.find('.contract-sent').hasClass('on')) {
					sent += 1;
					isNone = false;
				}
				if($el.find('.contract-signed').hasClass('on')) {
					signed += 1;
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
			$('label[for="progress-signed"] span').text(signed);
		}

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

		if($('#econstruction-filter-form').length) {
			let none = 0, required = 0, received = 0, completed = 0, sent = 0, quote = 0;
			$('#econstruction-filter-form').find('.econstruction-item:not(.hide)').each(function(i, el){
				let $el = $(el), isNone = true;
					
				if($el.find('.econstruction-required').hasClass('on')) {
					required += 1;
					isNone = false;
				}
				if($el.find('.econstruction-received').hasClass('on')) {
					received += 1;
					isNone = false;
				}
				if($el.find('.econstruction-completed').hasClass('on')) {
					completed += 1;
					isNone = false;
				}
				if($el.find('.econstruction-sent').hasClass('on')) {
					sent += 1;
					isNone = false;
				}
				if($el.find('.econstruction-quote').hasClass('on')) {
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

		var lightbox = new PhotoSwipeLightbox({
			gallery: '.pswp-gallery',
			children: 'a',
			pswpModule: PhotoSwipe 
		});
		lightbox.init();

		$('body').on('click', function (e) {
			$('[data-bs-toggle="popover"]').each(function () {
				// hide any open popovers when the anywhere else in the body is clicked
				if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
					$(this).popover('hide');
				}
			});
		});

	});// jQuery
	

}); // DOMContentLoaded