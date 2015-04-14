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
		jQuery('.copy-btn').on('click', function () {
			jQuery('.translate-content').val(jQuery('.original-text').html().trim());
		});

		jQuery('.translate-btn').on('click', function () {
			var text = jQuery('.original-text').html().trim();
			jQuery.post(
				'index.php?option=com_neno&task=editor.translate',
				{text: text}
				, function (data) {
					jQuery('.translate-content').val(data);
				}
			);
		});

		jQuery('.draft-button').on('click', function () {
			var text = jQuery('.translate-content').val();
			var translationId = jQuery(this).data('id');
			jQuery.post(
				'index.php?option=com_neno&task=editor.saveAsDraft',
				{
					id: translationId,
					text: text
				}
				, function (data) {
					if (data == 1) {
						alert('Translation Saved');
					}
				}
			);
		});

		jQuery('.save-next-button').on('click', function () {
			var text = jQuery('.translate-content').val();
			var translationId = jQuery(this).data('id');
			jQuery.post(
				'index.php?option=com_neno&task=editor.saveAsCompleted',
				{
					id: translationId,
					text: text
				}
				, function (data) {
					if (data == 1) {
						alert('Translation Saved');
					}
				}
			);
		});
	});
</script>
<div class="row">
	<div class="span12">
		<div class="span6">
			<?php echo empty($translation) ? '' : implode(' > ', $translation->breadcrumbs); ?>
		</div>
		<div class="span6 pull-right">
			<div class="pull-right">
				<button class="btn skip-button" type="button" data-id="<?php echo empty($translation) ? '' : $translation->id; ?>">
					<span class="icon-next"></span><?php echo JText::_('COM_NENO_EDITOR_SKIP_BUTTON'); ?>
				</button>
				<button class="btn draft-button" type="button" data-id="<?php echo empty($translation) ? '' : $translation->id; ?>">
								<span
									class="icon-file"></span><?php echo JText::_('COM_NENO_EDITOR_SAVE_AS_DRAFT_BUTTON'); ?>
				</button>
				<button class="btn btn-success save-next-button" type="button" data-id="<?php echo empty($translation) ? '' : $translation->id; ?>">
							<span
								class="icon-checkmark"></span><?php echo JText::_('COM_NENO_EDITOR_SAVE_AND_NEXT_BUTTON'); ?>
				</button>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div>
		<div class="span5">
			<div class="uneditable-input full-width original-text">
				<?php echo empty($translation) ? '' : $translation->original_text; ?>
			</div>
		</div>
		<div class="span2 full-width">
			<div class="span12">
				<button class="btn copy-btn" type="button">
					<span class="icon-copy"></span><?php echo JText::_('COM_NENO_EDITOR_COPY_BUTTON'); ?>
				</button>
			</div>
			<div class="span12">
				<button class="btn translate-btn" type="button">
					<span
						class="icon-screen"></span><?php echo JText::_('COM_NENO_EDITOR_COPY_AND_TRANSLATE_BUTTON'); ?>
				</button>
			</div>

		</div>
		<div class="span5">
			<textarea
				class="full-width translate-content"><?php echo empty($translation) ? '' : $translation->string; ?></textarea>
		</div>
	</div>
</div>