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
		max-width: 150px;
	}

</style>

<script type="text/javascript">

	function toggleCollapseRow (row) {
		var rowType = '';
		var childRowType = '';
		if (row.hasClass('row-group')) {
			rowType = 'group';
			childRowType = 'table';
		} else if (row.hasClass('row-table')) {
			rowType = 'table';
			childRowType = 'field';
		} else {
			rowType = 'element'; // Default
		}
		var nextRow = row.next('tr');
		while (nextRow.length!=0 && !nextRow.hasClass('row-'+rowType)) {
			if (nextRow.hasClass('row-'+childRowType)) {
				nextRow.toggleClass('hide');
				if (childRowType == 'table' && row.hasClass('expanded') && nextRow.hasClass('expanded')) {
					toggleCollapseRow(nextRow);
				}
			}
			nextRow = nextRow.next('tr');
		}
		if (rowType != 'element') {
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
		<?php /* @var $group NenoContentElementGroup */ ?>
		<table class="table table-striped table-groups-elements" id="table-groups-elements">
			<tr class="row-header" data-level="0" data-id="header">
				<th></th>
				<th class="cell-check"><input type="checkbox"/></th>
				<th colspan="3" class="group-label"><?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_GROUPS'); ?></th>
				<th class="table-groups-elements-label"><?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_ELEMENTS'); ?></th>
				<th class="table-groups-elements-label"><?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_COUNT'); ?></th>
				<th class="table-groups-elements-label"><?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_METHODS'); ?></th>
			</tr>
			<?php foreach ($this->items as $group):


				$languageStrings = $group->getLanguageStrings();
				var_dump($languageStrings);

				if (count($languageStrings)!==0)
				{
					$translations = $languageStrings[0]->getTranslations();
					if ($translations)
					{
						var_dump($translations);
					}
				}


				?>

				<tr class="row-group collapsed" data-level="1" data-id="group<?php echo $group->getId(); ?>" data-parent="header">
					<td <?php echo (count($group->getTables()))?(' class="cell-expand"><span class="icon-arrow-right-3"></span>'):('>'); ?></td>
					<td class="cell-check"><input type="checkbox"/></td>
					<td colspan="3"><?php echo $group->getGroupName(); ?></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<?php /* @var $table NenoContentElementTable */ ?>
				<?php foreach ($group->getTables() as $table):
					//var_dump($table);
					?>

					<tr class="row-table collapsed hide" data-level="2" data-id="table<?php echo $table->getId(); ?>" data-parent="group<?php echo $group->getId(); ?>">
						<td></td>
						<td <?php echo (count($table->getFields()))?(' class="cell-expand"><span class="icon-arrow-right-3"></span>'):('>'); ?></td>
						<td class="cell-check"><input type="checkbox"/></td>
						<td colspan="2"><?php echo $table->getTableName(); ?></td>
						<td><span class="icon-grid-view-2"></span> <?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_TABLE'); ?></td>
						<td></td>
						<td></td>
					</tr>
					<?php /* @var $field NenoContentElementField */ ?>
					<?php foreach ($table->getFields() as $field): ?>
						<tr class="row-field hide" data-level="3" data-id="field<?php echo $field->getId(); ?>" data-parent="table<?php echo $table->getId(); ?>">
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td><?php echo $field->getFieldName() ?></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
					<?php endforeach; ?>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</table>
	</div>
</div>


</div>


</div>
