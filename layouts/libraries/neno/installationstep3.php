<?php

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

?>

<style>
	.schedule-task-option {
		border: 1px solid #000;
		padding: 5px 10px;
	}
</style>

<div class="installation-step">
	<div class="installation-body span12">
		<h2><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_TITLE'); ?></h2>

		<div class="span6">
			<p><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_MESSAGE'); ?></p>

			<button type="button" class="btn btn-success next-step-button">
				<?php echo JText::_('COM_NENO_INSTALLATION_NEXT'); ?>
			</button>
		</div>
		<div class="span6">
			<p><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_DESCRIPTION_TEXT_P1'); ?></p>

			<p><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_DESCRIPTION_TEXT_P2'); ?></p>

			<p><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_DESCRIPTION_TEXT_P3'); ?></p>

			<h3><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_DESCRIPTION_TEXT_MANUAL_TRANSLATION_TITLE'); ?></h3>

			<p><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_DESCRIPTION_TEXT_MANUAL_TRANSLATION_MESSAGE'); ?></p>

			<h3><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_DESCRIPTION_TEXT_MACHINE_TRANSLATION_TITLE'); ?></h3>

			<p><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_DESCRIPTION_TEXT_MACHINE_TRANSLATION_MESSAGE'); ?></p>

			<h3><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_DESCRIPTION_TEXT_PROFESSIONAL_TRANSLATION_TITLE'); ?></h3>

			<p><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_DESCRIPTION_TEXT_PROFESSIONAL_TRANSLATION_MESSAGE'); ?></p>
		</div>
	</div>

	<?php echo JLayoutHelper::render('installationbottom', 2, JPATH_NENO_LAYOUTS); ?>
</div>