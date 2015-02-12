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

// Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}

$workingLanguage = NenoHelper::getWorkingLanguage();

?>

<style>
	.group-container {
		padding-bottom: 15px;
		margin-bottom: 10px;
		border-bottom: 2px solid #ccc;
	}
	.table-container {
		padding-top: 5px;
		border-top: 2px solid #dddddd;
		margin-left: 25px;
		display: none;
	}
	.fields-container {
		display: none;
	}
	/*.table-groups-elements .cell-check,*/
	.table-groups-elements .cell-expand,
	.table-groups-elements .cell-collapse {
		width: 15px;
	}
	.table-groups-elements .cell-check {
		width: 18px !important;
	}
	.table-groups-elements .cell-check input {
		margin-top: 0;
	}
	.table-groups-elements .cell-expand,
	.table-groups-elements .cell-collapse {
		padding-top: 10px;
		padding-bottom: 6px;
		cursor: pointer;
	}
	.table-groups-elements th,
	.table-groups-elements .row-group > td,
	.table-groups-elements .row-table > td {
		background-color: #ffffff !important;
		color: #2E87CB;
	}
	.table-groups-elements .row-file > td {
		background-color: #ffffff !important;
	}
	.table-groups-elements .type-icon {
		color: #333 !important;
	}
	.table-groups-elements th {
		border-top: none;
	}
	.table-groups-elements .icon-arrow-right-3,
	.table-groups-elements .icon-arrow-down-3 {
		color: #A7A7A7;
	}
	.table-groups-elements .group-label {
		width: 500px;
	}
	.table-groups-elements .table-groups-elements-label {
		width: 220px;
	}
	/*.table-groups-elements .table-groups-elements-label.translation-methods {
		width: 200px;
	}*/
	.table-groups-elements .table-groups-elements-blank {
		width: 15%;
	}
	.table-groups-elements .row-field {
		background-color: white;
	}
	.table-groups-elements .translation-progress-bar .word-count {
		float: left;
	}
	.table-groups-elements .translation-progress-bar .bar {
		width: 120px;
		height: 10px;
		margin-left: 30px;
		margin-top: 3px;
	}
	.table-groups-elements .translation-progress-bar .bar div {
		height: 100%;
		float: left;
	}
	.table-groups-elements .translation-progress-bar .translated {
		background-color: #6BC366;
	}
	.table-groups-elements .translation-progress-bar .queued {
		background-color: #368AB6;
	}
	.table-groups-elements .translation-progress-bar .changed {
		background-color: #FAC819;
	}
	.table-groups-elements .translation-progress-bar .not-translated {
		background-color: #DB3F35;
	}
	.table-groups-elements .translation-progress-bar .bar-disabled {
		background-color: #CACACA;
		/*width: 100px;*/
	}
	.toggle-translate .btn-group > .btn {
		font-size: 11px;
		line-height: 8px;
	}

</style>

