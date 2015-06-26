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

if (!empty($this->extraSidebar))
{
	$this->sidebar .= $this->extraSidebar;
}

?>

<style>
	.translation-type, .information-box {
		border: 1px solid #ccc;
		margin: 10px 0;
	}
	
	.translation-type .translation-type-header, .translation-type .translation-type-footer {
		background-color: #eee;
	}
	
	.translation-type > div {
		padding: 15px;
	}
	
	.translation-type .translation-introtext {
		color: #888;
	}
	
	.translation-type .translation {
		padding: 20px 15px;
	}
	
	.information-box {
		padding: 20px 15px;
	}

	.add-comment-to-translator-button {
		float: right;
		font-weight: normal;
		margin-top: -15px;
	}

	.modal {
		width: 580px !important;
		margin-left: -290px !important;
		left: 50% !important;
	}

	.modal-body p,
	.modal-body h3 {
		padding: 0 15px !important;
	}

	.modal-body textarea {
		width: 98%;
		height: 70px;
	}

</style>

<script>
	jQuery(document).ready(function () {
		jQuery('.translate_automatically_setting').off('click').on('click', function () {
			jQuery.ajax({
				beforeSend: onBeforeAjax,
				type: "POST",
				url: 'index.php?option=com_neno&task=externaltranslations.setAutomaticTranslationSetting',
				data: {
					setting: jQuery(this).data('setting'),
					value: +jQuery(this).is(':checked')
				},
				success: function (data) {
					if (data != 'ok') {
						alert("There was an error saving setting");
					}
				}
			});
		});

		jQuery('.order-button').off('click').on('click', function () {
			jQuery.ajax({
				beforeSend: onBeforeAjax,
				type: "POST",
				url: 'index.php?option=com_neno&task=externaltranslations.createJob',
				data: {
					type: jQuery(this).data('type'),
					language: jQuery(this).data('language')
				},
				success: function (data) {
					if (data != 'ok') {
						alert("There was an error saving setting");
					}
				}
			});
		});

		if (window.location.search.toLowerCase().indexOf("open=comment") >= 0) {
			jQuery('#addCommentForTranslators').modal('show');
		}
	})
	;
</script>

