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
//$document->addScript(JUri::root() . '/media/neno/js/strings.js');
$document->addStyleSheet(JUri::root() . '/media/neno/css/strings.css');

//$isOverlay = isset($displayData->isOverlay);

$translationStatesClasses = array();
$translationStatesClasses[NenoContentElementTranslation::TRANSLATED_STATE] = 'translated icon-checkmark';
$translationStatesClasses[NenoContentElementTranslation::QUEUED_FOR_BEING_TRANSLATED_STATE] = 'queued icon-clock';
$translationStatesClasses[NenoContentElementTranslation::SOURCE_CHANGED_STATE] = 'changed icon-loop';
$translationStatesClasses[NenoContentElementTranslation::NOT_TRANSLATED_STATE] = 'not-translated icon-cancel-2';

$translationStatesText = array();
$translationStatesText[NenoContentElementTranslation::TRANSLATED_STATE] = JText::_('COM_NENO_STATUS_TRANSLATED');
$translationStatesText[NenoContentElementTranslation::QUEUED_FOR_BEING_TRANSLATED_STATE] = JText::_('COM_NENO_STATUS_QUEUED');
$translationStatesText[NenoContentElementTranslation::SOURCE_CHANGED_STATE] = JText::_('COM_NENO_STATUS_CHANGED');
$translationStatesText[NenoContentElementTranslation::NOT_TRANSLATED_STATE] = JText::_('COM_NENO_STATUS_NOTTRANSLATED');

$translations = $displayData;

?>

<table class="table table-striped table-strings" id="table-strings">
	<thead>
	<tr>
		<th class="cell-check"><input type="checkbox"/></th>
		<th>Statuuus</th>
		<th>String</th>
		<th>Group</th>
		<th>Element</th>
		<th>Key</th>
		<th>Translation Methods</th>
		<th>Words</th>
		<th>Characters</th>
	</tr>
	</thead>
	<tbody>
	<?php /* @var $translation NenoContentElementTranslation */ ?>
	<?php foreach ($translations as $translation):
		$elementObject = $translation->getElement();
		//Kint::dump($elementObject);
		if (is_a($elementObject,'NenoContentElementField')) {
			$group = $elementObject->getTable()->getGroup()->getGroupName();
			$element = $elementObject->getTable()->getTableName();
			$key = $elementObject->getFieldName();


		} elseif (is_a($elementObject,'NenoContentElementLangstring')) {

		}


		?>
		<tr class="row-string">
			<td class="cell-check"><input type="checkbox"/></td>
			<td class="cell-status">
				<span class="status <?php echo $translationStatesClasses[$translation->getState()]; ?>" alt="<?php echo $translationStatesText[$translation->getState()]; ?>" title="<?php echo $translationStatesText[$translation->getState()]; ?>"></span>
			</td>
			<td title="<?php echo NenoHelper::html2text($translation->getOriginalText(), 200); ?>"><?php echo NenoHelper::html2text($translation->getString(), 200); ?></td>
			<td><?php echo $group; ?></td>
			<td><?php echo $element; ?></td>
			<td><?php echo $key; ?></td>
			<td><?php echo JText::_('COM_NENO_TRANSLATION_METHODS_' . strtoupper($translation->getTranslationMethod())); ?></td>
			<td><?php echo $translation->getWordsCounter(); ?></td>
			<td><?php echo $translation->getCharactersCounter(); ?></td>
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