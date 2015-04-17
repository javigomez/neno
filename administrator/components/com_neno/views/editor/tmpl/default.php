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

/*$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . '/media/neno/css/editorstrings.css');*/

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

// Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}

?>

<div id="j-sidebar-container" class="span2">
	<form action="<?php echo JRoute::_('index.php?option=com_neno&view=editor'); ?>" method="post"
	      name="adminForm" id="adminForm">
		<?php $extraDisplayData = new stdClass; ?>
		<?php $extraDisplayData->groups = $this->groups; ?>
		<?php echo JLayoutHelper::render('editorfilters', array('view' => $this, 'extraDisplayData' => $extraDisplayData), JPATH_NENO_LAYOUTS); ?>
		<input type="hidden" name="limitstart" id="limitstart" value="0"/>
		<input type="hidden" name="list_limit" id="list_limit" value="20"/>
	</form>
	<div id="filter-tags-wrapper"></div>
	<div id="results-wrapper">
			<span id="editor-strings-title">
				Search results:
			</span>

		<div id="elements-wrapper">
			<?php echo JLayoutHelper::render('editorStrings', $this->items, JPATH_NENO_LAYOUTS); ?>
		</div>
	</div>
</div>


<div id="j-main-container" class="span10">
	<div id="editor-wrapper">
		<?php echo JLayoutHelper::render('editor', null, JPATH_NENO_LAYOUTS); ?>
	</div>
</div>
</form>
