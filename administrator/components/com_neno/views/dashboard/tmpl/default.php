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

//Include the CSS file
JHtml::stylesheet('media/neno/css/admin.css');

// Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}

$workingLanguage = NenoHelper::getWorkingLanguage();

?>

<style>

	.language-configuration {
		display: none;
	}

</style>

<script type="text/javascript">

	jQuery(document).ready(function () {

		jQuery('.configuration-button').on('click', function () {
			jQuery(this).siblings('.language-configuration').slideToggle();
		});

		jQuery(".radio").on('change', function () {
			jQuery.ajax({
				beforeSend: onBeforeAjax,
				url: 'index.php?option=com_neno&task=dashboard.toggleLanguage&language=' + jQuery(this).data('language')
			});
		})

	});
</script>


<form action="<?php echo JRoute::_('index.php?option=com_neno&view=groupselements'); ?>" method="post" name="adminForm"
      id="adminForm">

	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<div class="languages-holder">
			<?php foreach ($this->items as $item): ?>
				<div class="language-wrapper">
					<h3>
						<img
							src="<?php echo JUri::root() . 'media/mod_languages/images/' . $item->image . '.gif'; ?>"/>
						<?php echo $item->title; ?>
					</h3>
					<?php echo NenoHelper::renderWordCountProgressBar($item->wordCount, true, true) ?>
					<a class="btn btn-primary"
					   href="<?php echo JRoute::_('index.php?option=com_neno&task=setWorkingLang&lang=' . $item->lang_code . '&next=editor'); ?>">
						<?php echo JText::_('COM_NENO_DASHBOARD_TRANSLATE_BUTTON'); ?>
					</a>
					<button class="btn configuration-button" type="button">
						<?php echo JText::_('COM_NENO_DASHBOARD_CONFIGURATION_BUTTON'); ?>
					</button>
					<div class="language-configuration">
						<?php echo JText::sprintf('COM_NENO_DASHBOARD_GROUPS_ELEMENTS_LINK', JRoute::_('index.php?option=com_neno&task=setWorkingLang&lang=' . $item->lang_code . '&next=groupselements')); ?>
						<fieldset id="jform_published_<?php echo $item->lang_code; ?>" class="radio btn-group btn-group-yesno"
						          data-language="<?php echo $item->lang_code; ?>">
							<input type="radio" id="jform_published_<?php echo $item->lang_code; ?>0" name="jform[published]" value="1"
								<?php echo ($item->published) ? 'checked="checked"' : ''; ?>>
							<label for="jform_published_<?php echo $item->lang_code; ?>0" class="btn">
								<?php echo JText::_('JPUBLISHED'); ?>
							</label>
							<input type="radio" id="jform_published_<?php echo $item->lang_code; ?>1" name="jform[published]" value="0"
								<?php echo ($item->published) ? '' : 'checked="checked"'; ?>>
							<label for="jform_published_<?php echo $item->lang_code; ?>1" class="btn">
								<?php echo JText::_('JUNPUBLISHED'); ?>
							</label>
						</fieldset>
						<button class="btn btn-small" type="button">
							<span class="icon-trash"></span> <?php echo JText::_('COM_NENO_DASHBOARD_REMOVE_BUTTON'); ?>
						</button>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</form>


