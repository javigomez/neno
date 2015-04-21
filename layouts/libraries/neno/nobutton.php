<?php
/**
 * Created by PhpStorm.
 * User: victor
 * Date: 21/04/15
 * Time: 16:09
 */

defined('JPATH_NENO') or die;

?>

<style type="text/css">
	.neno-no-button {
		outline: 0;
		background: none;
		border: 0;
		cursor: default;
	}
</style>

<div class="btn-wrapper <?php echo $displayData->class; ?>">
	<button class="neno-no-button">
		<?php echo $displayData->button; ?>
	</button>
</div>