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
					jQuery('.translated-content').val(data);
				}
			);
		});

		jQuery('.skip-button').on('click', loadNextTranslation);

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
					console.log(data);
					if (data == 1) {
						alert('Translation Saved');
					}
					loadNextTranslation();
				}
			);
		});
	});
	function loadNextTranslation() {
		var nextString = jQuery('.string-activated').next('div').next('div');
		if (nextString.length) {
			loadTranslation(nextString);
		}
	}
</script>
<style>
	.full-width {
		width: 100%;
		max-width: 100%;
	}

	.original-text,
	.translated-content {
		min-height: 500px;
		padding: 5%;
		width: 90%;
		max-width: 90%;
	}

	.original-text {
		word-break: break-word;
		white-space: normal;
	}

	button .small-text {
		display: block;
		clear: both;
		font-size: 0.75em;
		font-style: italic;
		text-align: left;
		padding-left: 28px;
		line-height: 0.75em;
		margin-bottom: 3px;;
	}

	button .normal-text {
		text-align: left;
		padding-left: 28px;
	}

	.central-buttons {
		/*padding-left: 20px;*/
		width: auto;
		text-align: center;
	}

	.central-buttons button {
		margin-bottom: 15px;
	}

	.right-buttons button {
		margin-left: 7px;
		margin-bottom: 15px;
	}

	.btn-big {
		height: 38px;
	}

	.big-icon {
		position: absolute;
		line-height: 16px;
		margin-top: 0.25em;
		font-size: 1.5em;
	}

	.big-line-height {
		line-height: 1.8em;
	}

	.icon-grey {
		color: #ddd;
		font-size: 5em;
		margin-top: 2.5em;
		width: 90%;
		text-align: center;
	}

	.last-modified {
		color: #ccc;
		font-style: italic;
	}
	.breadcrumbs {
		padding-top: 15px;
		color: #999;
	}
	.breadcrumbs .gt {
		font-size: 10px;
		color: #ccc;
		margin: 0 10px;
	}
</style>

<div>
	<div class="span12">
		<div class="span6 breadcrumbs">
			<?php echo empty($translation) ? '' : implode(' <span class="gt icon-arrow-right"></span>', $translation->breadcrumbs); ?>
		</div>
		<div class="span6 pull-right">
			<div class="pull-right right-buttons">
				<button class="btn btn-big skip-button" type="button"
				        data-id="<?php echo empty($translation) ? '' : $translation->id; ?>">
					<span class="icon-next big-icon"></span>
					<span
						class="normal-text big-line-height"><?php echo JText::_('COM_NENO_EDITOR_SKIP_BUTTON'); ?></span>
				</button>
				<button class="btn btn-big draft-button" type="button"
				        data-id="<?php echo empty($translation) ? '' : $translation->id; ?>">
					<span class="icon-briefcase big-icon"></span>
					<span class="normal-text"><?php echo JText::_('COM_NENO_EDITOR_SAVE_AS_DRAFT_BUTTON'); ?></span>
					<span class="small-text">Ctrl+S</span>
				</button>
				<button class="btn btn-big btn-success save-next-button" type="button"
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
				<?php echo empty($translation) ? '' : NenoHelper::html2text($translation->original_text); ?>
			</div>
			<div class="clearfix"></div>
			<div class="pull-right last-modified">
				<?php echo empty($translation) ? '' : JText::sprintf('COM_NENO_EDITOR_LAST_MODIFIED', $translation->time_added) ?>
			</div>
		</div>
		<div class="central-buttons">
			<div>
				<button class="btn btn-big copy-btn" type="button">
					<span class="icon-copy big-icon"></span>
					<span
						class="normal-text big-line-height"><?php echo JText::_('COM_NENO_EDITOR_COPY_BUTTON'); ?></span>
				</button>
			</div>
			<div class="clearfix"></div>
			<div>
				<button class="btn btn-big translate-btn" type="button">
					<span class="icon-screen big-icon"></span>
					<span class="normal-text big-line-height">
						<?php echo JText::_('COM_NENO_EDITOR_COPY_AND_TRANSLATE_BUTTON'); ?>
					</span>
				</button>
			</div>
			<span class="icon-grey icon-arrow-right-2"></span>
		</div>
		<div class="span5 pull-right">
			<textarea
				class="full-width translated-content"><?php echo empty($translation) ? '' : $translation->string; ?></textarea>

			<div class="clearfix"></div>
			<div class="pull-right last-modified">
				<?php echo empty($translation) ? '' : JText::sprintf('COM_NENO_EDITOR_LAST_MODIFIED', $translation->time_changed !== '0000-00-00 00:00:00' ? $translation->time_changed : JText::_('COM_NENO_EDITOR_NEVER')) ?>
			</div>
		</div>
	</div>
</div>