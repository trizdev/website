<?php

namespace SmartCrawl\Lighthouse\Checks;

use SmartCrawl\Lighthouse\Tables\Table;
use SmartCrawl\Simple_Renderer;

class Image_Alt extends Check {
	const ID = 'image-alt';

	/**
	 * @return void
	 */
	public function prepare() {
		$this->set_success_title( esc_html__( 'Image elements have [alt] attributes', 'wds' ) );
		$this->set_failure_title( esc_html__( 'Image elements do not have [alt] attributes', 'wds' ) );
		$this->set_success_description( $this->format_success_description() );
		$this->set_failure_description( $this->format_failure_description() );
		$this->set_copy_description( $this->format_copy_description() );
	}

	/**
	 * @return void
	 */
	private function print_common_description() {
		?>
		<div class="wds-lh-section">
			<strong><?php esc_html_e( 'Overview', 'wds' ); ?></strong>
			<p>
				<?php esc_html_e( 'Informative elements should aim for short, descriptive alternate text. Decorative elements can be ignored with an empty alt attribute.' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * @return false|string
	 */
	private function format_success_description() {
		ob_start();
		$this->print_common_description();
		?>
		<div class="wds-lh-section">
			<strong><?php esc_html_e( 'Status', 'wds' ); ?></strong>
			<?php
			Simple_Renderer::render(
				'notice',
				array(
					'class'   => 'sui-notice-success',
					'message' => esc_html__( 'Way to go! It appears all your images have alt image text.', 'wds' ),
				)
			);
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * @return false|string
	 */
	private function format_failure_description() {
		ob_start();
		$this->print_common_description();
		?>
		<div class="wds-lh-section">
			<strong><?php esc_html_e( 'Status', 'wds' ); ?></strong>
			<?php
			Simple_Renderer::render(
				'notice',
				array(
					'class'   => 'sui-notice-warning',
					'message' => sprintf(
						/* translators: %s: Alt tag */
						esc_html__( "We've detected some of your images are missing %s text.", 'wds' ),
						'<strong>' . esc_html__( 'alt tag', 'wds' ) . '</strong>'
					),
				)
			);
			?>

			<?php $this->print_details_table(); ?>
		</div>

		<div class="wds-lh-section">
			<strong><?php esc_html_e( 'How to add alternative text to images', 'wds' ); ?></strong>
			<p><?php esc_html_e( 'Provide an alt attribute for every <img> element. If the image fails to load, the alt text is used as a placeholder so users have a sense of what the image was trying to convey.', 'wds' ); ?></p>

			<ul>
				<li style="margin: 25px 0;">
					<?php esc_html_e( 'Most images should have short, descriptive text:', 'wds' ); ?><br/>
					<div class="wds-lh-highlight" style="margin-top: 10px; border:none;">
						<?php
						echo join(
							'',
							array(
								$this->tag( '<img ' ),
								$this->attr( 'alt=' ),
								esc_html__( '"Audits set-up in Chrome DevTools" ', 'wds' ),
								$this->attr( 'src=' ),
								'"..."',
								$this->tag( '/>' ),
							)
						);
						?>
					</div>
				</li>

				<li style="margin-bottom: 25px;">
					<?php esc_html_e( 'If the image acts as decoration and does not provide any useful content, give it an empty alt="" attribute to remove it from the accessibility tree:', 'wds' ); ?>
					<br/>
					<div class="wds-lh-highlight" style="margin-top: 10px; border:none;">
						<?php
						echo join(
							'',
							array(
								$this->tag( '<img ' ),
								$this->attr( 'src=' ),
								'"background.png" ',
								$this->attr( 'alt=' ),
								'""',
								$this->tag( '/>' ),
							)
						);
						?>
					</div>
				</li>
			</ul>

			<p>
			<?php
			echo \smartcrawl_format_link(
				/* translators: %s: Link to documentation */
				esc_html__( 'See also %s.', 'wds' ),
				'https://web.dev/labels-and-text-alternatives/#include-text-alternatives-for-images-and-objects',
				esc_html__( 'Include text alternatives for images and objects', 'wds' ),
				'_blank'
			);
			?>
			</p>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * @return string
	 */
	public function get_id() {
		return self::ID;
	}

	/**
	 * @param $raw_details
	 *
	 * @return Table
	 */
	public function parse_details( $raw_details ) {
		$table = new Table(
			array(
				esc_html__( 'Failing Elements', 'wds' ),
				esc_html__( 'Selector', 'wds' ),
			),
			$this->get_report()
		);

		$items = \smartcrawl_get_array_value( $raw_details, 'items' );
		foreach ( $items as $item ) {
			$screenshot_node_id = \smartcrawl_get_array_value( $item, array( 'node', 'lhId' ) );

			$table->add_row(
				array(
					\smartcrawl_get_array_value( $item, array( 'node', 'snippet' ) ),
					\smartcrawl_get_array_value( $item, array( 'node', 'selector' ) ),
				),
				$screenshot_node_id
			);
		}

		return $table;
	}

	/**
	 * @return false|string
	 */
	public function get_action_button() {
		return $this->edit_homepage_button();
	}

	/**
	 * @return string
	 */
	private function format_copy_description() {
		$parts = array_merge(
			array(
				__( 'Tested Device: ', 'wds' ) . $this->get_device_label(),
				__( 'Audit Type: Content audits', 'wds' ),
				'',
				__( 'Failing Audit: Image elements do not have [alt] attributes', 'wds' ),
				'',
				__( "Status: We've detected some of your images are missing alt tag text.", 'wds' ),
				'',
			),
			$this->get_flattened_details(),
			array(
				'',
				__( 'Overview:', 'wds' ),
				__( 'Informative elements should aim for short, descriptive alternate text. Decorative elements can be ignored with an empty alt attribute.', 'wds' ),
				'',
				__( 'For more information please check the SEO Audits section in SmartCrawl plugin.', 'wds' ),
			)
		);
		return implode( "\n", $parts );
	}
}