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

$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . '/media/neno/css/progress-wizard.min.css');

//JHtml::_('bootstrap.tooltip');
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
		jQuery('.next-step-button').off('click').on('click', function () {
			loadInstallationStep();
		});

		jQuery('.hasTooltip').tooltip();
	}
</script>

<div class="installation-form"></div>

