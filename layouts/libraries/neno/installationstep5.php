<?php

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

?>

<style>
	#task-messages {
		height: 500px;
		background-color: #f5f5f5;
		padding: 20px;
		color: #808080;
		overflow: auto;
	}

	.log-level-2 {
		margin-left: 20px;
		font-weight: bold;
		margin-top: 16px;
	}

	.log-level-3 {
		margin-left: 40px;
	}
</style>

<div class="installation-step">
	<div class="installation-body span12">
		<div class="error-messages"></div>
		<div id="installation-wrapper" class="hide">
			<h2><?php echo JText::_('COM_NENO_INSTALLATION_SETUP_COMPLETING_TITLE'); ?></h2>

			<div class="progress progress-striped active" id="progress-bar">
				<div class="bar" style="width: 0%;"></div>
			</div>
			<p><?php echo JText::_('COM_NENO_INSTALLATION_SETUP_COMPLETING_FINISH_SETUP_MESSAGE'); ?></p>

			<div id="task-messages">

			</div>
		</div>
		<div id="warning-message">
			<div class="alert"><?php echo JText::_('COM_NENO_INSTALLATION_WARNING_MESSAGE_TITLE'); ?></div>
			<p><?php echo JText::_('COM_NENO_INSTALLATION_WARNING_MESSAGE_P1'); ?></p>

			<?php if (!empty($displayData->tablesFound)): ?>
				<p><?php echo JText::_('COM_NENO_INSTALLATION_WARNING_MESSAGE_P2'); ?></p>
				<ul>
					<?php foreach ($displayData->tablesFound as $tableFound): ?>
						<li><?php echo JText::sprintf('COM_NENO_INSTALLATION_WARNING_MESSAGE_TABLE_MESSAGE', $tableFound->table, $tableFound->counter, $tableFound->language); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<label class="checkbox">
				<input type="checkbox"
				       id="backup-created-checkbox"><?php echo JText::_('COM_NENO_INSTALLATION_WARNING_MESSAGE_CHECKBOX_MESSAGE'); ?>
			</label>
			<button type="button" class="btn" id="proceed-button" disabled>
				<?php echo JText::_('COM_NENO_INSTALLATION_WARNING_MESSAGE_PROCEED_BUTTON'); ?>
			</button>
		</div>
	</div>

	<?php echo JLayoutHelper::render('installationbottom', 4, JPATH_NENO_LAYOUTS); ?>
</div>

<script>
	jQuery('#proceed-button').on('click', function () {

		if (jQuery('#backup-created-checkbox').prop('checked')) {
			jQuery('#warning-message').slideToggle(400, function () {
				jQuery('#installation-wrapper').slideToggle();
			});
			jQuery.ajax({
				url: 'index.php?option=com_neno&task=installation.getPreviousMessages',
				success: function (messages) {
					printMessages(messages);
					interval = setInterval(checkStatus, 2000);
				}
			});
		}

		sendDiscoveringStep();
	});

	jQuery('#backup-created-checkbox').on('click', function () {
		jQuery('#proceed-button').attr('disabled', !jQuery(this).prop('checked'));
	});

	function sendDiscoveringStep() {
		jQuery.ajax({
			url: 'index.php?option=com_neno&task=installation.processDiscoveringStep',
			success: function (data) {
				if (data != 'ok') {
					sendDiscoveringStep();
				} else {
					checkStatus();
					processInstallationStep();
					window.clearInterval(interval);
				}
			}
		});
	}

	function checkStatus() {
		jQuery.ajax({
			url: 'index.php?option=com_neno&task=installation.getSetupStatus',
			dataType: 'json',
			success: printMessages
		});
	}

	function printMessages(messages) {
		var percent = 0;
		for (var i = 0; i < messages.length; i++) {
			var log_line = jQuery('#installation-status-' + messages[i].level).clone().removeAttr('id').html(messages[i].message);
			if (messages[i].level == 1) {
				log_line.addClass('alert-' + messages[i].type);
			}
			jQuery('#task-messages').append(log_line);

			//Scroll to bottom
			jQuery("#task-messages").stop().animate({
				scrollTop: jQuery("#task-messages")[0].scrollHeight - jQuery("#task-messages").height()
			}, 400);
		}

		if (percent != 0) {
			jQuery('#progress-bar .bar').width(percent + '%');
		}
	}


</script>

<div class="hidden">
	<!-- Different HTML to show depending on log level -->
	<div id="installation-status-1" class="alert"></div>
	<div id="installation-status-2" class="log-level-2"></div>
	<div id="installation-status-3" class="log-level-3"></div>
</div>
