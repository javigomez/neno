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
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'groupelement.cancel') {
			Joomla.submitform(task, document.getElementById('groupelement-form'));
		}
		else {
			if (task != 'groupelement.cancel' && document.formvalidator.isValid(document.id('groupelement-form'))) {

				Joomla.submitform(task, document.getElementById('groupelement-form'));
			}
			else {
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_neno&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" enctype="multipart/form-data" name="adminForm" id="groupelement-form" class="form-validate">
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<fieldset class="adminform">
				<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>"/>

				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('group_name'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('group_name'); ?></div>
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="task" value=""/>
	<?php echo JHtml::_('form.token'); ?>
</form>
