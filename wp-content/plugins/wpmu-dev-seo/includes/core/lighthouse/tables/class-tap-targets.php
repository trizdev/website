<?php

namespace SmartCrawl\Lighthouse\Tables;

class Tap_Targets extends Table {
	/**
	 * @var array
	 */
	private $rows = array();
	/**
	 * @var array
	 */
	private $tap_target_screenshots = array();
	/**
	 * @var array
	 */
	private $overlapping_screenshots = array();

	/**
	 * @param $row
	 * @param $tap_target_node_id
	 * @param $overlapping_node_id
	 *
	 * @return void
	 */
	public function add_row( $row, $tap_target_node_id = '', $overlapping_node_id = '' ) {
		$this->rows[]                    = $row;
		$this->tap_target_screenshots[]  = $this->get_screenshot( $tap_target_node_id, 100, 75 );
		$this->overlapping_screenshots[] = $this->get_screenshot( $overlapping_node_id, 100, 75 );
	}

	/**
	 * @return void
	 */
	public function render() {
		if ( empty( $this->rows ) ) {
			return;
		}
		?>
		<table class="sui-table">
			<tr>
				<?php foreach ( $this->get_header() as $head_col ) : ?>
					<th><?php echo wp_kses_post( $head_col ); ?></th>
				<?php endforeach; ?>
			</tr>

			<?php foreach ( $this->rows as $index => $row_details ) : ?>
				<?php
				$row                    = $row_details;
				$tap_target_screenshot  = \smartcrawl_get_array_value( $this->tap_target_screenshots, $index );
				$overlapping_screenshot = \smartcrawl_get_array_value( $this->overlapping_screenshots, $index );
				?>
				<tr>
					<?php foreach ( $row as $col_index => $col ) : ?>
						<td>
							<div style="display: flex; align-items: center;">
								<div style="margin-right: 10px; word-break: break-all;">
									<?php echo esc_html( $col ); ?>
								</div>

								<?php if ( 0 === $col_index ) : ?>
									<?php echo $tap_target_screenshot; ?>
								<?php elseif ( 2 === $col_index ) : ?>
									<?php echo $overlapping_screenshot; ?>
								<?php endif; ?>
							</div>
						</td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</table>
		<?php
	}

	/**
	 * @return array
	 */
	public function get_rows() {
		return $this->rows;
	}
}