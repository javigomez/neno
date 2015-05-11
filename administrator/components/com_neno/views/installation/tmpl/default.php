<?php
/**
 * @package     Neno
 * @subpackage  Views
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen', 'select');

$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . '/media/neno/css/progress-wizard.min.css');
$document->addStyleSheet(JUri::root() . '/media/neno/css/languageconfiguration.css');

// Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}
?>

<script>
	jQuery(document).ready(loadInstallationStep);

	function loadInstallationStep() {
		jQuery.ajax({
			url: 'index.php?option=com_neno&task=installation.loadInstallationStep',
			success: function (html) {
				jQuery('.installation-form').empty().append(html);
				bindEvents();
			}
		});
	}

	function bindEvents() {
		jQuery('.next-step-button').off('click').on('click', processInstallationStep);
		jQuery('.hasTooltip').tooltip();
		jQuery('select').chosen();
	}

	function processInstallationStep() {
		var allInputs = jQuery('.installation-step').find(':input');
		var data = {};

		allInputs.each(function () {
			switch (jQuery(this).prop('tagName').toLowerCase()) {
				case 'select':
					data[jQuery(this).prop('name')] = jQuery(this).find('option:selected').val();
					break;
				case 'input':
					switch (jQuery(this).prop('type')) {
						case 'checkbox':
							data[jQuery(this).prop('name')] = jQuery(this).is(':checked').val();
							break;
						default:
							data[jQuery(this).prop('name')] = jQuery(this).val();
							break;
					}
					break;
			}
		});
		jQuery.ajax({
			url: 'index.php?option=com_neno&task=installation.processInstallationStep',
			type: 'POST',
			data: data,
			dataType: "json",
			success: function (response) {
				if (response.status == 'ok') {
					loadInstallationStep();
				}
				else {
					renderErrorMessages(response.error_messages);
				}
			}
		});

		function renderErrorMessages(messages) {
			jQuery('.error-messages').empty();
			for (var i = 0; i < messages.length; i++) {
				jQuery('.error-messages').append('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>' + messages[i] + '</div>');
			}
		}
	}
</script>

<div id="j-sidebar-container" class="hide">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span12">
	<div class="installation-form"></div>
</div>

