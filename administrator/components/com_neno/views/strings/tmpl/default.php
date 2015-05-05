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
$listOrder     = $this->state->get('list.ordering');
$listDirection = $this->state->get('list.direction');

// Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}

?>

<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<form action="<?php echo JRoute::_('index.php?option=com_neno&view=strings'); ?>" method="post" name="adminForm"
      id="adminForm">
	<div id="j-main-container" class="span10">
		<?php $extraDisplayData = new stdClass; ?>
		<?php $extraDisplayData->groups = $this->groups; ?>
		<?php $extraDisplayData->statuses = $this->statuses; ?>
		<?php $extraDisplayData->methods = $this->methods; ?>
		<?php $extraDisplayData->isOverlay = true; ?>
		<?php $extraDisplayData->modelState = $this->state; ?>
		<?php echo JLayoutHelper::render('stringfilters', array ('view' => $this, 'extraDisplayData' => $extraDisplayData), JPATH_NENO_LAYOUTS); ?>

		<div id="elements-wrapper">
			<?php $displayData = new stdClass; ?>
			<?php $displayData->translations = $this->items; ?>
			<?php $displayData->state = $this->state; ?>
			<?php $displayData->pagination = $this->pagination; ?>
			<?php echo JLayoutHelper::render('strings', $displayData, JPATH_NENO_LAYOUTS); ?>
		</div>
	</div>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirection; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