<script type="text/javascript">

	function toggleCollapseRow (row) {
		var rowType = '';
		if (row.hasClass('row-group')) {
			rowType = 'group';
		} else if (row.hasClass('row-table')) {
			rowType = 'table';
		}
		var nextRow = row.next('tr');
		while (nextRow.length!=0 && !nextRow.hasClass('row-'+rowType)) {
			if (nextRow.attr('data-level') == parseInt(row.attr('data-level')) + 1) {
				nextRow.toggleClass('hide');
				if (nextRow.hasClass('row-table') && row.hasClass('expanded') && nextRow.hasClass('expanded')) {
					toggleCollapseRow(nextRow);
				}
			}
			nextRow = nextRow.next('tr');
		}
		if (row.attr('data-level') != 3) {
			row.toggleClass('collapsed');
			row.toggleClass('expanded');
			row.children('td.cell-expand').first().children('span').first().toggleClass('icon-arrow-right-3');
			row.children('td.cell-expand').first().children('span').first().toggleClass('icon-arrow-down-3');
		}
	}

	function checkDescendant (check) {
		var state = check.prop('checked'),
			row = jQuery(check).closest('tr'),
		    nextRow = row.next('tr');
		while (nextRow.length!=0 && nextRow.attr('data-level') > row.attr('data-level') ) {
			nextRow.find('input[type=checkbox]').prop('checked', state);
			nextRow = nextRow.next('tr');
		}
	}

	function uncheckAncestor (check) {
		// Use function only when checkbox is not checked
		if (jQuery(check).prop('checked')) {
			return;
		}
		var row = jQuery(check).closest('tr'),
			targetLevel = row.attr('data-level') - 1,
			prevRow = row.prev('tr');
		while (prevRow.length!=0) {
			if (prevRow.attr('data-level') == targetLevel) {
				prevRow.find('input[type=checkbox]').prop('checked', false);
				targetLevel--;
			}
			prevRow = prevRow.prev('tr');
		}
	}

	function toggleStringStateAjax (radio) {
		var fieldset = radio.parent();
		var status = radio.attr('value');
		var field = fieldset.attr('data-field');
		var notstatus;
		if (radio.attr('value') == '0') {
			notstatus = '1';
		} else {
			notstatus = '0';
		}
		jQuery.ajax({
			type: "POST",
			url: "index.php?option=com_neno&task=groupselements.enableDisableContentElementField",
			data: { fieldId: field, translateStatus: status }
		})
		.done(function( ret ) {
			if (ret) {
				fieldset.find('#check-toggle-translate-' + field + '-' + status).unbind('click');
				fieldset.find('#check-toggle-translate-' + field + '-' + notstatus).click(function (e) {
					toggleStringStateAjax(jQuery(this));
				});
				fieldset.closest('tr').find('.translation-progress-bar').html(ret);
			}
		});
	}

	jQuery(document).ready(function () {
		/*jQuery('#table-groups-elements input[type="checkbox"]').change(function(e) {
			var checked = jQuery(this).prop("checked"),
				row = jQuery(check).closest('tr'),
				siblings = row.siblings();

			row.find('input[type="checkbox"]').prop({
				indeterminate: false,
				checked: checked
			});
			function checkRows(el) {
				var parent = jQuery('tr[data-id=' + el.attr('data-parent') ),
					all = true,
					siblings = jQuery('tr[data-parent=' + el.attr('data-parent') );

				siblings.each(function() {
					return all = ($(this).find('input[type="checkbox"]').prop("checked") === checked);
				});

				if (all && checked) {
					parent.children('input[type="checkbox"]').prop({
						indeterminate: false,
						checked: checked
					});
					checkRows(parent);
				} else if (all && !checked) {
					parent.children('input[type="checkbox"]').prop("checked", checked);
					parent.children('input[type="checkbox"]').prop("indeterminate", (parent.find('input[type="checkbox"]:checked').length > 0));
					checkRows(parent);
				} else {
					el.parents("li").children('input[type="checkbox"]').prop({
						indeterminate: true,
						checked: false
					});
				}
			}
			checkRows(row);
		});*/
		jQuery('#table-groups-elements tr.collapsed .cell-expand').click(function (e) {
			var row = jQuery(this).parent();
			toggleCollapseRow (row);
		});

		jQuery('#table-groups-elements input[type=checkbox]').click(function (e) {
			checkDescendant(jQuery(this));
			if (!jQuery(this).prop('checked')) {
				uncheckAncestor(jQuery(this));
			}
		});

		jQuery('#table-groups-elements .check-toggle-translate-radio').not("[checked='checked']").click(function (e) {
			toggleStringStateAjax(jQuery(this));
		});

	});

</script>

