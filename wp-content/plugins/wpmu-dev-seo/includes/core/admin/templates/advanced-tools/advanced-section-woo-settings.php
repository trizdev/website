<?php
$option_name = empty( $_view['option_name'] ) ? '' : $_view['option_name']; ?>

<input type="hidden" value="1" name="<?php echo esc_attr( $option_name ); ?>[save_woo]"/>

<div
	class="wds-vertical-tab-section sui-box tab_woo <?php echo $is_active ? '' : 'hidden'; ?>"
	id="tab_woo"
>
	<div id="wds-woo-settings-tab"></div>
</div>