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

// Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}

$user          = JFactory::getUser();
$userId        = $user->get('id');
$listOrder     = $this->state->get('list.ordering');
$listDirection = $this->state->get('list.direction');
?>

<form action="<?php echo JRoute::_('index.php?option=com_neno&view=settings'); ?>" method="post" name="adminForm"
      id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<table class="table table-striped" id="typeList">
			<tr>
				<th class='left'>
					<?php echo JHtml::_('grid.sort', 'COM_NENO_SETTINGS_KEY', 'a.setting_key', $listDirection, $listOrder); ?>
				</th>
				<th class='left'>
					<?php echo JHtml::_('grid.sort', 'COM_NENO_SETTINGS_VALUE', 'a.setting_value', $listDirection, $listOrder); ?>
				</th>
			</tr>
			<?php foreach ($this->items as $i => $item) : ?>
				<?php $canEdit = $user->authorise('core.edit', 'com_neno'); ?>
				<tr class="row<?php echo $i % 2; ?>">
					<td>
						<?php if ($canEdit) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_neno&task=setting.edit&id=' . (int) $item->id); ?>">
								<?php echo JText::_('COM_NENO_SETTINGS_SETTING_NAME_' . strtoupper($item->setting_key)); ?></a>
						<?php else : ?>
							<?php echo JText::_('COM_NENO_SETTINGS_SETTING_NAME_' . strtoupper($item->setting_key)); ?>
						<?php endif; ?>
					</td>
					<td>
						<?php echo $item->setting_value; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<tr>
				<td colspan="5">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</table>

		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirection; ?>"/>
		<?php echo JHtml::_('form.token'); ?>
		<?php echo JHtml::_('form.token'); ?>

	</div>

</form>


