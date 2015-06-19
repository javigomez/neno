<?php
/**
 * @package    Neno
 *
 * @author     Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright  Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

//No direct access
defined('_JEXEC') or die;

?>


<div class="installation-step">
	<img src="<?php echo JUri::root() . '/media/neno/images/neno_logo.png'; ?>" width="150"/>

	<h2><?php echo JText::_('COM_NENO_INSTALLATION_NENO_WAS_INSTALL_SUCCESSFULLY'); ?></h2>

	<p><?php echo JText::_('COM_NENO_INSTALLATION_MESSAGE'); ?></p>

	<button type="button" class="btn btn-success next-step-button">
		<?php echo JText::_('COM_NENO_INSTALLATION_GET_STARTED_BUTTON'); ?>
	</button>
</div>