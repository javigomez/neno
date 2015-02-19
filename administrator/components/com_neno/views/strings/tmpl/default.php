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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
//JHtml::_('searchtools.form', $formSelector, $data['options']);

// Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}

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
?>

<style>
	.table-strings th {
		color: #2E87CB;
	}
	.table-strings .cell-check {
		width: 18px !important;
	}
	.table-strings .cell-check input {
		margin-top: 0;
	}
	.table-strings .cell-status {
		width: 50px;
		text-align: center;
		padding: 5px 0 0 0;
	}
	.table-strings .status {
		height: 22px;
		width: 22px;
		border-radius: 50%;
		font-size: 11px;
		color: white;
		line-height: 23px;
	}
	.table-strings .status.translated {
		background-color: #0ba14b;
	}
	.table-strings .status.queued {
		background-color: #1e8ab6;
	}
	.table-strings .status.changed {
		background-color: #f19d1a;
	}
	.table-strings .status.not-translated {
		background-color: #e61e26;
	}
	.multiselect-wrapper {
		width: 200px;
	}
</style>

<script type="text/javascript">

	jQuery(document).ready(function () {

	});
</script>

<?php if (!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
<form action="<?php echo JRoute::_('index.php?option=com_neno&view=strings'); ?>" method="post" name="adminForm" id="adminForm">
<div id="j-main-container" class="span10">
	<?php else : ?>
	<form action="<?php echo JRoute::_('index.php?option=com_neno&view=strings'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		<!--
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('JSEARCH_FILTER');?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button class="btn hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button class="btn hasTooltip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
		</div>
		-->
		<?php endif;
		//Kint::dump(count($this->items));
		//Kint::dump($this->items[0]);
		//Kint::dump($this->items[0]->getSourceElementData());
		?>

		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<div class="multiselect-wrapper">
		<?php
		// Search tools bar
		$displayData = NenoHelper::getGroups();
		echo JLayoutHelper::render('multiselect', $displayData, JPATH_NENO_LAYOUTS);
		?>
		</div>
		<table class="table table-striped table-strings" id="table-strings">
			<thead>
				<tr>
					<th class="cell-check"><input type="checkbox"/></th>
					<th>Status</th>
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
			<?php foreach ($this->items as $translation):
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
					<td title="<?php echo $translation->getOriginalText(); ?>"><?php echo $translation->getString(); ?></td>
					<td><?php echo $group; ?></td>
					<td><?php echo $element; ?></td>
					<td><?php echo $key; ?></td>
					<td><?php echo JText::_('COM_NENO_TRANSLATION_METHODS_' . strtoupper($translation->getTranslationMethod())); ?></td>
					<td><?php echo $translation->getWordsCounter(); ?></td>
					<td><?php echo $translation->getCharactersCounter(); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</form>

</div>


