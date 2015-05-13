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
        font-size: 16px;
        margin-right: 20px;
	}
</style>

<div class="btn-wrapper pull-right">
	<button class="neno-no-button">
		<?php echo $displayData->button; ?>
	</button>
    
    <a href="<?php echo JRoute::_('index.php?option=com_neno&view=externaltranslations'); ?>" class="btn btn-success"><?php echo JText::_('COM_NENO_TRANSLATION_CREDIT_TOOLBAR_BUTTON'); ?></a>
</div>