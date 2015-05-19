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

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

// Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}

$user          = JFactory::getUser();
$userId        = $user->get('id');
?>

<script>
	jQuery(document).ready(function () {
		jQuery('select').on('change', saveSetting);
		jQuery(".input-setting").on('blur', saveSetting);
		jQuery('fieldset.radio').on('change', saveSetting);
	});

	function saveSetting() {
		var element = jQuery(this);
		var setting = '';
		var value = '';
		if (element.prop('tagName') == 'SELECT') {
			setting = element.prop('name');
			value = element.find(':selected').val();
		}
		else {
			if (element.prop('tagName') == 'FIELDSET') {
				value = element.find(':checked').val();
				setting = element.find(':checked').prop('name');
			}
			else {
				setting = element.prop('name');
				value = element.val();
			}
		}
		jQuery.ajax({
			url: 'index.php?option=com_neno&task=settings.saveSetting',
			type: 'POST',
			data: {
				setting: setting,
				value: value
			},
			success: function (response) {
				if (response == 'ok') {
					element.parent().append('<span class="icon-checkmark"></span>');
					setTimeout(function () {
						jQuery('.icon-checkmark').hide('slow');
					}, 2000);
				}
				else {
					element.parent().append('<span class="icon-remove"></span>');
					setTimeout(function () {
						jQuery('.icon-remove').hide('slow');
					}, 2000);
				}
			}
		});
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_neno&view=settings'); ?>" method="post" name="adminForm"
      id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<table class="table table-striped" id="typeList">
			<tr>
				<th class='left'>
					<?php echo JText::_('COM_NENO_SETTINGS_KEY'); ?>
				</th>
				<th class='left'>
					<?php echo JText::_('COM_NENO_SETTINGS_VALUE'); ?>
				</th>
			</tr>
			<?php foreach ($this->items as $i => $item) : ?>
				<?php $canEdit = $user->authorise('core.edit', 'com_neno') && $item->read_only == 0; ?>
				<tr class="row<?php echo $i % 2; ?>">
					<td>
						<?php echo JText::_('COM_NENO_SETTINGS_SETTING_NAME_' . strtoupper($item->setting_key)); ?>
					</td>
					<td>
						<?php if (isset($item->dropdown)): ?>
							<?php echo $item->dropdown; ?>
						<?php elseif (is_numeric($item->setting_value) && ($item->setting_value == 1 || $item->setting_value == 0)): ?>
							<fieldset id="<?php echo $item->setting_key; ?>" class="radio btn-group btn-group-yesno">
								<input type="radio" id="<?php echo $item->setting_key; ?>0"
								       name="<?php echo $item->setting_key; ?>" value="1"
									<?php echo ($item->setting_value) ? 'checked="checked"' : ''; ?>>
								<label for="<?php echo $item->setting_key; ?>0" class="btn">
									<?php echo JText::_('JYES'); ?>
								</label>
								<input type="radio" id="<?php echo $item->setting_key; ?>1"
								       name="<?php echo $item->setting_key; ?>" value="0"
									<?php echo ($item->setting_value) ? '' : 'checked="checked"'; ?>>
								<label for="<?php echo $item->setting_key; ?>1" class="btn">
									<?php echo JText::_('JNO'); ?>
								</label>
							</fieldset>
						<?php else: ?>
							<?php if ($canEdit): ?>
								<input type="text" name="<?php echo $item->setting_key; ?>"
								       class="input-setting input-xxlarge"
								       value="<?php echo $item->setting_value; ?>"/>
							<?php else: ?>
								<span class="input-xxlarge uneditable-input"><?php echo $item->setting_value; ?></span>
							<?php endif; ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>

		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<?php echo JHtml::_('form.token'); ?>

	</div>

</form>


