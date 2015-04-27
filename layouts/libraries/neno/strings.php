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
$translationStatesText[NenoContentElementTranslation::NOT_TRANSLATED_STATE]              = JText::_('COM_NENO_STATUS_NOTTRANSLATED');

$translations = $displayData;

?>

<table class="table table-striped table-strings" id="table-strings">
	<thead>
	<tr>
		<th class="cell-check"><input type="checkbox"/></th>
		<th><?php echo JText::_('COM_NENO_STATUS'); ?></th>
		<th><?php echo JText::_('COM_NENO_VIEW_STRINGS_STRING'); ?></th>
		<th><?php echo JText::_('COM_NENO_VIEW_STRINGS_GROUP'); ?></th>
		<th><?php echo JText::_('COM_NENO_VIEW_STRINGS_ELEMENT'); ?></th>
		<th><?php echo JText::_('COM_NENO_VIEW_STRINGS_KEY'); ?></th>
		<th><?php echo JText::_('COM_NENO_TRANSLATION_METHODS'); ?></th>
		<th><?php echo JText::_('COM_NENO_VIEW_STRINGS_WORDS'); ?></th>
		<th><?php echo JText::_('COM_NENO_VIEW_STRINGS_CHARS'); ?></th>
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
			<td title="<?php echo NenoHelper::html2text($translation->original_text, 200); ?>"><?php echo NenoHelper::html2text($translation->string, 200); ?></td>
			<td><?php echo $translation->breadcrumbs[0]; ?></td>
			<td><?php echo $translation->breadcrumbs[1]; ?></td>
			<td><?php echo $translation->breadcrumbs[2]; ?></td>
			<td><?php echo JText::_($translation->translation_method->name_constant); ?></td>
			<td><?php echo $translation->word_counter; ?></td>
			<td><?php echo $translation->characters_counter; ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
	<tfoot>
	<tr>
		<td colspan="9">
			<?php /*echo $this->pagination->getListFooter();*/ ?>
		</td>
	</tr>
	</tfoot>
</table>
