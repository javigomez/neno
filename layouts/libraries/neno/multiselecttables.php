<?php
/**
 * @package     Neno
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$tables   = $displayData['tables'];
$files    = $displayData['files'];

$isOverlay     = isset($displayData->isOverlay);
$elements      = $displayData['state']->get('filter.element', array ());
$filesSelected = $displayData['state']->get('filter.files', array ());
?>
<?php foreach ($tables as $table): ?>
	<?php $class = !empty($table->fields) ? 'cell-expand' : ''; ?>
	<tr class="row-table element-row collapsed" data-level="2"
	    data-id="table-<?php echo $table->id; ?>"
	    data-parent="group-<?php echo $table->group->id; ?>"
	    data-label="<?php echo $table->table_name; ?>">
		<td></td>
		<td class="<?php echo $class; ?>">
			<?php if (!empty($table->fields)): ?>
				<span class="icon-arrow-right-3"></span>
			<?php endif; ?>
		</td>
		<td class="cell-check"><input
				type="checkbox"
				id="input-table-<?php echo $table->id; ?>"
				<?php echo in_array($table->id, $elements) ? 'checked="checked"' : ''; ?>/>
		</td>
		<td colspan="3"
		    title="<?php echo $table->table_name; ?>">
			<label for="input-table-<?php echo $table->id; ?>">
				<?php echo $table->table_name; ?>
			</label>
		</td>
	</tr>
	<?php foreach ($table->fields as $field): ?>
		<tr class="row-field element-row hide" data-level="3" data-id="field-<?php echo $field->id; ?>"
		    data-parent="table-<?php echo $table->id; ?>"
		    data-label="<?php echo $field->field_name; ?>">
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td class="cell-check">
				<input id="input-field-<?php echo $field->id; ?>" type="checkbox"/>
			</td>
			<td title="<?php echo $field->field_name; ?>">
				<label for="input-field-<?php echo $field->id; ?>">
					<?php echo $field->field_name; ?>
				</label>
			</td>
		</tr>
	<?php endforeach; ?>
<?php endforeach; ?>
<?php foreach ($files as $file): ?>
	<tr class="row-table element-row collapsed" data-level="2"
	    data-id="file-<?php echo $file->id; ?>"
	    data-parent="group-<?php echo $file->group->id; ?>"
	    data-label="<?php echo $file->filename; ?>">
		<td></td>
		<td></td>
		<td class="cell-check"><input
				type="checkbox" <?php echo in_array($file->id, $filesSelected) ? 'checked="checked"' : ''; ?>/>
		</td>
		<td colspan="3"
		    title="<?php echo $file->filename; ?>"><?php echo $file->filename; ?></td>
	</tr>
<?php endforeach; ?>