<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
	<div class="span9">
		<div id="elements-wrapper">
			<h1><?php echo JText::_('COM_NENO_TITLE_EXTERNALTRANSLATIONS'); ?></h1>
			
			<p>
				<?php echo JText::_('COM_NENO_EXTERNALTRANSLATION_INTROTEXT'); ?>
			</p>
			
			<div class="translation-type">
				<div class="translation-type-header">
					<h3>
					<span
						class="icon-screen"></span> <?php echo JText::_('COM_NENO_EXTERNALTRANSLATION_MACHINE_TRANSLATION_TITLE'); ?>
					</h3>
					
					<p class="translation-introtext">
						<?php echo JText::sprintf('COM_NENO_EXTERNALTRANSLATION_MACHINE_TRANSLATION_INTROTEXT', '#'); ?>
					</p>
				</div>
				<div class="translation-type-content">
					<?php $machineTranslationsAvailable = false; ?>
					<?php foreach ($this->items as $key => $item): ?>
						<?php if ($item->translation_method_id == '2'): ?>
							<?php $machineTranslationsAvailable = true; ?>
							<div class="translation">
								<div class="span3">
									<img
										src="<?php echo JUri::root(); ?>/media/mod_languages/images/<?php echo $item->image; ?>.gif"
										style="margin-bottom: 3px;">
									<?php echo $item->title_native; ?>
								</div>
								<div class="span3">
									<?php echo JText::sprintf('COM_NENO_EXTERNALTRANSLATION_WORDS', $item->words); ?>
								</div>
								<div class="span3">
									<?php $pro_price_tc = $item->words; ?>
									<?php $pro_price_eur = number_format(ceil($pro_price_tc * 0.0005), 2, ',', '.'); ?>
									<?php echo JText::sprintf('COM_NENO_EXTERNALTRANSLATION_PRICE'); ?> <?php echo $pro_price_tc; ?>
									TC (€ <?php echo $pro_price_eur; ?>)
								</div>
								<div class="span3">
									<button type="button" class="btn order-button"
									        data-type="<?php echo $item->translation_method_id; ?>"
									        data-language="<?php echo $item->language; ?>">
										<?php echo JText::_('COM_NENO_EXTERNALTRANSLATION_ORDER_NOW'); ?>
									</button>
								</div>
							</div>
							<?php unset($this->items[$key]); ?>
						<?php endif; ?>
					<?php endforeach; ?>
					<?php if ($machineTranslationsAvailable === false): ?>
						<div
							class="alert alert-info"><?php echo JText::sprintf('COM_NENO_EXTERNALTRANSLATION_NO_TRANSLATIONS_AVAILABLE', JRoute::_('index.php?option=com_neno&view=groupselements')); ?></div>
					<?php endif; ?>
				</div>
				<div class="translation-type-footer">
					<input type="checkbox" class="translate_automatically_setting"
					       data-setting="translate_automatically_machine"
					       name="machine_translation" <?php echo NenoSettings::get('translate_automatically_machine') ? 'checked="checked"' : ''; ?>
					       value="1"/> <?php echo JText::_('COM_NENO_EXTERNALTRANSLATION_AUTOMATICALLY_MACHINE_TRANSLATE'); ?>
				</div>
			</div>
			
			<div class="translation-type">
				<div class="translation-type-header">
					<h3>
						<span class="icon-users"></span>
						<?php echo JText::_('COM_NENO_EXTERNALTRANSLATION_PROFESSIONAL_TRANSLATION_TITLE'); ?>
						<a
							href="#addCommentForTranslators"
							role="button"
							class="btn add-comment-to-translator-button"
							title=""
							type="button"
							data-toggle="modal">
							<span class="icon-pencil"></span>
							<?php
							if(empty($comment))
							{
								$btnLabel = 'COM_NENO_COMMENTS_TO_TRANSLATOR_GENERAL_CREATE';
							}
							else
							{
								$btnLabel = 'COM_NENO_COMMENTS_TO_TRANSLATOR_GENERAL_EDIT';
							}
							?>
							<?php echo JText::_($btnLabel); ?>
						</a>
					</h3>
					
					<p class="translation-introtext">
						<?php echo JText::sprintf('COM_NENO_EXTERNALTRANSLATION_PROFESSIONAL_TRANSLATION_INTROTEXT', '#'); ?>
					</p>
				</div>
				<div class="translation-type-content">
					<?php $professionalTranslationsAvailable = false; ?>
					<?php foreach ($this->items as $key => $item): ?>
						<?php if ($item->translation_method_id == '3'): ?>
							<?php $professionalTranslationsAvailable = true; ?>
							<div class="translation">
								<div class="span3">
									<img
										src="<?php echo JUri::root(); ?>/media/mod_languages/images/<?php echo $item->image; ?>.gif"
										style="margin-bottom: 3px;">
									<?php echo $item->title_native; ?>
								</div>
								<div class="span3">
									<?php echo JText::sprintf('COM_NENO_EXTERNALTRANSLATION_WORDS', $item->words); ?>
								</div>
								<div class="span3">
									<?php $pro_price_tc = $item->words * 20; ?>
									<?php $pro_price_eur = number_format(ceil($pro_price_tc * 0.0005), 2, ',', '.'); ?>
									<?php echo JText::sprintf('COM_NENO_EXTERNALTRANSLATION_PRICE'); ?> <?php echo $pro_price_tc; ?>
									TC (€ <?php echo $pro_price_eur; ?>)
								</div>
								<div class="span3">
									<button type="button" class="btn order-button"
									        data-type="<?php echo $item->translation_method_id; ?>"
									        data-language="<?php echo $item->language; ?>"
										>
										<?php echo JText::_('COM_NENO_EXTERNALTRANSLATION_ORDER_NOW'); ?>
									</button>
								</div>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
					<?php if ($professionalTranslationsAvailable === false): ?>
						<div
							class="alert alert-info"><?php echo JText::sprintf('COM_NENO_EXTERNALTRANSLATION_NO_TRANSLATIONS_AVAILABLE', JRoute::_('index.php?option=com_neno&view=groupselements')); ?></div>
					<?php endif; ?>
				</div>
				<div class="translation-type-footer">
					<input type="checkbox" class="translate_automatically_setting"
					       data-setting="translate_automatically_professional"
					       name="machine_translation" <?php echo NenoSettings::get('translate_automatically_professional') ? 'checked="checked"' : ''; ?>
					       value="1"/> <?php echo JText::_('COM_NENO_EXTERNALTRANSLATION_AUTOMATICALLY_PROFESSIONAL_TRANSLATE'); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="span3">
		<div class="information-box span11 pull-right">
			<div class="center">
				<div>
					<p class="center">
						<?php echo JText::sprintf('COM_NENO_EXTERNALTRANSLATION_BUY_TC_TEXT', $this->tcNeeded); ?>
					</p>
				</div>
				<div class="center">
					<h3><?php echo JText::sprintf('COM_NENO_EXTERNALTRANSLATION_PRICE'); ?>
						&nbsp;€<?php echo number_format(ceil($this->tcNeeded * 0.0005), 2, ',', '.'); ?> </h3>
				</div>
				<div class="center">
					<a href="#" class="btn btn-success">
						<?php echo JText::_('COM_NENO_EXTERNALTRANSLATION_BUY_TC_BUTTON'); ?>
					</a>
				</div>
			</div>
		</div>

		<?php // Only show the jobs link if there are any jobs ?>
		<?php if (NenoHelperBackend::areThereAnyJobs()): ?>
			<div class="information-box span11 pull-right alert alert-info">
				<div class="center">
					<div>
						<p class="left">
							<?php echo JText::_('COM_NENO_EXTERNALTRANSLATION_JOBS_INTRO'); ?>
							<br/>
							<a href="<?php echo JRoute::_('index.php?option=com_neno&view=jobs'); ?>"><?php echo JText::_('COM_NENO_EXTERNALTRANSLATION_JOBS_LINK'); ?></a>
						</p>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<div class="information-box span11 pull-right alert alert-danger">
			<div class="center">
				<div>
					<p class="left">
						This section is currently not complete. Please do not try to order any external translations or
						buy Translation Credit.
					</p>
				</div>
			</div>
		</div>


	</div>
