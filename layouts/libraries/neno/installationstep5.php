<?php

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

?>

<div class="installation-step">
	<div class="installation-body span12">
		<div class="error-messages"></div>
		<h2><?php echo JText::_('COM_NENO_INSTALLATION_SETUP_COMPLETING_TITLE'); ?></h2>

		<button type="button" class="btn btn-success next-step-button">
			<?php echo JText::_('COM_NENO_INSTALLATION_SETUP_COMPLETING_FINISH_SETUP_BUTTON'); ?>
		</button>
		<p><?php echo JText::_('COM_NENO_INSTALLATION_SETUP_COMPLETING_FINISH_SETUP_MESSAGE'); ?></p>
	</div>

	<?php echo JLayoutHelper::render('installationbottom', 4, JPATH_NENO_LAYOUTS); ?>
</div>