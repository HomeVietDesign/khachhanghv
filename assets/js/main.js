window.addEventListener('DOMContentLoaded', function(){
	
	// console.log(document.referrer);
	// console.log(window.atob(getCookie('_ref')).split(','));

	const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
	const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));

	const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
	const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

	jQuery(function($){

		let $ancho = $('#ancho');
		if($ancho.length>0) {
			let $dropdown_menu = $ancho.find('ul.dropdown-menu')
			$('.accordion-header').each(function(index, el){
				let $el = $(el);
				$dropdown_menu.append('<li><a class="dropdown-item" href="javascript:void(0)" data-to="'+($el.attr('id'))+'">'+($el.text())+'</a></li>');
			});
		}

		$(document).on('click', '#ancho a.dropdown-item', function(e){
			let $this = $(this);
			let $to = $('#'+$this.data('to'));

			$("html, body").animate({
				scrollTop: $to.offset().top - ($('#wpadminbar').height()+$('#site-header').height()+2*$('.client-heading').height())
			}, 500, 'linear');
	
			return false;
		});

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

			// Ẩn sau 3 giây (tuỳ chỉnh nếu cần)
			setTimeout(() => tooltip.hide(), 3000);
		});

	});// jQuery
	

}); // DOMContentLoaded