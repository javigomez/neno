<?php
/**
 * @package     Lingo
 * @subpackage  Views
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_lingo/assets/css/lingo.css');
?>
<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function () {

	});

	Joomla.submitbutton = function (task) {
		if (task == 'translation.cancel') {
			Joomla.submitform(task, document.getElementById('translation-form'));
		}
		else {

			if (task != 'translation.cancel' && document.formvalidator.isValid(document.id('translation-form'))) {

				Joomla.submitform(task, document.getElementById('translation-form'));
			}
			else {
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_lingo&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="translation-form" class="form-validate">

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array( 'active' => 'general' )); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_LINGO_TITLE_TRANSLATION', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset class="adminform">

					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('source_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('source_id'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('time_translated'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('time_translated'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('version'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('version'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('lang'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('lang'); ?></div>
					</div>

				</fieldset>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>



		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>

	</div>
</form>
