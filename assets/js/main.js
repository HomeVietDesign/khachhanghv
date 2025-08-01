window.addEventListener('DOMContentLoaded', function(){
	
	// console.log(document.referrer);
	// console.log(window.atob(getCookie('_ref')).split(','));

	const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
	const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));

	const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
	const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

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

		$(document).on('click', 'a.zalo-copy', function(e){
			e.preventDefault();

			let $this = $(this), $wrap = $this.closest('.popover-body'), text = $wrap.find('.copy-text').text();

			CopyToClipboard(text.trim());

			//Gỡ tooltip cũ (nếu có)
			if (bootstrap.Tooltip.getInstance(this)) {
				bootstrap.Tooltip.getInstance(this).dispose();
			}

			$this.attr('title', 'Đã sao chép!');

			// Tạo tooltip
			const tooltip = new bootstrap.Tooltip(this, {
				trigger: 'manual',
				placement: 'top',
			});

			tooltip.show();

			// Ẩn sau 2 giây (tuỳ chỉnh nếu cần)
			setTimeout(() => tooltip.hide(), 2000);
		});

	});// jQuery
	

}); // DOMContentLoaded