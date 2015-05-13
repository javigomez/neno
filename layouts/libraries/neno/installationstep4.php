<?php

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
$items = $displayData->languages;

?>

<style>
	.schedule-task-option {
		border: 1px solid #000;
		padding: 5px 10px;
	}
</style>

<div class="installation-step">
	<div class="installation-body span12">
		<div class="error-messages"></div>
		<h2><?php echo JText::_('COM_NENO_INSTALLATION_TARGET_LANGUAGES_TITLE'); ?></h2>

		<p><?php echo JText::_('COM_NENO_INSTALLATION_TARGET_LANGUAGES_MESSAGE'); ?></p>

		<?php foreach ($items as $item): ?>
			<?php echo JLayoutHelper::render('languageconfiguration', $item, JPATH_NENO_LAYOUTS); ?>
		<?php endforeach; ?>

		<button type="button" class="btn btn-primary" id="add-languages-button">
			<?php echo JText::_('COM_NENO_INSTALLATION_TARGET_LANGUAGES_ADD_LANGUAGE_BUTTON'); ?>
		</button>

		<button type="button" class="btn btn-success next-step-button">
			<?php echo JText::_('COM_NENO_INSTALLATION_NEXT'); ?>
		</button>
	</div>

	<?php echo JLayoutHelper::render('installationbottom', 3, JPATH_NENO_LAYOUTS); ?>
</div>

<script>
	jQuery('#add-languages-button').click(function () {
		jQuery.ajax({
			beforeSend: onBeforeAjax,
			url: 'index.php?option=com_neno&task=showInstallLanguagesModal&placement=installation',
			success: function (html) {
				jQuery('#languages-modal .modal-body').empty().append(html);
				jQuery('#languages-modal').modal('show');
			}
		});
	});

	function loadMissingTranslationMethodSelectors() {
		if (typeof jQuery(this).prop("tagName") == 'undefined') {
			i = 1;
			jQuery('.method-selectors').each(function () {
				//Count how many we currently are showing
				var n = jQuery(this).find('.translation-method-selector-container').length;

				//If we are loading because of changing a selector, remove all children
				var selector_id = jQuery(this).find('.translation-method-selector').attr('data-selector-id');
				if (typeof selector_id !== 'undefined') {
					//Loop through each selector and remove the ones that are after this one
					for (var i = 0; i < n; i++) {
						if (i > selector_id) {
							jQuery(this).find("[data-selector-container-id='" + i + "']").remove();
						}
					}
				}

				//Create a string to pass the current selections
				var selected_methods_string = '';
				jQuery(this).find('.translation-method-selector').each(function () {
					selected_methods_string += '&selected_methods[]=' + jQuery(this).find(':selected').val();
				});

				executeAjax(n, selected_methods_string, jQuery(this).find('.translation-method-selector'));
			});
		}
		else {
			//If we are loading because of changing a selector, remove all children
			var selector_id = jQuery(this).data('selector-id');
			var n = jQuery(this).closest('.method-selectors').find('.translation-method-selector-container').length;
			if (typeof selector_id !== 'undefined') {
				//Loop through each selector and remove the ones that are after this one
				for (var i = 0; i < n; i++) {
					if (i > selector_id) {
						console.log(jQuery(this).closest('.method-selectors').find("[data-selector-container-id='" + i + "']"));
						jQuery(this).closest('.method-selectors').find("[data-selector-container-id='" + i + "']").remove();
					}
				}
			}
			var selected_methods_string = '&selected_methods[]=' + jQuery(this).find(':selected').val();
			var lang = jQuery(this).closest('.method-selectors').data('language');
			var otherParams = '&language=' + lang;
			saveTranslationMethod(jQuery(this).find(':selected').val(), lang, selector_id + 1);
			executeAjax(n, selected_methods_string, jQuery(this), otherParams);
		}
	}


	function executeAjax(n, selected_methods_string, element, otherParams) {
		if (typeof otherParams == 'undefined') {
			otherParams = '';
		}
		jQuery.ajax({
			beforeSend: onBeforeAjax,
			url: 'index.php?option=com_neno&task=getTranslationMethodSelector&placement=language&n=' + n + selected_methods_string + otherParams,
			success: function (html) {
				if (html !== '') {
					jQuery(element).closest('.method-selectors').append(html);
				}

				jQuery('.translation-method-selector').off('change').on('change', loadMissingTranslationMethodSelectors);
				jQuery('select').chosen();
			}
		});
	}

	function saveTranslationMethod(translationMethod, language, ordering) {
		jQuery.ajax({
			beforeSend: onBeforeAjax,
			url: 'index.php?option=com_neno&task=saveTranslationMethod',
			type: 'POST',
			data: {
				translationMethod: translationMethod,
				language: language,
				ordering: ordering
			}
		});
	}

	loadMissingTranslationMethodSelectors();
</script>
