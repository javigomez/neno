<?php

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

?>

<style>
	.schedule-task-option {
		border: 1px solid #000;
		padding: 5px 10px;
		cursor: pointer;
	}

	.schedule-task-option.selected {
		border: 3px solid #000;
	}
</style>

<div class="installation-step">
	<div class="installation-body span12">
		
		<div class="error-messages"></div>
		
		<h2><?php echo JText::_('COM_NENO_INSTALLATION_SCHEDULED_TASK_TITLE'); ?></h2>
		
		<p><?php echo JText::_('COM_NENO_INSTALLATION_SCHEDULED_TASK_MESSAGE'); ?></p>
		
		<div class="schedule-task-options span12">
			<div class="schedule-task-option span4 selected" data-option="ajax">
				<h3><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_AJAX_MODULE_TITLE'); ?></h3>
				
				<p><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_AJAX_MODULE_P1'); ?></p>
				
				<p><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_AJAX_MODULE_P2'); ?></p>

				<div class="alert alert-info">
					<strong><?php echo JText::_('JDEFAULT'); ?></strong>
				</div>
			</div>
			<div class="schedule-task-option span4" data-option="cron">
				<h3><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_CRON_TITLE'); ?></h3>
				
				<p><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_CRON_P1'); ?></p>
				
				<p><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_CRON_P2'); ?></p>
				<a href="#"><?php echo JText::_('COM_NENO_DOCUMENTATION'); ?></a>

				<div class="alert alert-success">
					<strong><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_CRON_RECOMMEND'); ?></strong>
				</div>
			</div>
			<div class="schedule-task-option span4" data-option="disable">
				<h3><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_DISABLE_TITLE'); ?></h3>
				
				<p><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_DISABLE_P1'); ?></p>
				
				<p><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_DISABLE_P2'); ?></p>
				
				<div class="alert"><?php echo JText::_('COM_NENO_INSTALLATION_TASK_OPTION_DISABLE_P3'); ?></div>
			</div>
		</div>
		
		<button type="button" class="btn btn-success next-step-button">
			<?php echo JText::_('COM_NENO_INSTALLATION_NEXT'); ?>
		</button>
	</div>

	<input type="hidden" name="schedule_task_option" id="schedule_task_option" value="ajax"/>
	
	<?php echo JLayoutHelper::render('installationbottom', 1, JPATH_NENO_LAYOUTS); ?>
</div>

<script type="text/javascript">
	jQuery('.schedule-task-option').off('click').on('click', function () {
		jQuery('.selected').removeClass('selected');
		jQuery(this).addClass('selected');
		jQuery('#schedule_task_option').val(jQuery(this).data('option'));
	});
</script>