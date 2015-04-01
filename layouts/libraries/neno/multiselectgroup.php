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

$document = JFactory::getDocument();
$document->addScript(JUri::root() . '/media/neno/js/multiselect.js');
$document->addStyleSheet(JUri::root() . '/media/neno/css/multiselect.css');

$isOverlay = isset($displayData->isOverlay);
?>

<div class="multiselect">
	<div>
		<a class="btn btn-toggle" data-toggle="multiselect" href="#">
			<?php JText::_('COM_NENO_TITLE_GROUPSELEMENTS'); ?>
			<span class="caret pull-right"></span>
		</a>

		<div id="multiselect" class="dropdown-select menu-multiselect <?php echo ($isOverlay) ? (' overlay') : (''); ?>">
			<table class="table-condensend table-multiselect" id="table-multiselect">
				<?php foreach ($displayData->groups as $group): ?>
					<?php $elementCount = $group->element_count; ?>
					<?php $class = $elementCount ? 'cell-expand' : ''; ?>
					<tr class="row-group element-row collapsed" data-level="1" data-id="group-<?php echo $group->id; ?>"
					    data-parent="header">
						<td class="first-cell <?php echo $class; ?>">
							<?php if ($elementCount): ?>
								<span class="toggle-arrow icon-arrow-right-3"></span>
							<?php endif; ?>
						</td>
						<td class="cell-check"><input type="checkbox"/></td>
						<td colspan="4"
						    title="<?php echo $group->group_name; ?>"><?php echo $group->group_name; ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
</div>