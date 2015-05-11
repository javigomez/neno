<?php

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

?>

<div class="installation-step">
	<div class="installation-body span12">
		<div class="error-messages"></div>
		<h2><?php echo JText::_('COM_NENO_INSTALLATION_SETUP_COMPLETING_TITLE'); ?></h2>

		<div class="progress progress-striped active" id="progress-bar">
			<div class="bar" style="width: 0%;"></div>
		</div>

		<div id="task-message">

		</div>
		<p><?php echo JText::_('COM_NENO_INSTALLATION_SETUP_COMPLETING_FINISH_SETUP_MESSAGE'); ?></p>
	</div>

	<?php echo JLayoutHelper::render('installationbottom', 4, JPATH_NENO_LAYOUTS); ?>
</div>

<script>
	jQuery.ajax({
		url: 'index.php?option=com_neno&task=installation.finishingSetup',
		success: function (data) {
			checkStatus();
			processInstallationStep();
			window.clearInterval(interval);
		}
	});

	interval = window.setInterval(checkStatus, 2000);

	function checkStatus() {
		jQuery.ajax({
			url: 'index.php?option=com_neno&task=installation.getSetupStatus',
			dataType: 'json',
			success: function (data) {
				jQuery('#task-message').empty().append(data.message);
				jQuery('#progress-bar .bar').width(data.percent + '%');
			}
		});
	}


</script>