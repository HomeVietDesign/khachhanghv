document.addEventListener('DOMContentLoaded', function(e){
	jQuery(function($){

		$('#edit-constructions').on('show.bs.modal', function (event) {
			let $modal = $(this),
				$button = $(event.relatedTarget)
				,$body = $modal.find('.modal-body')
				,construction = $button.data('construction')
				,constructions_title = $button.data('constructions-title')
				;

			$('#edit-constructions-label').text(constructions_title);

			$.ajax({
				url: theme.ajax_url,
				type: 'GET',
				data: {
					action: 'get_edit_construction_form',
					construction:construction
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

			$('#edit-constructions-label').text('');
			$body.text('');

		});

		function get_construction_info(construction) {
			$.ajax({
				url: theme.ajax_url+'?action=get_construction_info',
				type: 'GET',
				dataType: 'json',
				cache: false,
				data: {construction:construction},
				success: function(response) {
					console.log(response);
					
					$('.construction-'+construction+' .estimate-link').html(response['construction_estimate_link']);
					$('.construction-'+construction+' .dates').html(response['construction_dates']);
					$('#edit-constructions .btn-close').trigger('click');
				}
			});
		}

		$(document).on('submit', '#frm-edit-construction', function(e){
			e.preventDefault();
			let $form = $(this)
				,formData = new FormData($form[0])
				,$button = $form.find('[type="submit"]')
				,$response = $('#edit-construction-response')
				;
			$button.prop('disabled', true);
			
			$.ajax({
				url: theme.ajax_url+'?action=update_construction',
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
						get_construction_info(formData.get('construction'));
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