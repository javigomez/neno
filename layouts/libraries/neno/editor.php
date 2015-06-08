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

			jQuery('#translate-btn').off('click').on('click', function () {
				jQuery('#translatorKeyModal').modal('show');
			});

			jQuery('#saveTranslatorKey').off('click').on('click', function () {
				var translator = jQuery('#translator').val();
				var translatorKey = jQuery('#translator_api_key').val();
				jQuery.ajax({
						beforeSend: onBeforeAjax,
						type: 'POST',
						data: {
							translator: translator,
							translatorKey: translatorKey
						},
						url: 'index.php?option=com_neno&task=editor.saveTranslatorConfig',
						success: function () {
							jQuery('#saveTranslatorKey').modal('hide');
							window.location.reload();
						}
					}
				);
			});

			var options = {
				html: true,
				placement: "right"
			}
			jQuery('.settings-tooltip').tooltip(options);

			<?php else: ?>

			jQuery('#translate-btn').off('click').on('click', translate);

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
             
            jQuery('#dont-translate').tooltip();  
            jQuery('#dont-translate').off().on('click', changeFieldTranslateState);
		});
        
        function changeFieldTranslateState() {

            var id = jQuery(this).attr('data-id');

            jQuery.ajax({
                    beforeSend: onBeforeAjax,
                    url: 'index.php?option=com_neno&task=groupselements.toggleContentElementField&fieldId=' + id + '&translateStatus=0',
                    success: function() {
                        window.location.reload();
                    }
                }
            );
        }        
        
	</script>

	<div>
		<div class="span12">
			<div class="span6 breadcrumbs">
				<?php echo empty($translation) ? '' : implode(' <span class="gt icon-arrow-right"></span>', $translation->breadcrumbs); ?>
                <?php if (!empty($translation->breadcrumbs)): ?>
                    &nbsp;&nbsp;
                    <a id="dont-translate" 
                       class="hasTooltip" 
                       href="javascript:void(0);" 
                       title="<?php echo JHtml::tooltipText('COM_NENO_EDITOR_BTN_DONT_TRANSLATE'); ?>" 
                       data-id="<?php echo $translation->content_id; ?>"><i class="icon-unpublish"></i></a>
                <?php endif; ?>
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
					<span class="label label-important error-title"><?php echo JText::sprintf('COM_NENO_EDITOR_ERROR_TRANSLATED_BY', NenoSettings::get('translator')); ?></span>
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
			<h3 id="myModalLabel">&nbsp;</h3>
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
			<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_MODAL_GROUPFORM_BTN_CLOSE'); ?></button>
			<button class="btn btn-primary" id="saveTranslatorKey"><?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_MODAL_GROUPFORM_BTN_SAVE'); ?></button>
		</div>
	</div>
<?php endif; ?>