<?php
/**
 * @package    Neno
 *
 * @author     Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright  Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

$step = $displayData;

?>


<div class="installation-bottom span12">
	<ul class="progress-indicator">
		<li <?php echo $step == 1 ? 'class="completed"' : ''; ?>>
			<span class="bubble"></span>
			<?php echo JText::_('COM_NENO_INSTALLATION_STEP_1'); ?>
		</li>
		<li <?php echo $step == 2 ? 'class="completed"' : ''; ?>>
			<span class="bubble"></span>
			<?php echo JText::_('COM_NENO_INSTALLATION_STEP_2'); ?>
		</li>
		<li <?php echo $step == 3 ? 'class="completed"' : ''; ?>>
			<span class="bubble"></span>
			<?php echo JText::_('COM_NENO_INSTALLATION_STEP_3'); ?>
		</li>
		<li <?php echo $step == 4 ? 'class="completed"' : ''; ?>>
			<span class="bubble"></span>
			<?php echo JText::_('COM_NENO_INSTALLATION_STEP_4'); ?>
		</li>
		<li <?php echo $step == 5 ? 'class="completed"' : ''; ?>>
			<span class="bubble"></span>
			<?php echo JText::_('COM_NENO_INSTALLATION_STEP_5'); ?>
		</li>
	</ul>
</div>