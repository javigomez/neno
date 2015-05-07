<?php

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

?>


<div class="installation-step">
	<div class="installation-body span12">
		<h2><?php echo JText::_('COM_NENO_INSTALLATION_WELCOME_MESSAGE'); ?></h2>

		<p><?php echo JText::_('COM_NENO_INSTALLATION_STEP_ONE_MESSAGE'); ?></p>

		<div class="control-group">
			<label><?php echo JText::_('COM_NENO_INSTALLATION_STEP_ONE_SELECT_SOURCE_LANGUAGE'); ?></label>
			<?php echo $displayData->select_widget; ?>
			<a href="#" class="hasTooltip" data-toggle="tooltip" data-html="true" data-placement="right"
			   title="<?php echo JText::_('COM_NENO_INSTALLATION_STEP_ONE_SOURCE_LANGUAGE_HELP_TEXT'); ?>">
				<span class="icon-help"></span>
			</a>
		</div>

		<button type="button" class="btn btn-success next-step-button">
			<?php echo JText::_('COM_NENO_INSTALLATION_NEXT'); ?>
		</button>
	</div>

	<?php echo JLayoutHelper::render('installationbottom', 1, JPATH_NENO_LAYOUTS); ?>
</div>