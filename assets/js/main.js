window.addEventListener('DOMContentLoaded', function(){
	
	// console.log(document.referrer);
	// console.log(window.atob(getCookie('_ref')).split(','));

	jQuery(function($){

		let debounced_contractor_search = debounce((event) => {
			let kw = $('#contractor-search-input').val().trim(),
				province = parseInt($('#contractor-search-province').val()),
				view = parseInt($('#contractor-search-view').val()),
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
					data:{kw: kw, view: view, province: province},
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
		
		$('.logout-post-password').on('click', function(e){
			e.preventDefault();
			let $this = $(this),
				url = $this.data('url');

			$.ajax({
				url:theme.ajax_url+'?action=url_delete_cache',
				method:'GET',
				data:{url:url},
				beforeSend:function(){
					$this.prop('disabled', true);
				},
				success:function(){
					deleteCookie('wp-postpass_'+$this.data('hash'));
					$this.remove();
					location.href = url;
				}
			});
			
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
		

		if($('body').hasClass('single')) {

			var sync1 = $(".single-gallery .slider");
			var sync2 = $(".single-gallery .navigation-thumbs");

			var thumbnailItemClass = '.owl-item';
			var args = {
				// video:false,
				items:1,
				lazyLoad:true,
				loop:false,
				autoplay:true,
				autoHeight:true,
				autoplayTimeout:3000,
				autoplayHoverPause:true,
				nav: true,
				dots: false
			};
			if(sync1.hasClass('has-video')) {
				args.autoplay = false;
			}
			var slides = sync1.owlCarousel(args).on('changed.owl.carousel', syncPosition);

			function syncPosition(el) {
				$owl_slider = $(this).data('owl.carousel');
				var loop = $owl_slider.options.loop;

				if(loop){
					var count = el.item.count-1;
					var current = Math.round(el.item.index - (el.item.count/2) - .5);
					if(current < 0) {
						current = count;
					}
					if(current > count) {
						current = 0;
					}
				}else{
					var current = el.item.index;
				}

				var owl_thumbnail = sync2.data('owl.carousel');
				var itemClass = "." + owl_thumbnail.options.itemClass;


				var thumbnailCurrentItem = sync2
				.find(itemClass)
				.removeClass("synced")
				.eq(current);

				thumbnailCurrentItem.addClass('synced');

				if (!thumbnailCurrentItem.hasClass('active')) {
					var duration = 300;
					sync2.trigger('to.owl.carousel',[current, duration, true]);
				}   
			}

			var thumbs = sync2.owlCarousel({
				items:4,
				lazyLoad:true,
				loop:false,
				margin:10,
				autoplay:false,
				nav: true,
				dots: false,
				// responsive : {
				// 	0 : {
				// 		items: 2
				// 	},
				// 	768 : {
				// 		items: 4
				// 	}
				// },
				onInitialized: function (e) {
					var thumbnailCurrentItem =  $(e.target).find(thumbnailItemClass).eq(this._current);
					thumbnailCurrentItem.addClass('synced');
				}
			})
			.on('click', thumbnailItemClass, function(e) {
				e.preventDefault();
				var duration = 300;
				var itemIndex =  $(e.target).parents(thumbnailItemClass).index();
				sync1.trigger('to.owl.carousel',[itemIndex, duration, true]);
			}).on("changed.owl.carousel", function (el) {
				var number = el.item.index;
				$owl_slider = sync1.data('owl.carousel');
				$owl_slider.to(number, 100, true);
			});
		}

		$('a[href$="#"]').on('click', function(e){
			e.preventDefault();
			return false;
		});
	
		// xử lý sub menu
		$('#main-nav a.toggle-sub-menu').on('click', function(e){
			e.preventDefault();
			e.stopPropagation();
			let $this = $(this);
			
			$this.parent('li').siblings().find('ul.sub-menu').removeClass('open');

			let sub = $this.next('ul.sub-menu');

			sub.toggleClass('open');

		});

		$('body').on('click', function(e){
			$('#main-nav ul.sub-menu').removeClass('open');
		});

		let pmsr = $('.posts-masonry,.list-media');
		pmsr.imagesLoaded(function(){
			pmsr.isotope();
			pmsr.isotope('layout');
		});
		
		$('.posts-masonry-loadmore-button').on('click', function(e){
			let $this = $(this),
					$container = $this.closest('.posts-masonry-section'),
					$msr = $container.find('.posts-masonry'),
					cat = parseInt($this.data('cat')),
					location = parseInt($this.data('location')),
					catexc = $this.data('catexc'),
					pages = parseInt($this.data('pages')),
					page = parseInt($this.data('page'))+1,
					per = parseInt($this.data('per')),
					exclude = parseInt($this.data('exclude')),
					btn_text = $this.text();
				//console.log(catexc);
			$.ajax({
				url:theme.ajax_url+'?action=posts_masonry_loadmore',
				method:'GET',
				data:{cat:cat, local:location, catexc:catexc, page:page, per:per, ex:exclude},
				beforeSend:function(){
					$this.text('Đang tải...');
					if(page>=pages) {
						$this.prop('disabled', true);
					}
				},
				success:function(response){
					let $item = $.parseHTML(response);
					$msr.append($item).isotope('appended', $item);
					$msr.imagesLoaded(function(){
						$msr.isotope();
						$msr.isotope('layout');
					});
					
					$container.find('.loaded-page').text(page);
					$container.find('.loaded-bar').css('width',(100*page/pages)+'%');
					$this.data('page',page);
					$this.text(btn_text);
					if(page>=pages) {
						$this.remove();
					}
				}
			});

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
					$body.find('#estimate_value').inputNumber({'negative':false});
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
								$('.estimate-'+formData.get('estimate_contractor')+' .contractor-info').html(response['info']);
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
			$this.prev('span').html('');
			$('#estimate_attachment_id').val('');
			$this.remove();
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
					$body.find('#estimate_client_value').inputNumber({'negative':false});
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
				data: $form.serialize(),
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
					$body.find('#partner_value').inputNumber({'negative':false});
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
			$this.prev('span').html('');
			$('#partner_attachment_id').val('');
			$this.remove();
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
				,document = $button.data('document')
				,document_title = $button.data('document-title')
				;

			$('#edit-document-label').text(document_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_document_form',
					client:client,
					document:document
				},
				beforeSend: function(xhr) {
					$body.text('Đang tải..');
				},
				success: function(response) {
					$body.html(response);
					$body.find('#document_value').inputNumber({'negative':false});
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
			$this.prev('span').html('');
			$('#document_attachment_id').val('');
			$this.remove();
		});

		$(document).on('input', '#document_attachment', function() {
			let $input = $(this);
			$input.closest('[for="document_attachment"]').find('.form-control').text($input.val().split('\\').pop());
		});

	});// jQuery
	

}); // DOMContentLoaded