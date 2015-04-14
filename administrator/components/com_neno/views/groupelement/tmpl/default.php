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
?>

<form action="<?php echo JRoute::_('index.php?option=com_neno&id=' . (int) $this->item->id); ?>"
      method="post" enctype="multipart/form-data" name="adminForm2" id="groupelement-form">
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<fieldset class="adminform">
				<?php echo $this->form->getInput('id'); ?>

				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('group_name'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('group_name'); ?></div>
				</div>

				<label>
					<?php echo JText::_('COM_NENO_GROUPELEMENT_GROUP_TRANSLATION_METHOD'); ?>
				</label>

				<div class="control-group">
					<?php echo $this->form->getInput('translation_method_1'); ?>
				</div>
				<div class="control-group">
					<?php echo $this->form->getInput('translation_method_2'); ?>
				</div>
				<div class="control-group">
					<?php echo $this->form->getInput('translation_method_3'); ?>
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="task" value="groupelement.save"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
