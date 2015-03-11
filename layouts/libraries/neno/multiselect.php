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
	<!--
	<div class="btn-wrapper input-append">
		<input type="text" name="filter[search]" id="filter_search" value="" class="js-stools-search-string" placeholder="Search">
		<button type="submit" class="btn hasTooltip" title="" data-original-title="Search">
			<i class="icon-search"></i>
		</button>
	</div>
	-->

	<div class="bt n-group">
		<a class="btn btn-toggle" data-toggle="multiselect" href="#">
			<?php JText::_('COM_NENO_TITLE_GROUPSELEMENTS'); ?>
			<span class="caret pull-right"></span>
		</a>

		<div id="multiselect" class="dro pdown-menu menu-multiselect <?php echo ($isOverlay)?(' overlay'):(''); ?>">
			<table class="table-condensend table-multiselect" id="table-multiselect">
				<?php /* @var $group NenoContentElementGroup */ ?>
				<?php foreach ($displayData->groups as $group):
					$stringsTranslated = $group->getLanguageWordsTranslated();
					$stringsQueued = $group->getLanguageWordsQueuedToBeTranslated();
					$stringsChanged = $group->getLanguageWordsSourceHasChanged();
					$stringsNotTranslated = $group->getLanguageWordsNotTranslated();
					$countLanguageStrings = $stringsTranslated + $stringsQueued + $stringsChanged + $stringsNotTranslated;
					if ($countLanguageStrings !== 0)
					{
						$stringsFile = NenoHelper::getWorkingLanguage() . '.' . $group->getGroupName() . '.ini';
					}
					?>
					<tr class="row-group collapsed" data-level="1" data-id="group<?php echo $group->getId(); ?>" data-parent="header">
						<td <?php echo (count($group->getTables()) || $countLanguageStrings)?(' class="cell-expand"><span class="icon-arrow-right-3"></span>'):('>'); ?></td>
						<td class="cell-check"><input type="checkbox"/></td>
						<td colspan="4" title="<?php echo $group->getGroupName(); ?>"><?php echo $group->getGroupName(); ?></td>
					</tr>
					<?php /* @var $table NenoContentElementTable */ ?>
					<?php foreach ($group->getTables() as $table): ?>
					<tr class="row-table collapsed hide" data-level="2" data-id="table<?php echo $table->getId(); ?>" data-parent="group<?php echo $group->getId(); ?>">
						<td></td>
						<td <?php echo (count($table->getFields()))?(' class="cell-expand"><span class="icon-arrow-right-3"></span>'):('>'); ?></td>
						<td class="cell-check"><input type="checkbox"/></td>
						<td colspan="3" title="<?php echo $table->getTableName(); ?>"><?php echo $table->getTableName(); ?></td>
					</tr>
					<?php /* @var $field NenoContentElementField */ ?>
					<?php foreach ($table->getFields() as $field): ?>
						<tr class="row-field hide" data-level="3" data-id="field<?php echo $field->getId(); ?>" data-parent="table<?php echo $table->getId(); ?>">
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td class="cell-check"><input type="checkbox"/></td>
							<td title="<?php echo $field->getFieldName() ?>"><?php echo $field->getFieldName() ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endforeach; ?>
					<?php if($countLanguageStrings !== 0): ?>
					<tr class="row-file collapsed hide" data-level="2" data-id="file<?php echo ''; ?>" data-parent="group<?php echo $group->getId(); ?>">
						<td></td>
						<td></td>
						<td class="cell-check"><input type="checkbox"/></td>
						<td colspan="3" title="<?php echo $stringsFile; ?>"><?php echo $stringsFile; ?></td>
					</tr>
				<?php endif; ?>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
</div>