document.addEventListener('DOMContentLoaded', function(e){
	jQuery(function($){

		$('#edit-designs').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,design = $button.data('design')
				,designs_title = $button.data('designs-title')
				;

			$('#edit-designs-label').text(designs_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_design_form',
					design:design
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

			$('#edit-designs-label').text('');
			$body.text('');

		});

		function get_design_info(design) {
			$.ajax({
				url: theme.ajax_url+'?action=get_design_info',
				type: 'GET',
				dataType: 'json',
				cache: false,
				data: {design:design},
				success: function(response) {
					console.log(response);
					
					$('.design-'+design+' .estimate-link').html(response['design_estimate_link']);
					$('.design-'+design+' .dates').html(response['design_dates']);
					$('#edit-designs .btn-close').trigger('click');
				}
			});
		}

		$(document).on('submit', '#frm-edit-design', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-design-response')
				;
			$button.prop('disabled', true);
			
			$.ajax({
				url: theme.ajax_url+'?action=update_design',
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
						get_design_info(formData.get('design'));
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
	});
});