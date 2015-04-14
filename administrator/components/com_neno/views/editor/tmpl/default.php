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

<?php if (!empty($this->sidebar) && 0): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
<form action="<?php /** @noinspection PhpToStringImplementationInspection */
echo JRoute::_('index.php?option=com_neno&view=strings'); ?>" method="post" name="adminForm" id="adminForm">
<div id="j-main-container" class="span10">
	<?php else : ?>

	<div id="j-sidebar-container" class="span2">
		<form action="<?php echo JRoute::_('index.php?option=com_neno&view=editor'); ?>" method="post" name="adminForm" id="adminForm">
		<?php
		// Search tools bar
		//echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		$extraDisplayData = new stdClass();
		$extraDisplayData->groups = $this->groups;
		echo JLayoutHelper::render('editorfilters', array('view' => $this, 'extraDisplayData' => $extraDisplayData), JPATH_NENO_LAYOUTS);
		?>
			<input type="hidden" name="limitstart" id="limitstart" value="0" />
			<input type="hidden" name="list_limit" id="list_limit" value="20" />
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
		<?php echo $this->pagination->getListFooter(); ?>
	</div>


	<div id="j-main-container">
		<!--
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('JSEARCH_FILTER');?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button class="btn hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button class="btn hasTooltip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
		</div>
		-->
		<?php endif;
		//Kint::dump(count($this->items));
		//Kint::dump($this->items[0]);
		//Kint::dump($this->items[0]->getSourceElementData());
		?>

		<div id="editor-wrapper">
		<!-- <?php //echo JLayoutHelper::render('strings', $this->items, JPATH_NENO_LAYOUTS);	?> -->
		</div>

	</div>


</div>


