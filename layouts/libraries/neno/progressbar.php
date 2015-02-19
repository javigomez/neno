<?php

/**
 * @package     Neno
 * @subpackage  Helpers
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('JPATH_NENO') or die;

if ($displayData === null): ?>
	<div class="bar bar-disabled" alt="<?php echo JText::_('COM_NENO_STATUS_NOTTRANSLATED'); ?>"
	     title="<?php echo JText::_('COM_NENO_STATUS_NOTTRANSLATED'); ?>"></div>
<?php else: ?>

	<div class="word-count"><?php echo $displayData->stringsStatus['totalStrings']; ?></div>

	<div class="bar">
		<div class="translated" style="width:<?php echo $displayData->widthTranslated; ?>%"
		     alt="<?php echo JText::_('COM_NENO_STATUS_TRANSLATED'); ?>: <?php echo $displayData->stringsStatus['translated']; ?>"
		     title="<?php echo JText::_('COM_NENO_STATUS_TRANSLATED'); ?>: <?php echo $displayData->stringsStatus['translated']; ?>">
		</div>

		<div class="queued" style="width:<?php echo $displayData->widthQueued; ?>%"
		     alt="<?php echo JText::_('COM_NENO_STATUS_QUEUED'); ?>: <?php echo $displayData->stringsStatus['queued']; ?>"
		     title="<?php echo JText::_('COM_NENO_STATUS_QUEUED'); ?>: <?php echo $displayData->stringsStatus['queued']; ?>">
		</div>
		<div class="changed" style="width:<?php echo $displayData->widthChanged; ?>%"
		     alt="<?php echo JText::_('COM_NENO_STATUS_CHANGED'); ?>: <?php echo $displayData->stringsStatus['changed']; ?>"
		     title="<?php echo JText::_('COM_NENO_STATUS_CHANGED'); ?>: <?php echo $displayData->stringsStatus['changed']; ?>">
		</div>
		<div class="not-translated" style="width:<?php echo $displayData->widthNotTranslated; ?>%"
		     alt="<?php echo JText::_('COM_NENO_STATUS_NOTTRANSLATED'); ?>: <?php echo $displayData->stringsStatus['notTranslated']; ?>"
		     title="<?php echo JText::_('COM_NENO_STATUS_NOTTRANSLATED'); ?>: <?php echo $displayData->stringsStatus['notTranslated']; ?>">
		</div>
	</div>

<?php endif; ?>

