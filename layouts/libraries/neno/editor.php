<?php
/**
 * @package     Neno
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

$document    = JFactory::getDocument();
$translation = $displayData;

?>
<script>
	jQuery(document).ready(function () {
		jQuery('#copy-btn').off('click').on('click', copyOriginal);

		<?php if (NenoSettings::get('translator') == '' || NenoSettings::get('translator_api_key') == ''): ?>

		askForTranslatorAPIKey();

		jQuery('body').on('keydown', function (e) {
			var ev = e || window.event;

			// Ctrl+Shift→
			if (ev.keyCode == 39 && e.ctrlKey && e.shiftKey) {
				ev.preventDefault();
				jQuery('#translatorKeyModal').modal('show');
			}
		});

		<?php else: ?>

		jQuery('#translate-btn').off('click').on('click', translate);

		jQuery('body').on('keydown', function (e) {
			var ev = e || window.event;

			// Ctrl+Shift→
			if (ev.keyCode == 39 && e.ctrlKey && e.shiftKey) {
				ev.preventDefault();
				translate();
			}
		});

		<?php endif; ?>

		jQuery('#skip-button').off('click').on('click', loadNextTranslation);

		jQuery('#draft-button').off('click').on('click', saveDraft);

		jQuery('#save-next-button').off('click').on('click', saveTranslationAndNext);

		var action = jQuery('#default_translate_action').val();
		if (action == '1') {
			copyOriginal();
		}
			<?php if (NenoSettings::get('translator') != '' && NenoSettings::get('translator_api_key') != ''): ?>
		else if (action == '2') {
			translate();
		}
		<?php endif; ?>

		//jQuery('#dont-translate').tooltip();
		jQuery('#dont-translate').off().on('click', changeFieldTranslateState);
	});

	function changeFieldTranslateState() {

		var id = jQuery(this).attr('data-id');

		jQuery.ajax({
				beforeSend: onBeforeAjax,
				url: 'index.php?option=com_neno&task=groupselements.toggleContentElementField&fieldId=' + id + '&translateStatus=0',
				success: function () {
					window.location.reload();
				}
			}
		);
	}

</script>

<div>
	<div class="span12">
		<div class="pull-left breadcrumbs">
			<?php echo empty($translation) ? '' : implode(' <span class="gt icon-arrow-right"></span>', $translation->breadcrumbs); ?>
			<?php if (!empty($translation->breadcrumbs)): ?>
				&nbsp;&nbsp;
				<a id="dont-translate"
				   data-toggle="tooltip"
				   class="hasTooltip"
				   href="javascript:void(0);"
				   title="<?php echo JHtml::tooltipText('COM_NENO_EDITOR_BTN_DONT_TRANSLATE'); ?>"
				   data-id="<?php echo $translation->content_id; ?>"><i class="icon-unpublish"></i></a>
			<?php endif; ?>
		</div>
		<div class="pull-right">
			<div class="pull-right right-buttons">
				<button id="copy-btn" class="btn btn-big" type="button">
					<span class="icon-copy big-icon"></span>
					<span class="normal-text"><?php echo JText::_('COM_NENO_EDITOR_COPY_BUTTON'); ?></span>
					<span class="small-text">Ctrl + <span class="arrow">&rArr;</span></span>
				</button>
				<button id="translate-btn" class="btn btn-big" type="button">
					<span class="icon-screen big-icon"></span>
						<span
							class="normal-text"><?php echo JText::_('COM_NENO_EDITOR_COPY_AND_TRANSLATE_BUTTON'); ?></span>
					<span class="small-text">Ctrl + Shift + <span class="arrow">&rArr;</span></span>
				</button>
				<button id="skip-button" class="btn btn-big" type="button"
				        data-id="<?php echo empty($translation) ? '' : $translation->id; ?>">
					<span class="icon-next big-icon"></span>
					<span class="normal-text"><?php echo JText::_('COM_NENO_EDITOR_SKIP_BUTTON'); ?></span>
					<span class="small-text">Ctrl + Space</span>
				</button>
				<button id="draft-button" class="btn btn-big" type="button"
				        data-id="<?php echo empty($translation) ? '' : $translation->id; ?>">
					<span class="icon-briefcase big-icon"></span>
					<span class="normal-text"><?php echo JText::_('COM_NENO_EDITOR_SAVE_AS_DRAFT_BUTTON'); ?></span>
					<span class="small-text">Ctrl + S</span>
				</button>
				<button id="save-next-button" class="btn btn-big btn-success" type="button"
				        data-id="<?php echo empty($translation) ? '' : $translation->id; ?>">
					<span class="icon-checkmark big-icon"></span>
					<span class="normal-text"><?php echo JText::_('COM_NENO_EDITOR_SAVE_AND_NEXT_BUTTON'); ?></span>
					<span class="small-text">Ctrl + Enter</span>
				</button>
			</div>
		</div>
	</div>
</div>
<div>
	<div>
		<div class="span6">
			<div class="uneditable-input full-width original-text">
				<?php echo empty($translation) ? '' : NenoHelper::highlightHTMLTags(NenoHelper::html2text($translation->original_text)); ?>
			</div>
			<div class="clearfix"></div>
			<div class="pull-right last-modified">
				<?php echo empty($translation) ? '' : JText::sprintf('COM_NENO_EDITOR_LAST_MODIFIED', $translation->time_added) ?>
			</div>
			<?php if (!empty($translation)): ?>
				<?php if (empty($translation->comment)): ?>
					<div class="add-comment-to-translator">
						<a
							href="#addCommentFor<?php echo $translation->content_id . '-' . $translation->language; ?>"
							role="button"
							class=""
							title=""
							type="button"
							data-toggle="modal">
							<span class="icon-pencil"></span>
							<?php echo JText::_('COM_NENO_COMMENTS_TO_TRANSLATOR_GENERAL_CREATE'); ?>
						</a>

					</div>
				<?php else: ?>
					<div class="clearfix"></div>
					<div class="full-width add-comment-to-translator">
						<a
							href="#addCommentFor<?php echo $translation->content_id . '-' . $translation->language; ?>"
							role="button"
							class=""
							title=""
							type="button"
							data-toggle="modal">
							<h3><?php echo JText::_('COM_NENO_COMMENTS_TO_TRANSLATOR_EDITOR_DISPLAY_COMMENT_TITLE'); ?>
								<span class="icon-pencil"></span></h3>

							<p><?php echo nl2br($translation->comment); ?></p>
						</a>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<div class="span6 pull-right">
			<textarea
				class="full-width translated-content"><?php echo !empty($translation) && ($translation->state != NenoContentElementTranslation::NOT_TRANSLATED_STATE || $translation->string !== $translation->original_text) ? $translation->string : ''; ?></textarea>

			<div class="clearfix"></div>
			<div class="pull-left translated-by">
				<?php echo JText::sprintf('COM_NENO_EDITOR_TRANSLATED_BY', NenoSettings::get('translator')); ?>
			</div>
			<div class="pull-right last-modified">
				<?php echo empty($translation) ? '' : JText::sprintf('COM_NENO_EDITOR_LAST_MODIFIED', $translation->time_changed !== '0000-00-00 00:00:00' ? $translation->time_changed : JText::_('COM_NENO_EDITOR_NEVER')) ?>
			</div>
			<div class="clearfix"></div>
			<br/>

			<div class="pull-left translated-error">
					<span
						class="label label-important error-title"><?php echo JText::sprintf('COM_NENO_EDITOR_ERROR_TRANSLATED_BY', NenoSettings::get('translator')); ?></span>
				<span class="error-message"></span>
			</div>
		</div>
	</div>
</div>
<?php if (NenoSettings::get('translator') == '' || NenoSettings::get('translator_api_key') == ''): ?>
	<!-- Modal for translator API key -->
	<div id="translatorKeyModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="translatorKey"
	     aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="myModalLabel"><?php echo JText::_('COM_NENO_EDITOR_NO_TRANSLATOR_HEADER'); ?></h3>
		</div>
		<div class="modal-body">
			<p><?php echo JText::_('COM_NENO_EDITOR_NO_TRANSLATOR_MESSAGE'); ?></p>
			<br/>
			<br/>
			<table class="full-width">
				<tr>
					<td class='setting-label'>
						<?php echo JText::_('COM_NENO_SETTINGS_SETTING_NAME_TRANSLATOR'); ?>
						<span class="settings-tooltip" data-toggle="tooltip"
						      title='<?php echo JText::_('COM_NENO_SETTINGS_SETTING_INFO_TRANSLATOR'); ?>'>[?]</span>
					</td>
					<td class=''>
						<?php echo NenoHelper::getTranslatorsSelect(); ?>
					</td>
				</tr>
				<tr>
					<td class='setting-label'>
						<?php echo JText::_('COM_NENO_SETTINGS_SETTING_NAME_TRANSLATOR_API_KEY'); ?>
					</td>
					<td class=''>
						<input type="text" name="translator_api_key" id="translator_api_key"
						       class="input-setting input-large"
						       value=""/>
					</td>
				</tr>
			</table>
		</div>
		<div class="modal-footer">
			<div class="pull-left">
				<a href="https://www.neno-translate.com/en/help/documentation/frequently-asked-questions/installation-and-upgrade/16-how-to-get-a-google-or-yandex-api-key"
				   target="_blank"><?php echo JText::_('COM_NENO_SETTINGS_SETTING_NAME_API_KEY_DOCS_LINK'); ?></a>
			</div>
			<button class="btn" data-dismiss=
			aria-hidden="true"><?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_MODAL_GROUPFORM_BTN_CLOSE'); ?></button>
			<button class="btn btn-primary"
			        id="saveTranslatorKey"><?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_MODAL_GROUPFORM_BTN_SAVE'); ?></button>
		</div>
	</div>
<?php endif; ?>
<?php if (!empty($translation)): ?>
	<div id="addCommentFor<?php echo $translation->content_id . '-' . $translation->language; ?>"
	     class="modal hide fade comment-modal"
	     tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-body">
			<h3 class="myModalLabel"><?php echo JText::_('COM_NENO_COMMENTS_TO_TRANSLATOR_GENERAL_MODAL_ADD_TITLE'); ?></h3>

			<p><?php echo JText::_('COM_NENO_COMMENTS_TO_TRANSLATOR_MODAL_ADD_BODY_PRE'); ?></p>

			<p><?php echo JText::sprintf('COM_NENO_COMMENTS_TO_TRANSLATOR_EDITOR_MODAL_ADD_BODY', JRoute::_('index.php?option=com_neno&view=externaltranslations&open=comment'), $translation->language, JRoute::_('index.php?option=com_neno&view=dashboard')); ?></p>

			<p><?php echo JText::sprintf('COM_NENO_COMMENTS_TO_TRANSLATOR_MODAL_ADD_BODY_POST', NenoSettings::get('source_language'), $translation->language); ?></p>

			<p><textarea class="comment-to-translator"
			             data-translation="<?php echo $translation->id; ?>"><?php echo empty($translation->comment) ? '' : $translation->comment; ?></textarea>
			</p>
		</div>
		<div class="modal-footer">
			<p>
				<input type="checkbox" id="comment-check-<?php echo $translation->id; ?>" class="comment-check"
				       data-content-id="<?php echo $translation->content_id; ?>"/>
				<label
					for="comment-check-<?php echo $translation->content_id; ?>"><?php echo JText::_('COM_NENO_COMMENTS_TO_TRANSLATOR_EDITOR_MODAL_CHECK_LABEL'); ?></label>
				<label for="comment-check-<?php echo $translation->content_id; ?>"
				       class="comment-breadcrumbs"><?php echo implode(' &gt; ', $translation->breadcrumbs); ?></label>
			</p>
			<a href="#" class="btn" data-dismiss="modal"
			   aria-hidden="true"><?php echo JText::_('COM_NENO_COMMENTS_TO_TRANSLATOR_MODAL_BTN_CLOSE'); ?></a>
			<a href="#"
			   class="btn btn-primary save-translation-comment"
			   data-translation="<?php echo $translation->id; ?>"><?php echo JText::_('COM_NENO_COMMENTS_TO_TRANSLATOR_MODAL_BTN_SAVE'); ?></a>
		</div>
	</div>
<?php endif; ?>

<script>
	jQuery('.save-translation-comment').off('click').on('click', function () {
		var translation = jQuery(this).data('translation');
		var checkbox = jQuery('#comment-check-' + translation);
		var contentId = checkbox.data('content-id');
		var data = {
			placement: 'string',
			stringId: translation,
			comment: jQuery(".comment-to-translator[data-translation='" + translation + "']").val()
		};

		if (checkbox.is(':checked')) {
			data['alltranslations'] = 1;
			data['contentId'] = contentId;
		}
		jQuery.post(
			'index.php?option=com_neno&task=saveExternalTranslatorsComment',
			data,
			function () {
				jQuery('.comment-modal').modal('toggle');
			}
		);
	});
</script>