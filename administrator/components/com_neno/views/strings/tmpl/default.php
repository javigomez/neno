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

?>

<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<form action="<?php echo JRoute::_('index.php?option=com_neno&view=strings'); ?>" method="post" name="adminForm"
      id="adminForm">
	<div id="j-main-container" class="span10">
		<?php $extraDisplayData = new stdClass; ?>
		<?php $extraDisplayData->groups = $this->groups; ?>
		<?php $extraDisplayData->isOverlay = true; ?>
		<?php echo JLayoutHelper::render('stringfilters', array ('view' => $this, 'extraDisplayData' => $extraDisplayData), JPATH_NENO_LAYOUTS); ?>

		<div id="elements-wrapper">
			<?php echo JLayoutHelper::render('strings', $this->items, JPATH_NENO_LAYOUTS); ?>
		</div>
		<?php echo $this->pagination->getListFooter(); ?>
	</div>
</form>