</div>
<div id="addCommentForTranslators" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-body">
		<h3 class="myModalLabel"><?php echo JText::sprintf('COM_NENO_COMMENTS_TO_TRANSLATOR_GENERAL_MODAL_ADD_TITLE'); ?></h3>
		<p><?php echo JText::_('COM_NENO_COMMENTS_TO_TRANSLATOR_MODAL_ADD_BODY_PRE'); ?></p>
		<p><?php echo JText::sprintf('COM_NENO_COMMENTS_TO_TRANSLATOR_GENERAL_MODAL_ADD_BODY', JRoute::_('index.php?option=com_neno&view=dashboard'), JRoute::_('index.php?option=com_neno&view=editor')); ?></p>
		<p><?php echo JText::sprintf('COM_NENO_COMMENTS_TO_TRANSLATOR_GENERAL_MODAL_ADD_BODY_POST', NenoSettings::get('source_language')); ?></p>
		<p><textarea class="comment-to-translator"><?php
				if(!empty($comment))
				{
					echo $comment;
				}
				?></textarea></p>
	</div>
	<div class="modal-footer">
		<a href="#" class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_NENO_COMMENTS_TO_TRANSLATOR_MODAL_BTN_CLOSE'); ?></a>
		<a href="#" class="btn btn-primary"><?php echo JText::_('COM_NENO_COMMENTS_TO_TRANSLATOR_MODAL_BTN_SAVE'); ?></a>
	</div>
</div>