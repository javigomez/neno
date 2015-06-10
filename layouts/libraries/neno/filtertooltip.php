<?php
/**
 * @package    Neno
 *
 * @author     Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright  Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

?>

<style>
	.filter-list {
		list-style-type: none;
	}

	.filter-list li {
		padding: 5px 0;
	}
</style>
<ul class="filter-list">
	<li>
		<span class="label label-info">INT</span>
		<?php echo JText::_('COM_NENO_GROUPS_ELEMENTS_FILTER_HELPER_TEXT_INT'); ?>
	</li>
	<li>
		<span class="label label-info">UINT</span>
		<?php echo JText::_('COM_NENO_GROUPS_ELEMENTS_FILTER_HELPER_TEXT_UINT'); ?>
	</li>
	<li>
		<span class="label label-info">FLOAT</span>
		<?php echo JText::_('COM_NENO_GROUPS_ELEMENTS_FILTER_HELPER_TEXT_FLOAT'); ?>
	</li>
	<li>
		<span class="label label-info">BOOL</span>
		<?php echo JText::_('COM_NENO_GROUPS_ELEMENTS_FILTER_HELPER_TEXT_BOOL'); ?>
	</li>
	<li>
		<span class="label label-info">WORD</span>
		<?php echo JText::_('COM_NENO_GROUPS_ELEMENTS_FILTER_HELPER_TEXT_WORD'); ?>
	</li>
	<li>
		<span class="label label-info">ALNUM</span>
		<?php echo JText::_('COM_NENO_GROUPS_ELEMENTS_FILTER_HELPER_TEXT_ALNUM'); ?>
	</li>
	<li>
		<span class="label label-info">CMD</span>
		<?php echo JText::_('COM_NENO_GROUPS_ELEMENTS_FILTER_HELPER_TEXT_CMD'); ?>
	</li>
	<li>
		<span class="label label-info">STRING</span>
		<?php echo JText::_('COM_NENO_GROUPS_ELEMENTS_FILTER_HELPER_TEXT_STRING'); ?>
	</li>
	<li>
		<span class="label label-info">HTML</span>
		<?php echo JText::_('COM_NENO_GROUPS_ELEMENTS_FILTER_HELPER_TEXT_HTML'); ?>
	</li>
	<li>
		<span class="label label-info">ARRAY</span>
		<?php echo JText::_('COM_NENO_GROUPS_ELEMENTS_FILTER_HELPER_TEXT_ARRAY'); ?>
	</li>
	<li>
		<span class="label label-info">TRIM</span>
		<?php echo JText::_('COM_NENO_GROUPS_ELEMENTS_FILTER_HELPER_TEXT_TRIM'); ?>
	</li>
	<li>
		<span class="label label-info">PATH</span>
		<?php echo JText::_('COM_NENO_GROUPS_ELEMENTS_FILTER_HELPER_TEXT_PATH'); ?>
	</li>
	<li>
		<span class="label label-info">USERNAME</span>
		<?php echo JText::_('COM_NENO_GROUPS_ELEMENTS_FILTER_HELPER_TEXT_USERNAME'); ?>
	</li>
	<li>
		<span class="label label-info">RAW</span>
		<?php echo JText::_('COM_NENO_GROUPS_ELEMENTS_FILTER_HELPER_TEXT_RAW'); ?>
	</li>
</ul>