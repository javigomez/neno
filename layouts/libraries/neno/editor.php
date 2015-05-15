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

$document    = JFactory::getDocument();
$translation = $displayData;

?>
<script>
	jQuery(document).ready(function () {
		jQuery('#copy-btn').on('click', function () {
			jQuery('.translated-content').val(jQuery('.original-text').html().trim());
			jQuery('.translated-by').hide();
		});

		jQuery('#translate-btn').on('click', translate);

		jQuery('#skip-button').on('click', loadNextTranslation);

		jQuery('#draft-button').on('click', saveDraft);

		jQuery('#save-next-button').on('click', saveTranslationAndNext);

		var action = jQuery('#default_translate_action').val();
		if (action == '1') {
			jQuery('.translated-content').val(jQuery('.original-text').html().trim());
			jQuery('.translated-by').hide();
		} else if (action == '2') {
			translate();
		}
	});
</script>

<div>
	<div class="span12">
		<div class="span6 breadcrumbs">
			<?php echo empty($translation) ? '' : implode(' <span class="gt icon-arrow-right"></span>', $translation->breadcrumbs); ?>
		</div>
		<div class="span6 pull-right">
			<div class="pull-right right-buttons">
				<button id="skip-button" class="btn btn-big" type="button"
				        data-id="<?php echo empty($translation) ? '' : $translation->id; ?>">
					<span class="icon-next big-icon"></span>
					<span
						class="normal-text big-line-height"><?php echo JText::_('COM_NENO_EDITOR_SKIP_BUTTON'); ?></span>
				</button>
				<button id="draft-button" class="btn btn-big" type="button"
				        data-id="<?php echo empty($translation) ? '' : $translation->id; ?>">
					<span class="icon-briefcase big-icon"></span>
					<span class="normal-text"><?php echo JText::_('COM_NENO_EDITOR_SAVE_AS_DRAFT_BUTTON'); ?></span>
					<span class="small-text">Ctrl+S</span>
				</button>
				<button id="save-next-button" class="btn btn-big btn-success" type="button"
				        data-id="<?php echo empty($translation) ? '' : $translation->id; ?>">
					<span class="icon-checkmark big-icon"></span>
					<span class="normal-text"><?php echo JText::_('COM_NENO_EDITOR_SAVE_AND_NEXT_BUTTON'); ?></span>
					<span class="small-text">Ctrl+Enter</span>
				</button>
			</div>
		</div>
	</div>
</div>
<div>
	<div>
		<div class="span5">
			<div class="uneditable-input full-width original-text">
				<?php echo empty($translation) ? '' : NenoHelper::highlightHTMLTags(NenoHelper::html2text($translation->original_text)); ?>
			</div>
			<div class="clearfix"></div>
			<div class="pull-right last-modified">
				<?php echo empty($translation) ? '' : JText::sprintf('COM_NENO_EDITOR_LAST_MODIFIED', $translation->time_added) ?>
			</div>
		</div>
		<div class="central-buttons">
			<div>
				<button id="copy-btn" class="btn btn-big" type="button">
					<span class="icon-copy big-icon"></span>
					<span
						class="normal-text big-line-height"><?php echo JText::_('COM_NENO_EDITOR_COPY_BUTTON'); ?></span>
				</button>
			</div>
			<div class="clearfix"></div>
			<div>
				<button id="translate-btn" class="btn btn-big" type="button">
					<span class="icon-screen big-icon"></span>
					<span class="normal-text big-line-height">
						<?php echo JText::_('COM_NENO_EDITOR_COPY_AND_TRANSLATE_BUTTON'); ?>
					</span>
				</button>
			</div>
			<span class="icon-grey icon-arrow-right-2"></span>
		</div>
		<div class="span5 pull-right">
			<textarea class="full-width translated-content"></textarea>
			<div class="clearfix"></div>
			<div class="pull-left translated-by">
				<?php echo JText::sprintf('COM_NENO_EDITOR_TRANSLATED_BY', NenoSettings::get('translator')); ?>
			</div>
			<div class="pull-right last-modified">
				<?php echo empty($translation) ? '' : JText::sprintf('COM_NENO_EDITOR_LAST_MODIFIED', $translation->time_changed !== '0000-00-00 00:00:00' ? $translation->time_changed : JText::_('COM_NENO_EDITOR_NEVER')) ?>
			</div>
		</div>
	</div>
</div>