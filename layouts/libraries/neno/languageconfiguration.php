<?php

/**
 * @package     Neno
 * @subpackage  Helpers
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('JPATH_NENO') or die;

$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . '/media/neno/css/languageconfiguration.css');

$item               = (array) $displayData;
$translationMethods = NenoHelper::loadTranslationMethods();
$n                  = 0;
?>

<div class="language-wrapper language-<?php echo $item['placement']; ?>">
	<?php if (!empty($item['errors'])): ?>
		<div class="alert alert-error">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<?php foreach ($item['errors'] as $itemError): ?>
				<span><?php echo $itemError; ?></span><br/>
			<?php endforeach; ?>
		</div>
	<?php elseif (!empty($item['orderText']) && $item['placement'] == 'dashboard'): ?>
		<div class="alert alert-info">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<span><?php echo $item['orderText']; ?></span>
			<a href="<?php echo $item['orderLink']; ?>"
			   class="btn btn-primary"><?php echo JText::_('COM_NENO_DASHBOARD_ORDER_BUTTON'); ?></a>
		</div>
	<?php endif; ?>
	<h4>
		<?php
		if (file_exists(JPATH_SITE . '/media/mod_languages/images/' . $item['image'] . '.gif')): ?>
			<img src="<?php echo JUri::root() . 'media/mod_languages/images/' . $item['image'] . '.gif'; ?>"/>
		<?php endif; ?>
		<?php echo $item['title']; ?>
	</h4>
	<?php if ($item['placement'] == 'dashboard'): ?>
		<?php echo NenoHelper::renderWordCountProgressBar($item['wordCount'], true, true) ?>
		<a class="btn <?php echo $item['isInstalled'] == false ? 'not-ready' : ''; ?>"
		   href="<?php echo JRoute::_('index.php?option=com_neno&task=setWorkingLang&lang=' . $item['lang_code'] . '&next=editor'); ?>">
			<?php echo JText::_('COM_NENO_DASHBOARD_TRANSLATE_BUTTON'); ?>
		</a>
	<?php endif; ?>
	<?php if ($item['placement'] == 'dashboard'): ?>
		<button class="btn configuration-button" type="button">
			<?php echo JText::_('COM_NENO_DASHBOARD_CONFIGURATION_BUTTON'); ?>
		</button>
		<div class="clearfix"></div>
	<?php endif; ?>

	<div class="language-configuration">
		<span class="link-ge">
			&nbsp;
			<?php if ($item['placement'] == 'dashboard'): ?>
				<?php echo JText::sprintf('COM_NENO_DASHBOARD_GROUPS_ELEMENTS_LINK', JRoute::_('index.php?option=com_neno&task=setWorkingLang&lang=' . $item['lang_code'] . '&next=groupselements')); ?>
			<?php endif; ?>
		</span>

		<div class="language-configuration-controls">
			<button class="btn remove-language-button <?php echo empty($item['errors']) ? '' : 'disabled'; ?>"
			        title="<?php echo empty($item['errors']) ? '' : JText::_('COM_NENO_DASHBOARD_REMOVE_DISABLED'); ?>"
			        data-language="<?php echo $item['lang_code']; ?>"
			        type="button">
				<span class="icon-trash"></span>
				<?php echo JText::_('COM_NENO_DASHBOARD_REMOVE_BUTTON'); ?>
			</button>
			<fieldset id="jform_published_<?php echo $item['lang_code']; ?>"
			          class="radio btn-group btn-group-yesno"
			          data-language="<?php echo $item['lang_code']; ?>">
				<input type="radio" id="jform_published_<?php echo $item['lang_code']; ?>0"
				       name="jform[published]" value="1"
					<?php echo ($item['published']) ? 'checked="checked"' : ''; ?>>
				<label for="jform_published_<?php echo $item['lang_code']; ?>0" class="btn">
					<?php echo JText::_('JPUBLISHED'); ?>
				</label>
				<input type="radio" id="jform_published_<?php echo $item['lang_code']; ?>1"
				       name="jform[published]" value="0"
					<?php echo ($item['published']) ? '' : 'checked="checked"'; ?>>
				<label for="jform_published_<?php echo $item['lang_code']; ?>1" class="btn">
					<?php echo JText::_('JUNPUBLISHED'); ?>
				</label>
			</fieldset>
			<div class="method-selectors" data-language="<?php echo $item['lang_code']; ?>">
				<?php $displayData = array (); ?>
				<?php $displayData['n'] = $n; ?>
				<?php $displayData['assigned_translation_methods'] = $item['translationMethods']; ?>
				<?php $displayData['translation_methods'] = $translationMethods; ?>
				<?php echo JLayoutHelper::render('translationmethodselector', $displayData, JPATH_NENO_LAYOUTS); ?>
			</div>
		</div>
	</div>
</div>