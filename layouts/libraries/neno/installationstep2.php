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
		<h2><?php echo JText::_('COM_NENO_INSTALLATION_SCHEDULED_TASK_TITLE'); ?></h2>

		<p><?php echo JText::_('COM_NENO_INSTALLATION_SCHEDULED_TASK_MESSAGE'); ?></p>

		<div class="schedule-task-options span12">
			<div class="schedule-task-option span4 default">
				<h3><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_AJAX_MODULE_TITLE'); ?></h3>

				<p><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_AJAX_MODULE_P1'); ?></p>

				<p><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_AJAX_MODULE_P2'); ?></p>
				<button class="btn"><?php echo JText::_('JDEFAULT'); ?></button>
			</div>
			<div class="schedule-task-option span4">
				<h3><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_CRON_TITLE'); ?></h3>

				<p><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_CRON_P1'); ?></p>

				<p><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_CRON_P2'); ?></p>
				<a href="#"><?php echo JText::_('COM_NENO_DOCUMENTATION'); ?></a>
				<button class="btn"><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_CRON_RECOMMEND'); ?></button>
			</div>
			<div class="schedule-task-option span4">
				<h3><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_DISABLE_TITLE'); ?></h3>

				<p><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_DISABLE_P1'); ?></p>

				<p><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_DISABLE_P2'); ?></p>

				<p><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_DISABLE_P3'); ?></p>
			</div>
		</div>

		<button type="button" class="btn btn-success next-step-button">
			<?php echo JText::_('COM_NENO_INSTALLATION_NEXT'); ?>
		</button>
	</div>

	<?php echo JLayoutHelper::render('installationbottom', 1, JPATH_NENO_LAYOUTS); ?>
</div>