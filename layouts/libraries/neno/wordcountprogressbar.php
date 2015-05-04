<?php
/**
 * @package     Neno
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('JPATH_NENO') or die;
?>

<div class="translation-progress-bar">
	<div class="word-count">
		<?php $total = $displayData->wordCount->total; ?>
		<?php if ($displayData->showPercent): ?>
			<?php if ($displayData->wordCount->total != 0): ?>
				<?php $total = ((int) (($displayData->wordCount->translated * 100) / $displayData->wordCount->total)) . '%'; ?>
			<?php else : ?>
				<?php $total = '0%'; ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php echo $total; ?>
	</div>

	<div class="bar <?php echo (!$displayData->enabled) ? 'bar-disabled' : '' ?>">
		<div class="translated" style="width:<?php echo $displayData->widthTranslated; ?>%"
		     alt="<?php echo JText::_('COM_NENO_STATUS_TRANSLATED'); ?>: <?php echo $displayData->wordCount->translated; ?>"
		     title="<?php echo JText::_('COM_NENO_STATUS_TRANSLATED'); ?>: <?php echo $displayData->wordCount->translated; ?>">
		</div>

		<div class="queued" style="width:<?php echo $displayData->widthQueued; ?>%"
		     alt="<?php echo JText::_('COM_NENO_STATUS_QUEUED'); ?>: <?php echo $displayData->wordCount->queued; ?>"
		     title="<?php echo JText::_('COM_NENO_STATUS_QUEUED'); ?>: <?php echo $displayData->wordCount->queued; ?>">
		</div>
		<div class="changed" style="width:<?php echo $displayData->widthChanged; ?>%"
		     alt="<?php echo JText::_('COM_NENO_STATUS_CHANGED'); ?>: <?php echo $displayData->wordCount->changed; ?>"
		     title="<?php echo JText::_('COM_NENO_STATUS_CHANGED'); ?>: <?php echo $displayData->wordCount->changed; ?>">
		</div>
		<div class="not-translated" style="width:<?php echo $displayData->widthNotTranslated; ?>%"
		     alt="<?php echo JText::_('COM_NENO_STATUS_NOTTRANSLATED'); ?>: <?php echo $displayData->wordCount->untranslated; ?>"
		     title="<?php echo JText::_('COM_NENO_STATUS_NOTTRANSLATED'); ?>: <?php echo $displayData->wordCount->untranslated; ?>">
		</div>
	</div>
</div>