<?php if (!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>
		<table class="table table-striped table-groups-elements" id="table-groups-elements">
			<tr class="row-header" data-level="0" data-id="header">
				<th></th>
				<th class="cell-check"><input type="checkbox"/></th>
				<th colspan="3" class="group-label"><?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_GROUPS'); ?></th>
				<th class="table-groups-elements-label"><?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_ELEMENTS'); ?></th>
				<th class="table-groups-elements-label"><?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_COUNT'); ?></th>
				<th class="table-groups-elements-label translation-methods"><?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_METHODS'); ?></th>
				<th class="table-groups-elements-blank"></th>
			</tr>
			<?php
			Kint::dump($this->items[count($this->items)-1]);
			//var_dump($this->items[1]->getLanguageStrings());
			//var_dump($this->items[1]);
			?>
			<?php /* @var $group NenoContentElementGroup */ ?>
			<?php foreach ($this->items as $group):

				$fieldsTranslated = 0;
				$fieldsQueued = 0;
				$fieldsChanged = 0;
				$fieldsNotTranslated = 0;
				$countElements = count($group->getTables());
				$groupTables = array();
				/* @var $table NenoContentElementTable */
				foreach ($group->getTables() as $table)
				{
					$groupTables[$table->getId()] = array();
					/* @var $field NenoContentElementField */
					foreach ($table->getFields() as $field)
					{
						if (!$field->isTranslate())
						{
							continue;
						}
						$groupTables[$table->getId()][$field->getId()] = array();
						$groupTables[$table->getId()][$field->getId()]['totalStrings'] = 0;
						$groupTables[$table->getId()][$field->getId()]['totalStrings'] += ($groupTables[$table->getId()][$field->getId()]['translated'] = $field->getStringsTranslated());
						$fieldsTranslated += $field->getStringsTranslated();
						$groupTables[$table->getId()][$field->getId()]['totalStrings'] += ($groupTables[$table->getId()][$field->getId()]['queued'] = $field->getStringsQueuedToBeTranslated());
						$fieldsQueued += $field->getStringsQueuedToBeTranslated();
						$groupTables[$table->getId()][$field->getId()]['totalStrings'] += ($groupTables[$table->getId()][$field->getId()]['changed'] = $field->getStringsSourceHasChanged());
						$fieldsChanged += $field->getStringsSourceHasChanged();
						$groupTables[$table->getId()][$field->getId()]['totalStrings'] += ($groupTables[$table->getId()][$field->getId()]['notTranslated'] = $field->getStringsNotTranslated());
						$fieldsNotTranslated += $field->getStringsNotTranslated();
					}
				}
				$totalFields = $fieldsTranslated + $fieldsQueued + $fieldsChanged + $fieldsNotTranslated;

				$stringsTranslated = $group->getLanguageStringsTranslated();
				$stringsQueued = $group->getLanguageStringsQueuedToBeTranslated();
				$stringsChanged = $group->getLanguageStringsSourceHasChanged();
				$stringsNotTranslated = $group->getLanguageStringsNotTranslated();
				$countLanguageStrings = $stringsTranslated + $stringsQueued + $stringsChanged + $stringsNotTranslated;
				if ($countLanguageStrings !== 0)
				{
					$stringsFile = NenoHelper::getWorkingLanguage() . '.' . $group->getGroupName() . '.ini';
					$countElements++;
				}

				?>

				<tr class="row-group collapsed" data-level="1" data-id="group<?php echo $group->getId(); ?>" data-parent="header">
					<td <?php echo (count($group->getTables()) || $countLanguageStrings)?(' class="cell-expand"><span class="icon-arrow-right-3"></span>'):('>'); ?></td>
					<td class="cell-check"><input type="checkbox"/></td>
					<td colspan="3"><?php echo $group->getGroupName(); ?></td>
					<td<?php echo ($countElements)?(' class="cell-expand"'):(''); ?>><?php echo $countElements ?></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<?php /* @var $table NenoContentElementTable */ ?>
				<?php foreach ($group->getTables() as $table): ?>

					<tr class="row-table collapsed hide" data-level="2" data-id="table<?php echo $table->getId(); ?>" data-parent="group<?php echo $group->getId(); ?>">
						<td></td>
						<td <?php echo (count($table->getFields()))?(' class="cell-expand"><span class="icon-arrow-right-3"></span>'):('>'); ?></td>
						<td class="cell-check"><input type="checkbox"/></td>
						<td colspan="2"><?php echo $table->getTableName(); ?></td>
						<td class="type-icon"><span class="icon-grid-view-2"></span> <?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_TABLE'); ?></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
					<?php /* @var $field NenoContentElementField */ ?>
					<?php foreach ($table->getFields() as $field):
						if (isset($groupTables[$table->getId()][$field->getId()])) {
							$fieldTotalStrings = $groupTables[$table->getId()][$field->getId()]['totalStrings'];
						}
						?>
						<tr class="row-field hide" data-level="3" data-id="field<?php echo $field->getId(); ?>" data-parent="table<?php echo $table->getId(); ?>">
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td><?php echo $field->getFieldName() ?></td>
							<td><?php echo strtoupper($field->getFieldType()) ?></td>
							<td class="translation-progress-bar">
								<?php
								if (!isset($groupTables[$table->getId()][$field->getId()])) {
									$groupTables[$table->getId()][$field->getId()] = array();
								}
								echo NenoHelper::htmlTranslationBar($groupTables[$table->getId()][$field->getId()], $field->isTranslate());
								?>
							</td>
							<td class="toggle-translate">
								<fieldset id="check-toggle-translate-<?php echo $field->getId();?>" class="radio btn-group btn-group-yesno" data-field="<?php echo $field->getId(); ?>">
									<input class="check-toggle-translate-radio" type="radio" id="check-toggle-translate-<?php echo $field->getId();?>-1" name="jform[check-toggle-translate]" value="1" <?php echo ($field->isTranslate())?('checked="checked"'):(''); ?>>
									<label for="check-toggle-translate-<?php echo $field->getId();?>-1" class="btn">Translate</label>
									<input class="check-toggle-translate-radio" type="radio" id="check-toggle-translate-<?php echo $field->getId();?>-0" name="jform[check-toggle-translate]" value="0" <?php echo (!$field->isTranslate())?('checked="checked"'):(''); ?>>
									<label for="check-toggle-translate-<?php echo $field->getId();?>-0" class="btn">Don't translate</label>
								</fieldset>
							</td>
							<td></td>
						</tr>
					<?php endforeach; ?>
				<?php endforeach; ?>
				<?php if($countLanguageStrings !== 0): ?>
					<tr class="row-file collapsed hide" data-level="2" data-id="file<?php echo ''; ?>" data-parent="group<?php echo $group->getId(); ?>">
						<td></td>
						<td></td>
						<td class="cell-check"><input type="checkbox"/></td>
						<td colspan="2"><?php echo $stringsFile; ?></td>
						<td class="type-icon"><span class="icon-file"></span> <?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_FILE'); ?></td>
						<td class="translation-progress-bar">
							<div class="word-count"><?php echo $countLanguageStrings; ?></div>
							<div class="bar">
								<div class="translated" style="width: <?php echo 100*$stringsTranslated/$countLanguageStrings; ?>%" alt="<?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_TRANSLATED'); ?>" title="<?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_TRANSLATED') . ': ' . $stringsTranslated; ?>"></div>
								<div class="queued" style="width: <?php echo 100*$stringsQueued/$countLanguageStrings; ?>%" alt="<?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_QUEUED'); ?>" title="<?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_QUEUED') . ': ' . $stringsQueued; ?>"></div>
								<div class="changed" style="width: <?php echo 100*$stringsChanged/$countLanguageStrings; ?>%" alt="<?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_CHANGED'); ?>" title="<?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_CHANGED') . ': ' . $stringsChanged; ?>"></div>
								<div class="not-translated" style="width: <?php echo 100*$stringsNotTranslated/$countLanguageStrings; ?>%" alt="<?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_NOTTRANSLATED'); ?>" title="<?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_NOTTRANSLATED') . ': ' . $stringsNotTranslated; ?>"></div>
							</div>
						</td>
						<td></td>
						<td></td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>
		</table>
	</div>
</div>


</div>


</div>
