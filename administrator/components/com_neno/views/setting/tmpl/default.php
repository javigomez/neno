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

JHtml::_('behavior.tooltip');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');
?>

<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		Joomla.submitform(task, document.getElementById('setting-form'));
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_neno&id=' . (int) $this->item->id); ?>"
      method="post" enctype="multipart/form-data" name="adminForm2" id="setting-form">
	<div class="row-fluid">
		<div class="span8 form-horizontal">
			<fieldset class="adminform">
				<?php echo $this->form->getInput('id'); ?>
				<?php echo $this->form->getInput('setting_key'); ?>
				<div class="control-group">
					<div class="control-label">
						<?php echo JText::_('COM_NENO_SETTINGS_SETTING_NAME_' . strtoupper($this->item->setting_key)) ?>
					</div>
					<div class="controls">
						<?php if ($this->item->setting_key === 'translator'): ?>
							<?php echo $this->item->translator_list; ?>
						<?php elseif ($this->item->setting_key === 'license_code'): ?>
							<textarea name="jform[setting_value]"><?php echo $this->item->setting_value; ?></textarea>
						<?php elseif ($this->item->setting_key === 'default_translate_action'): ?>
							<select name="jform[setting_value]" id="default_translate_action">
								<option value="0"><?php echo JText::_('COM_NENO_SETTINGS_SETTING_OPTION_DEFAULT_TRANSLATE_ACTION_NO'); ?></option>
								<option value="1"><?php echo JText::_('COM_NENO_SETTINGS_SETTING_OPTION_DEFAULT_TRANSLATE_ACTION_COPY'); ?></option>
								<option value="2"><?php echo JText::_('COM_NENO_SETTINGS_SETTING_OPTION_DEFAULT_TRANSLATE_ACTION_TRANSLATE'); ?></option>
							</select>
						<?php else: ?>
							<input type="text" name="jform[setting_value]"
							       value="<?php echo $this->item->setting_value; ?>"/>
						<?php endif; ?>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="setting.save"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
