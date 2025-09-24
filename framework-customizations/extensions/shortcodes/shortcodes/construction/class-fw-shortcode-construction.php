<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Construction extends FW_Shortcode
{
	
	public function _init()
	{
		add_action( 'wp_footer', [$this, 'edit_modal'] );
		add_action( 'wp_ajax_get_edit_construction_form', [$this, 'ajax_get_edit_construction_form']);
		add_action( 'wp_ajax_update_construction', [$this, 'ajax_update_construction']);
		add_action( 'wp_ajax_get_construction_info', [$this, 'ajax_get_construction_info']);
	}

	public function ajax_get_construction_info() {

		$construction = isset($_GET['construction'])?absint($_GET['construction']):0;

		$response = [
			'construction_estimate_link' => '',
			'construction_dates' => '',
		];
		
		if($construction) {
			$construction_estimate_link = fw_get_db_post_option($construction, 'construction_estimate_link');
			$_construction_dates = fw_get_db_post_option($construction, 'construction_dates');
			
			$construction_dates = [
				'date1' => [
					'title' => '',
					'date' => ''
				],
				'date2' => [
					'title' => '',
					'date' => ''
				],
				'date3' => [
					'title' => '',
					'date' => ''
				],
				'date4' => [
					'title' => '',
					'date' => ''
				]
			];

			if($_construction_dates) {
				foreach ($_construction_dates as $key => $value) {
					$construction_dates['date'.($key+1)] = [
						'title' => $value['title'],
						'date' => $value['date']
					];
				}
			}

			$response['construction_estimate_link'] = ($construction_estimate_link!='')?'<a class="btn btn-sm btn-primary" href="'.esc_url($construction_estimate_link).'" target="_blank">Dự toán</a>':'';
			
			ob_start();
			$i = 0;
			foreach ($construction_dates as $key => $value) {
				$i++;
				?>
				<div class="col construction-date-<?=$i?> position-relative">
				<?php
				if($construction_dates[$key]['date']!='') {
					$date = date('d/m/y', strtotime($construction_dates[$key]['date']));
					?>
					<span class="w-100 d-flex justify-content-center align-items-center text-yellow" title="<?=esc_attr($construction_dates[$key]['title'])?>"><?=esc_html($date)?></span>
					<?php
				}
				?>
				</div>
				<?php
			}
			$response['construction_dates'] = ob_get_clean();
			
		}
		
		wp_send_json($response);
	}

	public function ajax_update_construction() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		if(current_user_can('construction_edit') && check_ajax_referer( 'edit-construction', 'nonce', false )) {
			$construction_id = isset($_POST['construction'])?absint($_POST['construction']):0;

			$construction_estimate_link = isset($_POST['construction_estimate_link'])?sanitize_url($_POST['construction_estimate_link']):'';
			
			$construction_dates = isset($_POST['construction_dates']) ? $_POST['construction_dates'] : [];
			
			if($construction_id) {

				fw_set_db_post_option($construction_id, 'construction_estimate_link', $construction_estimate_link);
				fw_set_db_post_option($construction_id, 'construction_dates', $construction_dates);

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_get_edit_construction_form() {

		$construction = isset($_GET['construction'])?absint($_GET['construction']):0;

		if($construction) {
			//$construction_content = fw_get_db_post_option($construction, 'construction_content');
			//$construction_stage_options = fw_get_db_post_option($construction, 'construction_stage_options');
			$construction_estimate_link = fw_get_db_post_option($construction, 'construction_estimate_link');
			$_construction_dates = fw_get_db_post_option($construction, 'construction_dates');
			
			$construction_dates = [
				'date1' => [
					'title' => '',
					'date' => ''
				],
				'date2' => [
					'title' => '',
					'date' => ''
				],
				'date3' => [
					'title' => '',
					'date' => ''
				],
				'date4' => [
					'title' => '',
					'date' => ''
				]
			];

			if($_construction_dates) {
				foreach ($_construction_dates as $key => $value) {
					$construction_dates['date'.($key+1)] = [
						'title' => $value['title'],
						'date' => $value['date']
					];
				}
			}

			?>
			<form id="frm-edit-construction" method="POST" action="" enctype="multipart/form-data">
				<input type="hidden" id="construction" name="construction" value="<?=$construction?>">
				<?php wp_nonce_field( 'edit-construction', 'nonce' ); ?>
				<div id="edit-construction-response"></div>
				<?php
				$i=0;
				foreach ($construction_dates as $key => $value) {
					?>
					<div class="mb-3">
						<input class="form-control mb-2" type="text" value="<?=esc_attr($value['title'])?>" name="construction_dates[<?=$i?>][title]" id="construction_<?=esc_attr($key)?>_label" placeholder="Ghi chú ngày <?=($i+1)?>">
						<input class="form-control" type="date" value="<?php echo ($value['date']!='')?esc_html(date('Y-m-d', strtotime($value['date']))):''; ?>" name="construction_dates[<?=$i?>][date]" id="construction_<?=esc_attr($key)?>">
					</div>
					<?php
					$i++;
				}
				?>
				<div class="mb-3">
					Link dự toán
					<input type="text" id="construction_estimate_link" name="construction_estimate_link" placeholder="" class="form-control" value="<?php echo ($construction_estimate_link!='')?esc_url($construction_estimate_link):''; ?>">
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-construction-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-constructions" tabindex="-1" role="dialog" aria-labelledby="edit-constructions-label">
			<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-constructions-label"></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
