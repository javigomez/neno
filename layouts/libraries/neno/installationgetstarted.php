<?php

defined('_JEXEC') or die;

?>


<div class="installation-step">
	<img src="<?php echo JUri::root() . '/media/neno/images/neno_logo.png'; ?>" width="150"/>

	<h2><?php echo JText::_('COM_NENO_INSTALLATION_NENO_WAS_INSTALL_SUCCESSFULLY'); ?></h2>

	<p><?php echo JText::_('COM_NENO_INSTALLATION_MESSAGE'); ?></p>

	<button type="button" class="btn btn-success next-step-button">
		<?php echo JText::_('COM_NENO_INSTALLATION_GET_STARTED_BUTTON'); ?>
	</button>
</div>