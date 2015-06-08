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
$document->addStyleSheet(JUri::root() . '/media/neno/css/strings.css');

$translationStatesClasses                                                                   = array ();
$translationStatesClasses[NenoContentElementTranslation::TRANSLATED_STATE]                  = 'translated icon-checkmark';
$translationStatesClasses[NenoContentElementTranslation::QUEUED_FOR_BEING_TRANSLATED_STATE] = 'queued icon-clock';
$translationStatesClasses[NenoContentElementTranslation::SOURCE_CHANGED_STATE]              = 'changed icon-loop';
$translationStatesClasses[NenoContentElementTranslation::NOT_TRANSLATED_STATE]              = 'not-translated icon-cancel-2';

$translationStatesText                                                                   = array ();
$translationStatesText[NenoContentElementTranslation::TRANSLATED_STATE]                  = JText::_('COM_NENO_STATUS_TRANSLATED');
$translationStatesText[NenoContentElementTranslation::QUEUED_FOR_BEING_TRANSLATED_STATE] = JText::_('COM_NENO_STATUS_QUEUED');
$translationStatesText[NenoContentElementTranslation::SOURCE_CHANGED_STATE]              = JText::_('COM_NENO_STATUS_CHANGED');
$translationStatesText[NenoContentElementTranslation::NOT_TRANSLATED_STATE]              = JText::_('COM_NENO_STATUS_NOT_TRANSLATED');

$translations  = $displayData->translations;
$listOrder     = $displayData->state->get('list.ordering');
$listDirection = $displayData->state->get('list.direction');

?>

<table class="table table-striped table-strings" id="table-strings">
	<thead>
	<tr>
		<th class="cell-check"><input type="checkbox"/></th>
		<th><?php echo JHtml::_('grid.sort', 'COM_NENO_STATUS', 'a.state', $listDirection, $listOrder); ?></th>
		<th><?php echo JHtml::_('grid.sort', 'COM_NENO_VIEW_STRINGS_STRING', 'a.string', $listDirection, $listOrder); ?></th>
		<th><?php echo JHtml::_('grid.sort', 'COM_NENO_VIEW_STRINGS_GROUP', 'a.group', $listDirection, $listOrder); ?></th>
		<th><?php echo JHtml::_('grid.sort', 'COM_NENO_VIEW_STRINGS_ELEMENT', 'a.element_name', $listDirection, $listOrder); ?></th>
		<th><?php echo JHtml::_('grid.sort', 'COM_NENO_VIEW_STRINGS_KEY', 'a.key', $listDirection, $listOrder); ?></th>
		<th><?php echo JHtml::_('grid.sort', 'COM_NENO_TRANSLATION_METHODS', 'a.translation_method', $listDirection, $listOrder); ?></th>
		<th><?php echo JHtml::_('grid.sort', 'COM_NENO_VIEW_STRINGS_WORDS', 'a.word_counter', $listDirection, $listOrder); ?></th>
		<th><?php echo JHtml::_('grid.sort', 'COM_NENO_VIEW_STRINGS_CHARS', 'a.characters', $listDirection, $listOrder); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php /* @var $translation JObject */ ?>
	<?php foreach ($translations as $translation): ?>
		<tr class="row-string">
			<td class="cell-check"><input type="checkbox"/></td>
			<td class="cell-status">
				<span class="status <?php echo $translationStatesClasses[$translation->state]; ?>"
				      alt="<?php echo $translationStatesText[$translation->state]; ?>"
				      title="<?php echo $translationStatesText[$translation->state]; ?>"></span>
			</td>
			<td title="<?php echo JText::sprintf('COM_NENO_VIEW_STRINGS_EDIT', NenoHelper::html2text($translation->original_text, 200)); ?>">
				<a href="index.php?option=com_neno&view=editor&stringId=<?php echo $translation->id; ?>">
					<?php echo NenoHelper::html2text($translation->string, 200); ?>
				</a>
			</td>
			<td><?php echo $translation->breadcrumbs[0]; ?></td>
			<td><?php echo $translation->breadcrumbs[1]; ?></td>
			<td><?php echo $translation->breadcrumbs[2]; ?></td>
			<td>
				<?php for ($i = 0; $i < count($translation->translation_methods); $i++): ?>
					<?php echo JText::_($translation->translation_methods[$i]->name_constant); ?>
					<?php echo ($i < count($translation->translation_methods) - 1) ? ',' : ''; ?>
				<?php endfor; ?>
			</td>
			<td><?php echo $translation->word_counter; ?></td>
			<td><?php echo $translation->characters_counter; ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
	<tfoot>
	<tr>
		<td colspan="9">
			<?php echo $displayData->pagination->getListFooter(); ?>
		</td>
	</tr>
	</tfoot>
</table>
